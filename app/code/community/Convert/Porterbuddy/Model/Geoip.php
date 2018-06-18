<?php

class Convert_Porterbuddy_Model_Geoip
{
    protected $cacheType = 'config';

    /**
     * @var Convert_Porterbuddy_Helper_Data
     */
    protected $helper;

    const CACHE_TAG = 'geoip';

    public function __construct(
        array $data = null,
        Convert_Porterbuddy_Helper_Data $helper = null
    ) {
        $this->helper = $helper ?: Mage::helper('convert_porterbuddy');
    }

    /**
     * @param string $ip
     * @return \GeoIp2\Model\City
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     * @throws Convert_Porterbuddy_Exception
     */
    public function getInfo($ip)
    {
        if (!class_exists('GeoIp2\Database\Reader')) {
            throw new Convert_Porterbuddy_Exception($this->helper->__('Geoip database reader is not installed'));
        }

        $cacheId = "GEOIP_INFO_IP_$ip";
        if (Mage::app()->useCache($this->cacheType) && $info = Mage::app()->loadCache($cacheId)) {
            $info = unserialize($info);
        } else {
            // TODO: default locale from config, e.g. 'se'
            $reader = new GeoIp2\Database\Reader(
                $this->getResource()->getDBPath(),
                array('no', 'en')
            );

            try {
                $info = $reader->city($ip);
            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                // cache not found exceptions, throw later
                $info = $e;
            }

            if (Mage::app()->useCache($this->cacheType)) {
                Mage::app()->saveCache(
                    serialize($info),
                    $cacheId,
                    array(Mage_Core_Model_Config::CACHE_TAG, self::CACHE_TAG)
                );
            }
        }

        if ($info instanceof \GeoIp2\Exception\AddressNotFoundException) {
            throw $info;
        }

        return $info;
    }

    /**
     * @return array
     * @throws Convert_Porterbuddy_Exception
     */
    public function update()
    {
        return $this->getResource()->update();
    }

    /**
     * @return Convert_Porterbuddy_Model_Resource_Geoip
     */
    public function getResource()
    {
        return Mage::getSingleton('convert_porterbuddy/resource_geoip');
    }
}
