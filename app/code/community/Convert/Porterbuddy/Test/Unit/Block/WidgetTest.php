<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_Test_Unit_Model_WidgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Model_Quote_Address|PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddress;

    /**
     * @var Mage_Sales_Model_Quote|PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var Convert_Porterbuddy_Block_Widget|PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    public function setUp()
    {
        // FIXME: bootstrap file
        require_once realpath(__DIR__) . '/../../../../../../../../../../../app/Mage.php';
        Mage::app();

        /** @var Mage_Sales_Model_Quote_Address||PHPUnit_Framework_MockObject_MockObject $shippingAddress */
        $this->shippingAddress = $this->getMockBuilder(Mage_Sales_Model_Quote_Address::class)
            ->setMethods(['getData'])
            ->getMock();

        /** @var Mage_Sales_Model_Quote|PHPUnit_Framework_MockObject_MockObject $quote */
        $this->quote = $this->createMock(Mage_Sales_Model_Quote::class);
        $this->quote
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);

        /** @var Convert_Porterbuddy_Block_Widget|PHPUnit_Framework_MockObject_MockObject $block */
        $this->block = $this->getMockBuilder(Convert_Porterbuddy_Block_Widget::class)
            ->setMethods(array('getQuote'))
            ->getMock();
        $this->block
            ->method('getQuote')
            ->willReturn($this->quote);
    }

    public function testSomething()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
