<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_CronTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Convert_Porterbuddy_Model_Cron|PHPUnit_Framework_MockObject_MockObject
     */
    protected $cron;

    /**
     * @var Convert_Porterbuddy_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var Convert_Porterbuddy_Model_Shipment|PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipment;

    public function setUp()
    {
        // FIXME: bootstrap file
        require_once realpath(__DIR__) . '/../../../../../../../../../../../app/Mage.php';
        Mage::app();

        $this->helper = $this->getMockBuilder(Convert_Porterbuddy_Helper_Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipment = $this->getMockBuilder(Convert_Porterbuddy_Model_Shipment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cron = $this->getMockBuilder(Convert_Porterbuddy_Model_Cron::class)
            ->setMethods(array('getNextOrder'))
            ->setConstructorArgs(array(
                'data' => null,
                'helper' => $this->helper,
                'shipment' => $this->shipment,
            ))
            ->getMock();
    }

    /**
     * @param array $orderData
     * @param bool $createShipment
     * @param string $expectedCase (optional)
     * @dataProvider sendShipmentsProvider
     */
    public function testSendShipments(array $orderData, $createShipment, $expectedCase = null)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getSingleton('sales/order');
        $order->reset();
        $order->setData($orderData);

        $empty = new Varien_Object();

        $this->cron
            ->expects($this->exactly(2))
            ->method('getNextOrder')
            ->willReturnOnConsecutiveCalls($order, $empty);
        $this->helper
            ->method('getCreateShipmentTimeout')
            ->willReturn(5);
        $this->helper
            ->method('getAutoCreateShipment')
            ->willReturn(true);

        if ($createShipment) {
            $this->shipment
                ->expects($this->once())
                ->method('createShipment')
                ->with($order);
            $this->helper
                ->expects($this->once())
                ->method('log')
                ->with(
                    $this->anything(), // message
                    $this->equalTo(array('order_id' => $order->getId(), 'create_case' => $expectedCase)),
                    $this->greaterThan(0) // log level
                );
        } else {
            $this->shipment
                ->expects($this->never())
                ->method('createShipment');
            $this->helper
                ->expects($this->never())
                ->method('log');
        }

        $this->cron->sendShipments();
    }

    public function sendShipmentsProvider()
    {
        $id = 1;
        $justNow = date('Y-m-d H:i:s', strtotime('30 seconds ago'));
        $someTimeAgo = date('Y-m-d H:i:s', strtotime('10 minutes ago'));
        $someLocation = '59.3909072,11.309796';

        return array(
            // user has just paid, no location yet
            array(
                'order' => array(
                    'entity_id' => $id++,
                    'pb_paid_at' => $justNow, // cron runs every minute
                    'pb_user_edited' => 0,
                    'pb_location' => null,
                ),
                'create_shipment' => false,
                'expected_case' => null,
            ),
            // order paid, but user doesn't send location for a while - timeout
            array(
                'order' => array(
                    'entity_id' => $id++,
                    'pb_paid_at' => $someTimeAgo,
                    'pb_user_edited' => 0,
                    'pb_location' => null,
                ),
                'create_shipment' => true,
                'expected_case' => "no user approved location after `5` minutes",
            ),
            // order paid, location set automatically from address book, but user hasn't approved them
            array(
                'order' => array(
                    'entity_id' => $id++,
                    'pb_paid_at' => $justNow,
                    'pb_user_edited' => 0,
                    'pb_location' => $someLocation,
                ),
                'create_shipment' => false,
                'expected_case' => null,
            ),
            // same as above, but after some time - create by timeout
            array(
                'order' => array(
                    'entity_id' => $id++,
                    'pb_paid_at' => $someTimeAgo,
                    'pb_user_edited' => 0,
                    'pb_location' => $someLocation,
                ),
                'create_shipment' => true,
                'expected_case' => "no user approved location after `5` minutes",
            ),
            // users sets location, later payment arrives
            array(
                'order' => array(
                    'entity_id' => $id++,
                    'pb_paid_at' => $justNow,
                    'pb_user_edited' => 1,
                    'pb_location' => $someLocation,
                ),
                'create_shipment' => true,
                'expected_case' => "both user approved location and payment received",
            ),
        );
    }
}
