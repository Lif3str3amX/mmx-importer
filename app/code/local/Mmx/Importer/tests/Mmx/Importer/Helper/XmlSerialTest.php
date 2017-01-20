<?php

class Mmx_Importer_Helper_XmlSerialTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Helper_XmlSerial
     */
    protected $helper;
    protected $xml_filename = '/Users/gn/Sites/magento/magento-mmx/SageData/In/tests/IndigoSerialised.xml';
    protected $xpath = '/Report/table1/Detail_Collection/Detail';

    public function setUp() {

        $this->helper = new Mmx_Importer_Helper_XmlSerial();
        $this->helper->setXmlFilename($this->xml_filename)
                ->setXpath($this->xpath);
    }

    public function testGetSkus() {
        $this->assertContains('INCIENABOM', $this->helper->getSkus());
        $this->assertContains('INBTRESERVATION', $this->helper->getSkus());
    }

    public function testGetSerialsBySkuReturnsPopulatedArray() {
        $serials = $this->helper->getSerialsBySku('INBTRESERVATION');
        $this->assertContains('AA117031', $serials);
    }

    public function testGetSerialsBySkuReturnsEmptyArray() {
        $serials = $this->helper->getSerialsBySku('FAKE-SKU');
        $this->assertEquals(array(), $serials);
    }
    
}
