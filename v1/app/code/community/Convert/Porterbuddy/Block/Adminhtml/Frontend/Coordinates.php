<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Block_Adminhtml_Frontend_Coordinates
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    protected function _construct()
    {
        $this->helper = Mage::helper('convert_porterbuddy');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $apiKey = $this->helper->getMapsApiKey();
        $zoom = $this->helper->getMapsZoom();

        $storeLocation = json_encode($this->helper->getStoreLocation());
        $defaultLocation = json_encode($this->helper->getDefaultLocation());
        $defaultZoom = $this->helper->getDefaultZoom();

        $html = $element->getElementHtml();

        if (!$apiKey) {
            $url = $this->getUrl(
                'adminhtml/system_config/edit',
                array('section' => 'carriers', '_fragment' => 'carriers_' . Convert_Porterbuddy_Model_Carrier::CODE . '-head')
            );
            $message = $this->helper->__(
                'Maps API key must be set in %sPorterbuddy Settings%s.',
                '<a href="' . $url . '">',
                '</a>'
            );
            $html .= "<span style='color: red'>$message</span>";
            return $html;
        }

        $htmlId = $element->getHtmlId();

        $html .= <<<HTML
<div id="porterbuddy_map" style="width: 100%; height: 280px;"></div>

<script async defer src="https://maps.googleapis.com/maps/api/js?key={$apiKey}&callback=porterbuddyInit"
  type="text/javascript"></script>
<script type="text/javascript">
function porterbuddyInit() {
    onContainerVisible('shipping_origin', function() {
        initMap();
    });

    function onContainerVisible(targetContainerId, callback) {
        var container = $(targetContainerId + '-head');
        if (!container || container.hasClassName('open')) {
            callback();
        } else {
            var origApplyCollapse = Fieldset.applyCollapse;
            Fieldset.applyCollapse = function(containerId) {
                origApplyCollapse(containerId);
                if (containerId === targetContainerId && container.hasClassName('open')) {
                    // restore original handler
                    Fieldset.applyCollapse = origApplyCollapse;
                    callback();
                }
            };
        }
    }

    function initMap() {
        var map,
            marker,
            element,
            center,
            coords,
            htmlId = '$htmlId',
            zoom = $zoom,
            storeLocation = $storeLocation,
            defaultLocation = $defaultLocation,
            defaultZoom = $defaultZoom;

        element = $(htmlId);
        coords = parseCoords();
        if (coords) {
            center = coords;
        } else if (storeLocation) {
            center = storeLocation;
        } else {
            center = defaultLocation;
            zoom = defaultZoom;
        }

        map = new google.maps.Map($('porterbuddy_map'), {
            center: center,
            zoom: zoom
        });

        if (coords) {
            placeMarker(coords);
        } else {
            enableMarkerPlacing();
        }

        function placeMarker(position) {
            marker = new google.maps.Marker({
                position: position,
                map: map,
                draggable: true
            });
            map.setCenter(position);

            google.maps.event.addListener(marker, 'dragend', function(e) {
                saveCoords(e.latLng);
            });
        }

        function enableMarkerPlacing() {
            google.maps.event.addListenerOnce(map, 'click', function(e) {
                placeMarker(e.latLng);
                saveCoords(e.latLng);
            }.bind(this));
        }

        function parseCoords() {
            var parts = element.value.split(',', 2);
            if (parts.length == 2 && !isNaN(parseFloat(parts[0])) && !isNaN(parseFloat((parts[1])))) {
                return {lat: parseFloat(parts[0]), lng: parseFloat(parts[1])};
            } else {
                return null;
            }
        }

        function saveCoords(coords) {
            element.value = coords.lat() + ',' + coords.lng();
        }
    }
}
</script>
HTML;

        return $html;
    }
}
