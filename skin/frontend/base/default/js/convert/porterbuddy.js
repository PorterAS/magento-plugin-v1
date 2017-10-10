'use strict';

/**
 * Porterbuddy constants
 */
function Porterbuddy() {}
Porterbuddy.CARRIER_CODE = 'cnvporterbuddy';
Porterbuddy.METHOD_ASAP = 'asap';
Porterbuddy.METHOD_SCHEDULED = 'scheduled';

Porterbuddy.utilities = {
    debounce: function(callback, timeout) {
        var timer;
        return function() {
            var that = this;
            var args = arguments;

            if (timer) {
                clearTimeout(timer);
                timer = null;
            }

            timer = setTimeout(function() {
                timer = null;
                callback.apply(that, args);
            }, timeout);
        };
    }
};

/**
 * Porterbuddy Widget
 *
 * TODO: jQuery widget
 */
window.PorterbuddyWidget = Class.create({
    initialize: function(data) {
        this.initOptions(data);

        this.$element = jQuery('input[name=shipping_method]');

        this.$allRates = this.$element;
        this.$porterbuddyRates = this.$allRates.filter('[value^="' + Porterbuddy.CARRIER_CODE + '_"]');
        this.$asapRate = this.$allRates.filter('[value="' + Porterbuddy.CARRIER_CODE + '_' + Porterbuddy.METHOD_ASAP + '"]');
        this.$scheduledRates = this.$allRates.filter('[value^="' + Porterbuddy.CARRIER_CODE + '_' + Porterbuddy.METHOD_SCHEDULED + '_' + '"]');

        this.$widget = null;
        this.$timeslots = null;
        this.$return = null;

        this.insertWidget();
    },

    initOptions: function(options) {
        this.formKey = options.formKey;
        this.dates = options.dates;
        this.widgetHtml = options.widgetHtml;
        this.timeslotTemplate = new Template(options.timeslotTemplate);
        this.optionsDelay = options.optionsDelay || 100;
        this.optionsSaveUrl = options.optionsSaveUrl;
    },

    insertWidget: function() {
        var that = this;

        if (!this.$scheduledRates.length) {
            return this;
        }

        this.$widget = jQuery(this.widgetHtml);
        this.$selectedDate = this.$widget.find('#selected-date');
        this.$timeslots = this.$widget.find('#timeslots');
        this.$groupRate = this.$widget.find('#s_method_porterbuddy');
        this.$return = this.$widget.find('#porterbuddy_return');
        this.returnAvailable = this.$return.length > 0;
        this.$leaveDoorstep = this.$widget.find('#porterbuddy_leave_doorstep');
        this.$comment = this.$widget.find('#porterbuddy_comment');

        var $selectedRate = this.$allRates.filter(':checked');

        // bind widget events - date prev-next, timeslot click
        this.$widget.find('.prev-date').click(this.setPrevDate.bind(this));
        this.$widget.find('.next-date').click(this.setNextDate.bind(this));
        this.$timeslots.on('click', '.porterbuddy-widget-timeslot', function() {
            that.selectTimeslot(jQuery(this).data('value'));
        });
        this.$allRates.click(function(e, internal) {
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
        this.$groupRate.click(function() {
            // if selected rate is not porterbuddy, select first timeslot
            if (-1 === that.$porterbuddyRates.index(this)) {
                that.selectTimeslot(null);
            }
        });

        // return checkbox
        if (this.isPorterbuddyRate($selectedRate) && $selectedRate.val().match(/_return$/)) {
            this.$return.prop('checked', true);
        }
        this.$return.change(function() {
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
        this.$scheduledRates.closest('li').css('position', 'absolute').css('left', '50%').each(function() {
            jQuery(this).css('top', top+=30);
        });*/
        this.$widget.insertAfter(this.$scheduledRates.last().closest('li'));

        return this;
    },

    onOptionsChange: function() {
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
        }).done(function(data) {
            if (data.error) {
                dfd.reject(data.message);
            } else {
                dfd.resolve();
            }
        }).fail(function() {
            dfd.reject();
        });

        return dfd.promise();
    },

    isPorterbuddyRate: function($rate) {
        var exp = new RegExp('^' + Porterbuddy.CARRIER_CODE + '_');
        return exp.test($rate.val());
    },

    render: function() {
        this.renderDate();
        this.renderTimeslots();
        this.renderClasses();

        return this;
    },

    renderDate: function() {
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

    renderTimeslots: function() {
        var timeslots = this.getVisibleTimeslots();
        var timeslotsHtml = '';
        jQuery.each(timeslots, function(code, timeslot) {
            timeslotsHtml += this.timeslotTemplate.evaluate(timeslot);
        }.bind(this));
        this.$timeslots.html(timeslotsHtml);

        return this;
    },

    renderClasses: function() {
        var that = this;
        this.$widget
            .toggleClass('porterbuddy-widget-item-selected', Boolean(this.selectedTimeslot));
        this.$widget.find('.prev-date')
            .toggleClass('available', this.prevDateAvailable())
            .toggleClass('unavailable', !this.prevDateAvailable());
        this.$widget.find('.next-date')
            .toggleClass('available', this.nextDateAvailable())
            .toggleClass('unavailable', !this.nextDateAvailable());
        this.$timeslots.find('.porterbuddy-widget-timeslot').each(function(index) {
            var $this = jQuery(this);
            var active = Boolean(that.selectedTimeslot && $this.data('value') === that.selectedTimeslot.value);
            jQuery(this).toggleClass('active', active);
        });

        return this;
    },

    /**
     * @api
     */
    setPrevDate: function() {
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
    setNextDate: function() {
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
    prevDateAvailable: function() {
        return false !== this.getPrevDateCode();
    },

    /**
     * @api
     * @returns {boolean}
     */
    nextDateAvailable: function() {
        return false !== this.getNextDateCode();
    },

    getPrevDateCode: function() {
        var keys = jQuery.map(this.dates, function(element, index) {
            return index;
        });
        var pos = keys.indexOf(this.selectedDateCode);

        if (pos > 0) {
            return keys[pos-1];
        } else {
            return false;
        }
    },

    getNextDateCode: function() {
        var keys = jQuery.map(this.dates, function(element, index) {
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
    selectDate: function(dateCode) {
        if (null === dateCode) {
            // find first
            for (dateCode in this.dates) {
                if (this.dates.hasOwnProperty(dateCode)) {
                    break;
                }
            }
        }

        if (!dateCode in this.dates) {
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
    selectTimeslot: function(code) {
        var timeslots = this.getVisibleTimeslots();

        if (null === code) {
            // find first
            for (code in timeslots) {
                if (timeslots.hasOwnProperty(code)) {
                    break;
                }
            }
        }

        if (!code in timeslots) {
            throw new Error('Invalid timeslot ' + timeslotIndex);
        }

        this.selectedTimeslot = timeslots[code];
        this.$porterbuddyRates.filter('[value="' + code + '"]').trigger('click', true); // internal, don't call selectRate
        this.renderTimeslots(); // TODO: check
        this.renderClasses();

        return this;
    },

    getVisibleTimeslots: function() {
        if (!this.returnAvailable) {
            return this.selectedDate.timeslots;
        }

        // only timeslots with return if selected, or only timeslots without return otherwise
        var returnSelected = this.$return.is(':checked');
        var timeslots = {};
        jQuery.each(this.selectedDate.timeslots, function(code, timeslot) {
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
    selectRate: function($rate) {
        if (!this.isPorterbuddyRate($rate)) {
            // not a scheduled rate
            this.selectedTimeslot = null;
            this.renderClasses();
            return;
        }

        var value = $rate.val();
        var found = false;
        jQuery.each(this.dates, function(dateCode, date) {
            jQuery.each(date.timeslots, function(timeslotIndex, timeslot) {
                if (value === timeslot.value) {
                    // found date
                    if (dateCode !== this.selectedDateCode) {
                        this.selectDate(dateCode);
                    }
                    if (timeslot !== this.selectedTimeslot) {
                        this.selectTimeslot(value);
                    }
                    found = true;
                    return false;
                }
            }.bind(this));
            if (found) {
                return false;
            }
        }.bind(this));

        return this;
    },

    destroy: function() {
        if (this.$widget) {
            this.$widget.remove();
            this.$porterbuddyRates.closest('li').show();
            // TODO: unbind events
        }
    }
});

/**
 * Porterbuddy Popup
 */
window.PorterbuddyPopup = Class.create({
    initialize: function(data) {
        this.initOptions(data);

        this.geocoder = null;
        this.map = null;
        this.marker = null;

        this.openPopup();
        this.initEvents();
        this.insertMap();
    },

    initOptions: function(options) {
        this.formKey = options.formKey;
        this.orderNumber = options.orderNumber;
        this.protectCode = options.protectCode;
        this.mapsApiKey = options.mapsApiKey;
        this.deliveryLocation = options.deliveryLocation;
        this.deliveryAddress = options.deliveryAddress;
        this.addressDelay = options.addressDelay || 100;
        this.storeLocation = options.storeLocation;
        this.storeAddress = options.storeAddress;
        this.zoom = options.zoom;
        this.defaultLocation = options.defaultLocation;
        this.defaultZoom = options.defaultZoom;
        this.markerOptions = options.markerOptions;
        this.locationSaveUrl = options.locationSaveUrl;
    },

    initEvents: function() {
        this.$map = jQuery('#porterbuddy-map');
        this.$message = jQuery('#porterbuddy-message');
        this.$address = jQuery('#porterbuddy-address');
        this.$saveButton = jQuery('#porterbuddy-save');
        this.$closeButton = jQuery('#porterbuddy-close');

        this.canClose = false;

        this.$address.on(
            'propertychange keyup change input paste blur',
            Porterbuddy.utilities.debounce(this.onAddressChange.bind(this), this.addressDelay)
        );
        this.$saveButton.click(this.onSaveButtonClick.bind(this));
        this.$closeButton.click(this.onCloseButtonClick.bind(this));
    },

    onAddressChange: function() {
        if (!this.map) {
            return;
        }
        // TODO: show loader

        var newAddress = this.$address.val();
        this.geocode(newAddress)
            .done(function(location) {
                this.deliveryAddress = newAddress;
                this.deliveryLocation = location;
                if (this.marker) {
                    this.moveMarker(location);
                    this.marker.setTitle(newAddress);
                } else {
                    this.placeMarker(location);
                }
            }.bind(this))
            .fail(function() {
                this.showMessage(Translator.translate('We could not find this location on the map.'));
            }.bind(this));
    },

    onSaveButtonClick: function() {
        if (!this.deliveryLocation) {
            this.showMessage(Translator.translate('Please set location first.'));
            return;
        }

        this.freezeMap();
        this.$address.prop('readonly', true);
        this.$saveButton.prop('disabled', true);

        this.saveLocation(this.deliveryLocation)
            .done(function() {
                this.map.panTo(this.deliveryLocation);
                this.showMessage(Translator.translate('Thank you! Delivery location has been updated.'), 'success');

                this.canClose = true;
                this.$saveButton.hide();
                this.$closeButton.show();
                this.updatePopupHeight();
            }.bind(this))
            .fail(function(reason) {
                this.unfreezeMap();
                this.$address.prop('readonly', false);
                this.$saveButton.prop('disabled', false);

                var message = Translator.translate('An error occurred when saving delivery location.');
                if (reason) {
                    message += '\n' + reason;
                }
                this.showMessage(message, 'error');
            }.bind(this));
    },

    onCloseButtonClick: function() {
        if (!this.canClose) {
            this.showMessage(Translator.translate('Please set location first.'));
            return false;
        } else {
            this.closePopup();
        }
    },

    // Map related methods
    insertMap: function() {
        jQuery.when(this.loadMaps(), this.oncePopupOpened()).done(function() {
            if (this.deliveryLocation) {
                // location selected
                this.createMap(this.deliveryLocation, this.zoom);
                this.placeMarker(this.deliveryLocation);
            } else if (this.storeLocation) {
                this.createMap(this.storeLocation, this.zoom);
                // this.showInfo('Calculating delivery coordinates...')
                this.geocode(this.deliveryAddress)
                    .done(function(location) {
                        this.deliveryLocation = location;
                        this.placeMarker(location);
                    }.bind(this))
                    .fail(function() {
                        this.showMessage(Translator.translate('We could not automatically find your location on the map. Please place a marker.'));
                        this.enableMarkerPlacing();
                    }.bind(this));
            } else {
                // TODO: show loader
                // geocode delivery, create map, place marker
                // or geocode store, create map, enable placing
                // or create map with default center, enable placing
                this.geocode(this.deliveryAddress)
                    .done(function(location) {
                        this.createMap(location, this.zoom);
                        this.deliveryLocation = location;
                        this.placeMarker(location);
                    }.bind(this))
                    .fail(function(status) {
                        this.geocode(this.storeAddress)
                            .done(function(location) {
                                this.createMap(location, this.zoom);
                                this.showMessage(Translator.translate('We could not automatically find your location on the map. Please place a marker.'));
                                this.enableMarkerPlacing();
                            }.bind(this))
                            .fail(function(status) {
                                this.createMap(this.defaultLocation, this.defaultZoom);
                                this.showMessage(Translator.translate('We could not automatically find your location on the map. Please place a marker.'));
                                this.enableMarkerPlacing();
                            }.bind(this))
                    }.bind(this));
            }
        }.bind(this));

        return this;
    },

    loadMaps: function() {
        if ('undefined' === typeof window.google || !'maps' in window.google) {
            return jQuery.getScript(
                'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(this.mapsApiKey)
            );
        } else {
            // resolve immediately
            return jQuery.when();
        }
    },

    createMap: function(center, zoom) {
        this.map = new google.maps.Map(this.$map.get(0), {
            center: center,
            zoom: zoom
        });

        return this;
    },

    placeMarker: function(position) {
        var image = {
            url: this.markerOptions.url,
            size: new google.maps.Size(this.markerOptions.size[0], this.markerOptions.size[1]),
            origin: new google.maps.Point(this.markerOptions.origin[0], this.markerOptions.origin[1]),
            anchor: new google.maps.Point(this.markerOptions.anchor[0], this.markerOptions.anchor[1])
        };
        var shape = {
            coords: this.markerOptions.coords,
            type: 'poly'
        };
        this.marker = new google.maps.Marker({
            position: position,
            map: this.map,
            draggable: true,
            icon: image,
            shape: shape,
            title: this.deliveryAddress
        });

        this.map.panTo(position);

        google.maps.event.addListener(this.marker, 'dragend', function(e) {
            this.deliveryLocation = e.latLng;
        }.bind(this));

        return this;
    },

    enableMarkerPlacing: function() {
        google.maps.event.addListenerOnce(this.map, 'click', function(e) {
            this.deliveryLocation = e.latLng;
            this.placeMarker(e.latLng);
        }.bind(this));

        return this;
    },

    moveMarker: function(position) {
        this.marker.setPosition(position);
        this.map.panTo(position);
    },

    freezeMap: function() {
        this.mapReadonly = true;
        this.map.setOptions({draggable: false});
        this.marker.setDraggable(false);
    },

    unfreezeMap: function() {
        this.mapReadonly = false;
        this.map.setOptions({draggable: true});
        this.marker.setDraggable(true);
    },

    geocode: function(address) {
        if (!this.geocoder) {
            this.geocoder = new google.maps.Geocoder();
        }

        var dfd = jQuery.Deferred();
        this.geocoder.geocode({'address': address}, function(results, status) {
            if (status === 'OK') {
                dfd.resolve(results[0].geometry.location);
            } else {
                dfd.reject(status);
            }
        });

        return dfd.promise();
    },

    // Popup related methods
    /**
     * @override
     */
    openPopup: function() {
        this.popup = new Window({
            className: "alphacube",
            destroyOnClose: true,
            //closeOnEsc: false,
            //closable: false,
            closeCallback: function() {
                if (!this.canClose) {
                    this.showMessage(Translator.translate('Please set location first.'));
                }
                return this.canClose;
            }.bind(this),
            draggable: false,
            minimizable: false,
            maximizable: false,
            showEffectOptions: {
                duration: 0.4
            },
            hideEffectOptions:{
                duration: 0.4
            }
        });
        this.popup.setContent('porterbuddy-popup', true, true);
        this.popup.showCenter(true);
    },

    /**
     * @override
     */
    updatePopupHeight: function() {
        this.popup.updateHeight();
    },

    /**
     * @override
     */
    closePopup: function() {
        if (this.popup) {
            this.popup.close();
        }
    },

    /**
     * Listen to popup opened event, resolve
     *
     * @override
     * @returns {Promise}
     */
    oncePopupOpened: function() {
        var dfd = jQuery.Deferred();

        Windows.addObserver({
            onShow: function() {
                dfd.resolve();
            }
        });

        return dfd.promise();
    },

    showMessage: function(message, level) {
        level = level || 'info';
        this.$message.removeClass(function(index, className) {
            return (className.match (/(^|\s)alert-\S+/g) || []).join(' ');
        }).addClass('alert-' + level);
        this.$message.html(message).show();
        this.updatePopupHeight();
    },

    saveLocation: function(latLng) {
        var dfd = jQuery.Deferred();

        // marker loader animation
        this.marker.setAnimation(google.maps.Animation.BOUNCE);

        // send, check response.error
        jQuery.ajax(this.locationSaveUrl, {
            type: 'post',
            dataType: 'json',
            data: {
                form_key: this.formKey,
                order_number: this.orderNumber,
                protect_code: this.protectCode,
                lat: latLng.lat,
                lng: latLng.lng
            }
        }).done(function(data) {
            if (data.error) {
                dfd.reject(data.message);
            } else {
                dfd.resolve();
            }
        }).fail(function() {
            dfd.reject();
        }).always(function() {
            this.marker.setAnimation(null);
        }.bind(this));

        return dfd.promise();
    }
});
