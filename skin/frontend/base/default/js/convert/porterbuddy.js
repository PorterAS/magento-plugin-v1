'use strict';

/**
 * Porterbuddy constants
 */
function Porterbuddy() {}
Porterbuddy.CARRIER_CODE = 'cnvporterbuddy';
Porterbuddy.METHOD_EXPRESS = 'express';
Porterbuddy.METHOD_DELIVERY = 'delivery';
Porterbuddy.COOKIE = 'porterbuddy_location';
Porterbuddy.SOURCE_BROWSER = 'browser';
Porterbuddy.SOURCE_IP = 'ip';
Porterbuddy.SOURCE_USER = 'user';

Porterbuddy.utilities = {
    debounce: function (callback, timeout) {
        var timer;
        return function () {
            var that = this;
            var args = arguments;

            if (timer) {
                clearTimeout(timer);
                timer = null;
            }

            timer = setTimeout(function () {
                timer = null;
                callback.apply(that, args);
            }, timeout);
        };
    },
    getCookie: function (cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    },
    setCookie: function (cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
    },
    getCounterText: function (date, remainingMinutes) {
        if (typeof window.moment !== 'undefined') {
            return moment().to(date, true);
        }

        var days = Math.floor(remainingMinutes / (60*24));
        var hours = Math.floor(remainingMinutes / 60) % 24;
        var minutes = remainingMinutes % 60;

        var parts = [];
        if (days) {
            parts.push(
                Translator.translate(1 === days ? '%s day' : '%s days').replace('%s', days)
            );
        }
        if (hours) {
            parts.push(
                Translator.translate(1 === hours ? '%s hour' : '%s hours').replace('%s', hours)
            );
        }
        if (minutes) {
            parts.push(
                Translator.translate(1 === minutes ? '%s minute' : '%s minutes').replace('%s', minutes)
            );
        }

        return parts.join(' ');
    }
};

window.PorterbudyAvailability = Class.create({
    initialize: function (options) {
        this.$element = options.$element;

        this.initOptions(options);
        this.initEvents();
        this.initExtensions();
    },

    initOptions: function (options) {
        this.options = options;

        this.mapsApiKey = options.mapsApiKey;
        this.getLocationURL = options.getLocationURL;

        var availableMethods = this.getLocationDiscoveryMethods();
        this.enabledDiscoveryMethods = [];
        jQuery.each(options.locationDiscovery, function (index, discoveryMethod) {
            if (discoveryMethod in availableMethods) {
                this.enabledDiscoveryMethods.push(availableMethods[discoveryMethod]);
            }
        }.bind(this));

        this.getAvailabilityURL = options.getAvailabilityURL;
        this.isAlwaysShow = options.isAlwaysShow;
        this.defaultCountry = options.defaultCountry;

        this.$availabilityText = this.$element.find('.porterbuddy-availability-text');
        this.textTemplate = new Template(this.$availabilityText.html());
        this.$availabilityText.html(''); // clear template

        this.textClickToSee = options.textClickToSee;
        this.textFetchingTemplate = new Template(options.textFetching);
        this.textDeliveryUnavailableTemplate = new Template(options.textDeliveryUnavailable);

        this.$locationLink = this.$element.find('.porterbuddy-availability-location-link');
        this.locationTemplate = new Template(this.$locationLink.html());
        this.$locationLink.html(''); // clear template

        this.popupId = this.$locationLink.data('open');
        this.$popup = jQuery('#' + this.popupId);
        this.$popupPostcode = this.$popup.find('.porterbuddy-popup-postcode');
        this.$popupSave = this.$popup.find('.porterbuddy-popup-save');
        this.$message = this.$popup.find('.porterbuddy-message');

        this.geocoder = null;
        this.location = null;
        this.availability = null;

        this.$form = jQuery('#product_addtocart_form');
        this.$qty = this.$form.find('#qty');

        var productId = this.options.productId;
        if (!productId && this.$form.attr('action')) {
            var match = this.$form.attr('action').match(/product\/(\d+)/);
            if (match) {
                productId = match[1];
            }
        }

        // productId, qty, anything else
        this.params = {
            productId: productId,
            qty: this.$qty.val()
        };

        // deferred objects
        this.getAvailabilityDfd = {}; // by postcode and product id
        this.ipLocationDfd = null;
        this.browserLocationDfd = null;
        this.geocodeDfd = {}; // by request
        this.availabilityTimer = null;
    },

    getLocationDiscoveryMethods: function() {
        return {
            'browser': this.getBrowserLocation.bind(this),
            'ip': this.getIpLocation.bind(this),
        };
    },

    initEvents: function () {
        this.$locationLink.on('click', 'a, button', this.changeLocation.bind(this));
        this.$popupSave.click(this.saveChangedLocation.bind(this));

        this.listenQtyChange();
        if (typeof window.spConfig !== 'undefined') {
            this.listenConfigurableChange(window.spConfig);
        }else{
            this.update();
        }
    },

    listenQtyChange: function () {
        this.$qty.change(function () {
            this.params.qty = this.$qty.val();
            this.update();
        }.bind(this));
    },

    listenConfigurableChange: function (spConfig) {
        var that = this;
        var orig = {
            configureElement: Product.Config.prototype.configureElement
        };
        Product.Config.prototype.configureElement = function (element) {
            orig.configureElement.call(this, element);
            var simpleProductId = that.getSimpleProductId(spConfig);
            if (simpleProductId) {
                that.params.productId = simpleProductId;
                that.update();
            }
        }
    },

    getSimpleProductId: function (spConfig) {
        var productCandidates = [];
        jQuery.each(spConfig.settings, function (selectIndex, select) {
            var attributeId = select.id.replace('attribute', '');
            var selectedValue = select.options[select.selectedIndex].value;

            jQuery.each(spConfig.config.attributes[attributeId].options, function(optionIndex, option) {
                if (option.id == selectedValue) {
                    var optionProducts = option.products;

                    if (productCandidates.length == 0) {
                        productCandidates = optionProducts;
                    } else {
                        var productIntersection = [];
                        jQuery.each(optionProducts, function (productIndex, productId) {
                            if (productCandidates.indexOf(productId) > -1) {
                                productIntersection.push(productId);
                            }
                        });
                        productCandidates = productIntersection;
                    }
                }
            });
        });
        return (productCandidates.length == 1) ? productCandidates[0] : null;
    },

    /**
     * @override
     */
    initExtensions: function () {
        // extension point
    },

    update: function () {
        var location = this.getCachedLocation();
        if (location) {
            this.locationSuccess(location);
        } else {
            this.$element.addClass('location-loading');
            this.chainCallbacks(this.enabledDiscoveryMethods)
                .done(function (location) {
                    this.rememberLocation(location);
                    this.locationSuccess(location);
                }.bind(this))
                .fail(this.locationError.bind(this))
                .always(function () {
                    this.$element.removeClass('location-loading');
                }.bind(this));
        }
    },

    getCachedLocation: function() {
        var location = Porterbuddy.utilities.getCookie(Porterbuddy.COOKIE);
        if (!location) {
            return null;
        }
        location = JSON.parse(location);
        if (!location || !location.postcode) {
            return null;
        }
        return location;
    },

    rememberLocation: function (location) {
        Porterbuddy.utilities.setCookie(Porterbuddy.COOKIE, JSON.stringify(location), 90);
    },

    prepareAvailabilityData: function (postcode) {
        // can extend to send whole serialized product form if necessary
        return jQuery.extend({}, this.params, {
            postcode: postcode
        })
    },

    prepareAvailabilityKey: function (data) {
        return data.postcode + '_' + data.productId + '_' + data.qty;
    },

    getAvailability: function (postcode) {
        var data = this.prepareAvailabilityData(postcode);
        var key = this.prepareAvailabilityKey(data);
        if (this.getAvailabilityDfd && this.getAvailabilityDfd[key]) {
            return this.getAvailabilityDfd[key].promise();
        }

        var dfd = this.getAvailabilityDfd[key] = jQuery.Deferred();
        dfd.always(function () {
            // once complete, stop caching this call
            delete this.getAvailabilityDfd[key];
        }.bind(this));

        jQuery.ajax(this.getAvailabilityURL, {
            method: 'post',
            dataType: 'json',
            data: data
        }).done(function (result) {
            if (!result.available) {
                dfd.reject(result.message);
                return;
            }
            dfd.resolve(result);
        }.bind(this)).fail(function () {
            dfd.reject();
        }.bind(this));

        return dfd.promise();
    },

    locationSuccess: function (location) {
        this.setCurrentLocation(location);

        this.$availabilityText.html(this.textFetchingTemplate.evaluate(this.location));
        this.$element.addClass('availability-loading');

        this.getAvailability(location.postcode)
        .done(function (result) {
            this.availabilitySuccess(result);
        }.bind(this))
        .fail(function (message) {
            this.availabilityError(message);
        }.bind(this))
        .always(function () {
            this.$element.removeClass('availability-loading');
        }.bind(this));
    },

    setCurrentLocation: function (location) {
        this.location = location;

        // rendered location summary for rendering in templates
        this.location.location = (this.location.postcode + ' ' + this.location.city).replace(/ +$/, '');

        this.$locationLink.html(this.locationTemplate.evaluate(this.location));
        this.$element.removeClass('postcode-error').addClass('postcode-success');
    },

    locationError: function (reason) {
        //this.hide();
        this.$element.addClass('postcode-error').removeClass('postcode-success');
        this.$locationLink.html(this.textClickToSee);
        this.show();
    },

    availabilitySuccess: function (result) {
        this.availability = result;
        this.$element.removeClass('availability-error').addClass('availability-success');
        this.renderText();
        this.show();
    },

    availabilityError: function (message) {
        if (!this.isAlwaysShow) {
            this.hide();
            return;
        }

        this.$element.addClass('availability-error').removeClass('availability-success');

        // default error message
        var error = this.textDeliveryUnavailableTemplate.evaluate(this.location);
        if (message) {
            try {
                var template = new Template(message);
                error = template.evaluate(this.location);
            } catch (error) {
            }
        }

        this.$availabilityText.html(error);
        this.show();
    },

    renderText: function () {
        var params = {
            date: this.availability.humanDate,
            countdown: this.getCounterText(this.availability.date, this.availability.timeRemaining)
        };
        this.$availabilityText.html(this.textTemplate.evaluate(params));

        if (this.availability.timeRemaining-- > 0) {
            // prevent multiple timers
            clearTimeout(this.availabilityTimer);
            // revisit in a minute
            this.availabilityTimer = setTimeout(this.renderText.bind(this), 60*1000);
        } else {
            this.hide();
        }
    },

    show: function () {
        this.$element.show();
    },

    hide: function () {
        this.$element.hide();
    },

    getCounterText: function (date, remainingMinutes) {
        if (typeof window.moment !== 'undefined') {
            return moment().to(date, true);
        }

        var days = Math.floor(remainingMinutes / (60*24));
        var hours = Math.floor(remainingMinutes / 60) % 24;
        var minutes = remainingMinutes % 60;

        var parts = [];
        if (days) {
            parts.push(
                Translator.translate(1 === days ? '%s day' : '%s days').replace('%s', days)
            );
        }
        if (hours) {
            parts.push(
                Translator.translate(1 === hours ? '%s hour' : '%s hours').replace('%s', hours)
            );
        }
        if (minutes) {
            parts.push(
                Translator.translate(1 === minutes ? '%s minute' : '%s minutes').replace('%s', minutes)
            );
        }

        return parts.join(' ');
    },

    /**
     * Chain postcode detection methods until any returns results
     */
    chainCallbacks: function (enabledDiscoveryMethods) {
        var dfd = jQuery.Deferred();

        var index = 0;
        function runNext(previousError) {
            if (enabledDiscoveryMethods[index]) {
                enabledDiscoveryMethods[index++]().then(success, runNext);
            } else {
                dfd.reject(previousError);
            }
        }

        function success(postcode) {
            dfd.resolve(postcode);
        }

        runNext();

        return dfd.promise();
    },

    getIpLocation: function () {
        if (this.ipLocationDfd) {
            return this.ipLocationDfd.promise();
        }

        var dfd = this.ipLocationDfd = jQuery.Deferred();

        // use POST to eliminate full page cache
        jQuery.ajax(this.getLocationURL, {method: 'post', dataType: 'json'})
            .done(function (result) {
                if (result.postcode) {
                    // postcode, city, country
                    delete result.error;
                    delete result.message;
                    result.source = Porterbuddy.SOURCE_IP;
                    dfd.resolve(result);
                } else {
                    dfd.reject(result.message);
                }
            }).fail(function () {
                dfd.reject('AJAX request error');
        });

        return dfd.promise();
    },

    getBrowserLocation: function () {
        if (this.browserLocationDfd) {
            return this.browserLocationDfd.promise();
        }

        var dfd = this.browserLocationDfd = jQuery.Deferred();

        this.getBrowserCoordinates()
            .done(function (latlng) {
                this.geocodeLocation({'location': latlng})
                    .done(function (location) {
                        location.source = Porterbuddy.SOURCE_BROWSER;
                        dfd.resolve(location);
                    })
                    .fail(function (reason) {
                        dfd.reject(reason);
                    });
            }.bind(this))
                .fail(function () {
                dfd.reject('Browser location API failed');
            });

        return dfd.promise();
    },

    geocodeLocation: function(request) {
        var key = JSON.stringify(request);
        if (this.geocodeDfd[key]) {
            return this.geocodeDfd[key].promise();
        }

        var dfd = this.geocodeDfd[key] = jQuery.Deferred();
        dfd.always(function () {
            // once complete, stop caching this call
            delete this.geocodeDfd[key];
        }.bind(this));

        // TODO: move this check to loadMaps to run only once
        // detect maps auth failure (bad API key) and reject
        var origAuthFailure = window.gm_authFailure;
        window.gm_authFailure = function () {
            dfd.reject('Google maps auth failure');

            if (origAuthFailure) {
                origAuthFailure();
            }
        };

        this.loadMaps(this.mapsApiKey)
            .done(function () {
                this.geocoder = this.geocoder || new google.maps.Geocoder;
                this.geocoder.geocode(request, function (results, status) {
                    if (status !== 'OK' || !results) {
                        dfd.reject('No results');
                        return;
                    }

                    var postcode = null,
                        city = '',
                        country = '';
                    jQuery.each(results[0].address_components, function (index, component) {
                        if (-1 !== component.types.indexOf('postal_code')) {
                            postcode = component.long_name;
                        }
                        if (-1 !== component.types.indexOf('postal_town')) {
                            city = component.long_name;
                        }
                        if (-1 !== component.types.indexOf('country')) {
                            country = component.long_name;
                        }
                    });

                    if (null === postcode) {
                        dfd.reject('Unknown postcode after geocoding');
                        return;
                    }

                    dfd.resolve({
                        postcode: postcode,
                        city: city,
                        country: country
                    });
                });
            }.bind(this))
            .fail(function () {
                dfd.reject('Cannot load Google maps');
            });

        return dfd.promise();
    },

    /**
     * Gets user coordinates
     * @returns {*}
     */
    getBrowserCoordinates: function () {
        if (this.browserCoordinatesDfd) {
            return this.browserCoordinatesDfd.promise();
        }

        var dfd = this.browserCoordinatesDfd = jQuery.Deferred();

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function (position) {
                dfd.resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
            }, function (error) {
                // error.code, error.message
                dfd.reject(error);
            });
        } else {
            dfd.reject('Geolocation is not supported');
        }

        return dfd.promise();
    },

    /**
     * Loads maps if not loaded and returns deferred object
     * @returns {*}
     */
    loadMaps: function (mapsApiKey) {
        if ('undefined' === typeof window.google || !'maps' in window.google) {
            return jQuery.getScript(
                'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(mapsApiKey)
            );
        } else {
            // resolve immediately
            return jQuery.when();
        }
    },

    changeLocation: function () {
        this.$popupPostcode.val(this.location && this.location.postcode);
        Validation.reset(this.$popupPostcode.get(0));
        this.hideMessage();
        this.openPopup();
    },

    saveChangedLocation: function () {
        var postcode = this.$popupPostcode.val();

        if (!Validation.validate(this.$popupPostcode.get(0))) {
            setTimeout(function () {
                this.updatePopupHeight();
            }.bind(this), 1000);

            return;
        }

        this.$popupSave.prop('disabled', true);

        this.getAvailability(postcode)
            .done(function (result) {
                // set imprecise location, geocode more details later
                var location = {
                    postcode: postcode,
                    city: '',
                    country: this.defaultCountry,
                    source: Porterbuddy.SOURCE_USER
                };
                this.rememberLocation(location);
                this.setCurrentLocation(location);
                this.availabilitySuccess(result);
                this.closePopup();

                // geocode city and country name
                this.geocodeLocation({
                    address: 'country ' + this.defaultCountry + ', postal code ' + postcode,
                    region: this.defaultCountry
                }).done(function (geocodedLocation) {
                    var currentLocation = this.location;
                    currentLocation.city = geocodedLocation.city;
                    currentLocation.country = geocodedLocation.country;

                    this.rememberLocation(currentLocation);
                    this.setCurrentLocation(currentLocation);
                }.bind(this));
            }.bind(this))
            .fail(function (message) {
                var newLocation = {
                    postcode: postcode,
                    location: postcode,
                    city: '',
                    country: ''
                };

                // default error message
                var error = this.textDeliveryUnavailableTemplate.evaluate(newLocation);
                if (message) {
                    try {
                        var template = new Template(message);
                        error = template.evaluate(newLocation);
                    } catch (error) {
                    }
                }

                this.showMessage(error, 'error');
            }.bind(this))
            .always(function () {
                this.$popupSave.prop('disabled', false);
            }.bind(this));
    },

    // Popup related methods
    /**
     * @override
     */
    openPopup: function () {
        this.popup = new Window({
            className: "alphacube",
            destroyOnClose: true,
            draggable: false,
            minimizable: false,
            maximizable: false,
            showEffect: Element.show,
            hideEffect: Element.hide,
            onClose: function () {
                this.$popup.hide();
            }.bind(this)
        });
        this.popup.setContent(this.popupId, true, true);
        this.popup.showCenter(true);
    },

    /**
     * @override
     */
    updatePopupHeight: function () {
        this.popup.updateHeight();
    },

    /**
     * @override
     */
    closePopup: function () {
        if (this.popup) {
            this.popup.close();
        }
    },

    hideMessage: function () {
        this.$message.removeClass(function (index, className) {
            return (className.match (/(^|\s)alert-\S+/g) || []).join(' ');
        });
        this.$message.hide();
    },

    showMessage: function (message, level) {
        level = level || 'info';
        this.$message.removeClass(function (index, className) {
            return (className.match (/(^|\s)alert-\S+/g) || []).join(' ');
        }).addClass('alert-' + level);
        this.$message.html(message).show();
        this.updatePopupHeight();
    }
});

/**
 * Porterbuddy Checkout Widget
 */
window.PorterbuddyWidget = Class.create({
    initialize: function (data) {
        this.$element = jQuery(jQuery('input[name=shipping_method]').closest('form')[0] ||
            jQuery('input[name=shipping_method]').closest('div')[0]);

        this.initOptions(data);
        this.initRates();
        this.initExtensions();
        this.initWidget();
        this.initRefresh();
    },

    initOptions: function (options) {
        this.formKey = options.formKey;
        this.showTimeslots = options.showTimeslots;
        this.price = options.dates.lowestPrice;
        this.onlyPrice = options.dates.onlyPrice;
        this.availability = options.dates.availability;
        this.expiryTime = (new Date()).getTime() + (options.dates.availability.timeRemaining *60*1000)

        this.setDates(options.dates.dates);
        this.widgetHtml = options.widgetHtml;
        this.optionsDelay = options.optionsDelay || 100;
        this.optionsSaveUrl = options.optionsSaveUrl;
        this.refreshUrl = options.refreshUrl;
        this.refreshOptionsTimeout = options.refreshOptionsTimeout;
        this.defaultCountry = options.defaultCountry;
        this.setCheckoutCookie(options.postcode);

        this.$widget = jQuery(this.widgetHtml);
        this.$selectedDate = this.$widget.find('.selected-date');
        this.$timeslots = this.$widget.find('.porterbuddy-widget-timeslots');
        this.timeslotTemplate = new Template(this.$timeslots.html());
        this.$timeslots.html(''); // clear template

        this.$titleText = this.$widget.find('.porterbuddy-widget-title');
        this.titleTemplate = new Template(this.$titleText.html());
        this.$titleText.html(''); // clear template

        this.$groupRate = this.$widget.find('#s_method_porterbuddy');
        this.$return = this.$widget.find('#porterbuddy_return');
        this.returnAvailable = this.$return.length > 0;
        this.$leaveDoorstep = this.$widget.find('#porterbuddy_leave_doorstep');
        this.$comment = this.$widget.find('#porterbuddy_comment');
    },

    initRates: function () {
        this.$allRates = this.$element.find('input[name=shipping_method]');
        this.$porterbuddyRates = this.$allRates.filter('[value^="' + Porterbuddy.CARRIER_CODE + '_"]');
    },

    /**
     * @override
     */
    initExtensions: function () {
        // extension point
    },

    initRefresh: function () {
        if (this.refreshOptionsTimeout > 0) {
            // clear on re-init
            if (window.PorterbuddyWidget.refreshIntervalId) {
                clearInterval(window.PorterbuddyWidget.refreshIntervalId);
            }

            window.PorterbuddyWidget.refreshIntervalId = setInterval(function () {
                // TODO: clear interval when not applicable
                this.refresh();
            }.bind(this), this.refreshOptionsTimeout*60*1000);
        }
    },

    setCheckoutCookie: function(postcode){
      if(null != postcode){
        var location = {
            postcode: postcode,
            city: '',
            country: this.defaultCountry,
            source: Porterbuddy.SOURCE_USER
        };
        Porterbuddy.utilities.setCookie(Porterbuddy.COOKIE, JSON.stringify(location), 90);

      }

    },
    /**
     * @api
     */
    refresh: function () {
        // request new Porterbuddy rates
        jQuery.ajax(this.refreshUrl, {dataType: 'json'})
            .done(function (dates) {
                var oldTimeslotsByValue = jQuery.extend({}, this.timeslotsByValue);
                this.price = dates.lowestPrice;
                this.onlyPrice = dates.onlyPrice;
                this.setDates(dates.dates);

                // delete old rate fields, add new rate fields
                var diffTimeslots = this.diffTimeslots(oldTimeslotsByValue, this.timeslotsByValue);
                jQuery.each(diffTimeslots.added, function (index, timeslotInfo) {
                    this.addNewRate(timeslotInfo);
                }.bind(this));
                jQuery.each(diffTimeslots.deleted, function (index, timeslotInfo) {
                    this.deleteOldRate(timeslotInfo);
                }.bind(this));

                // check if selected date is missing, selectDate(firstDate)
                if (!(this.selectedDateCode in this.dates)) {
                    this.selectDate(null);
                }

                // select first available if Porterbuddy was selected and became unavailable
                var $selectedRate = this.$allRates.filter(':checked');
                var timeslotExists = $selectedRate.val() in this.timeslotsByValue;
                if (this.isPorterbuddyRate($selectedRate) && !timeslotExists) {
                    this.selectTimeslot(null);
                }

                this.render();
            }.bind(this));

        // filter Porterbuddy
        // compare with current list, add/delete, render
    },

    diffTimeslots: function (before, after) {
        var deleted = {};
        var added = jQuery.extend({}, after);
        jQuery.each(before, function (code, timeslot) {
            if (!(code in after)) {
                deleted[code] = timeslot;
            } else {
                delete added[code];
            }
        });
        return {
            deleted: deleted,
            added: added
        };
    },

    deleteOldRate: function (timeslotInfo) {
        this.$porterbuddyRates.filter('[value="' + timeslotInfo.timeslot.value + '"]').closest('li').remove();

        this.initRates();
    },

    addNewRate: function (timeslotInfo) {
        // clone any Porterbuddy input, set value and label
        var $donor = this.$porterbuddyRates.last().closest('li');
        var $newRate = $donor.clone();
        var id = 's_method_' + timeslotInfo.timeslot.value;
        $newRate
            .find('input:radio')
            .val(timeslotInfo.timeslot.value)
            .attr('id', id);
        $newRate.find('label')
            .attr('for', id)
            .html(timeslotInfo.timeslot.label);
        $newRate.insertAfter($donor);

        this.initRates();
    },

    setDates: function (dates) {
        this.dates = dates;

        this.timeslotsByValue = {};
        jQuery.each(this.dates, function (dateCode, date) {
            jQuery.each(date.timeslots, function (timeslotIndex, timeslot) {
                this.timeslotsByValue[timeslot.value] = {
                    timeslot: timeslot,
                    date: dateCode
                };
            }.bind(this));
        }.bind(this));
    },

    initWidget: function () {
        var that = this;
        var $selectedRate = this.$allRates.filter(':checked');

        // bind widget events - date prev-next, timeslot click
        this.$widget.find('.prev-date').click(this.setPrevDate.bind(this));
        this.$widget.find('.next-date').click(this.setNextDate.bind(this));
        this.$timeslots.on('click', '.porterbuddy-widget-timeslot', function () {
            that.selectTimeslot(jQuery(this).data('value'));
        });
        this.$allRates.click(function (e, internal) {
            var $rate = jQuery(this);
            if (!internal) {
                that.selectRate($rate);
            }
            that.$groupRate.prop('checked', that.isPorterbuddyRate($rate));
        });

        // group checkbox
        if (this.isPorterbuddyRate($selectedRate)) {
            that.$groupRate.prop('checked', true);
        }
        this.$groupRate.click(function () {
            // if selected rate is not porterbuddy, select first timeslot
            if (-1 === that.$porterbuddyRates.index(this)) {
                that.selectTimeslot(null);
            }
        });

        // return checkbox
        if (this.isPorterbuddyRate($selectedRate) && $selectedRate.val().match(/_return$/)) {
            this.$return.prop('checked', true);
        }
        this.$return.change(function () {
            if (that.selectedTimeslot) {
                var baseCode = that.selectedTimeslot.value.replace(/_return$/, '');
                if (that.$return.is(':checked')) {
                    that.selectTimeslot(baseCode + '_return');
                } else {
                    that.selectTimeslot(baseCode);
                }
            } else {
                // first
                that.selectTimeslot(null);
            }
        });

        // leave at doorstep checkbox, courier comment
        var delayedOptionsSave = Porterbuddy.utilities.debounce(this.onOptionsChange.bind(this), this.optionsDelay);
        this.$leaveDoorstep.change(delayedOptionsSave);
        this.$comment.on('propertychange keyup change input paste blur', delayedOptionsSave);

        // initial selection
        this.selectDate(null);

        if ($selectedRate.length) {
            this.selectRate($selectedRate);
        }

        // show widget
        this.$porterbuddyRates.closest('li').hide();
        /*var top = 200;
        this.$porterbuddyRates.closest('li').css('position', 'absolute').css('left', '65%').each(function () {
            jQuery(this).css('top', top+=30);
        });*/
        this.$widget.insertAfter(this.$porterbuddyRates.last().closest('li'));

        return this;
    },

    onOptionsChange: function () {
        var dfd = jQuery.Deferred();

        // send, check response.error
        jQuery.ajax(this.optionsSaveUrl, {
            type: 'post',
            dataType: 'json',
            data: {
                form_key: this.formKey,
                leave_doorstep: Number(this.$leaveDoorstep.is(':checked')),
                comment: this.$comment.val()
            }
        }).done(function (data) {
            if (data.error) {
                dfd.reject(data.message);
            } else {
                dfd.resolve();
            }
        }).fail(function () {
            dfd.reject();
        });

        return dfd.promise();
    },

    isPorterbuddyRate: function ($rate) {
        var exp = new RegExp('^' + Porterbuddy.CARRIER_CODE + '_');
        return exp.test($rate.val());
    },

    render: function () {
        this.renderTitle();
        this.renderDate();
        this.renderTimeslots();
        this.renderClasses();

        return this;
    },

    getFormattedPrice: function(){

      return (this.selectedTimeslot?this.selectedTimeslot.price:(this.onlyPrice?this.price:Translator.translate('from') + ' ' + this.price));
    },

    renderTitle: function() {
      var formattedPrice = this.getFormattedPrice();
      var params = {
          price: formattedPrice,
          date: this.availability.humanDate,
          countdown: Porterbuddy.utilities.getCounterText(this.availability.date, this.availability.timeRemaining)
      };
      this.availability.timeRemaining = (this.expiryTime - (new Date()).getTime())/60000;

      this.$titleText.html(this.titleTemplate.evaluate(params));

      if (this.availability.timeRemaining > 0) {
          // prevent multiple timers
          clearTimeout(this.availabilityTimer);
          // revisit in a minute
          this.availabilityTimer = setTimeout(this.renderTitle.bind(this), 60*1000);
      } else {
          this.refresh();
      }
    },

    renderDate: function () {
        this.$selectedDate
            .html(this.selectedDate.label)
            .attr('data-datetime', this.selectedDate.datetime);

        var $prevDate = this.$widget.find('.prev-date');
        if (!this.prevDateAvailable()) {
            $prevDate.html('<span>' + Translator.translate('Today') + '</span>');
        } else {
            $prevDate.html('<span>' + Translator.translate('Previous Day') + '</span>');
        }

        return this;
    },

    renderTimeslots: function () {
        var timeslots = this.getVisibleTimeslots();
        var timeslotsHtml = '';
        jQuery.each(timeslots, function (code, timeslot) {
            timeslotsHtml += this.timeslotTemplate.evaluate(timeslot);
        }.bind(this));
        this.$timeslots.html(timeslotsHtml);

        return this;
    },

    renderClasses: function () {
        var that = this;
        this.$widget
            .toggleClass('porterbuddy-widget-item-selected', Boolean(this.selectedTimeslot));
        this.$widget.find('.prev-date')
            .toggleClass('available', this.prevDateAvailable())
            .toggleClass('unavailable', !this.prevDateAvailable());
        this.$widget.find('.next-date')
            .toggleClass('available', this.nextDateAvailable())
            .toggleClass('unavailable', !this.nextDateAvailable());
        this.$timeslots.find('.porterbuddy-widget-timeslot').each(function (index) {
            var $this = jQuery(this);
            var active = Boolean(that.selectedTimeslot && $this.data('value') === that.selectedTimeslot.value);
            jQuery(this).toggleClass('active', active);
        });

        return this;
    },

    /**
     * @api
     */
    setPrevDate: function () {
        var dateCode = this.getPrevDateCode();
        if (false !== dateCode) {
            this.selectDate(dateCode);
            this.selectTimeslot(null);
        }

        return this;
    },

    /**
     * @api
     */
    setNextDate: function () {
        var dateCode = this.getNextDateCode();
        if (false !== dateCode) {
            this.selectDate(dateCode);
            this.selectTimeslot(null);
        }

        return this;
    },

    /**
     * @api
     * @returns {boolean}
     */
    prevDateAvailable: function () {
        return false !== this.getPrevDateCode();
    },

    /**
     * @api
     * @returns {boolean}
     */
    nextDateAvailable: function () {
        return false !== this.getNextDateCode();
    },

    getPrevDateCode: function () {
        var keys = jQuery.map(this.dates, function (element, index) {
            return index;
        });
        var pos = keys.indexOf(this.selectedDateCode);

        if (pos > 0) {
            return keys[pos-1];
        } else {
            return false;
        }
    },

    getNextDateCode: function () {
        var keys = jQuery.map(this.dates, function (element, index) {
            return index;
        });
        var pos = keys.indexOf(this.selectedDateCode);
        if (-1 !== pos && pos < keys.length-1) {
            return keys[pos+1];
        } else {
            return false;
        }
    },

    /**
     * @api
     * @param dateCode
     * @returns {Window.PorterbuddyWidget}
     */
    selectDate: function (dateCode) {
        if (null === dateCode) {
            // find first
            for (dateCode in this.dates) {
                if (this.dates.hasOwnProperty(dateCode)) {
                    break;
                }
            }
        }

        if (!(dateCode in this.dates)) {
            throw new Error('Invalid date index ' + dateCode);
        }
        if (this.selectedDateCode !== dateCode) {
            this.selectedDateCode = dateCode;
            this.selectedDate = this.dates[this.selectedDateCode];
            this.render();
        }

        return this;
    },

    /**
     * @api
     * @param code
     * @returns {Window.PorterbuddyWidget}
     */
    selectTimeslot: function (code) {
        var timeslots = this.getVisibleTimeslots();

        if (null === code) {
            // find first
            for (code in timeslots) {
                if (timeslots.hasOwnProperty(code)) {
                    break;
                }
            }
        }

        // TODO: check/uncheck return checkbox if needed
        if (!(code in timeslots)) {
            throw new Error('Invalid timeslot ' + timeslotIndex);
        }

        this.selectedTimeslot = timeslots[code];
        this.$porterbuddyRates.filter('[value="' + code + '"]').trigger('click', true); // internal, don't call selectRate
        this.renderTimeslots();
        this.renderClasses();
        this.renderTitle();

        return this;
    },

    getVisibleTimeslots: function () {
        if (!this.returnAvailable) {
            return this.selectedDate.timeslots;
        }

        // only timeslots with return if selected, or only timeslots without return otherwise
        var returnSelected = this.$return.is(':checked');
        var timeslots = {};
        jQuery.each(this.selectedDate.timeslots, function (code, timeslot) {
            if (timeslot.return === returnSelected) {
                timeslots[code] = timeslot;
            }
        });
        return timeslots;
    },

    /**
     * Native shipping rate updated -> select according widget date and timeslot
     * @api
     */
    selectRate: function ($rate) {
        if (!this.isPorterbuddyRate($rate)) {
            // not a scheduled rate
            this.selectedTimeslot = null;
            this.renderClasses();
            return;
        }

        var value = $rate.val();
        if (value in this.timeslotsByValue) {
            var newRate = this.timeslotsByValue[value];

            if (newRate.date !== this.selectedDateCode) {
                this.selectDate(newRate.date);
            }
            if (newRate.timeslot !== this.selectedTimeslot) {
                this.selectTimeslot(value);
            }
        }

        return this;
    },

    destroy: function () {
        if (this.$widget) {
            this.$widget.remove();
            this.$porterbuddyRates.closest('li').show();
            // TODO: unbind events
        }
    }
});
