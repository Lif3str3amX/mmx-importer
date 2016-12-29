<?php

class Mmx_Importer_Helper_SerialImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Helper_SerialImporter
     */
    protected $helper;

    public function setUp() {

        $this->helper = new Mmx_Importer_Helper_SerialImporter();
        $this->helper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/IndigoSerialised.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail')
                ->setSkus(array('INCIENABOM', 'INBTRESERVATION'));
    }
    
    public function testGetXmlFilename() {
        $file = '/Users/gn/Sites/magento/magento-mmx/SageData/In/IndigoSerialised.xml';
        $this->assertEquals($file, $this->helper->getXmlFilename());
    }
    
    public function testGetSkus() {
        $this->assertEquals(array('INCIENABOM', 'INBTRESERVATION'), $this->helper->getSkus());
    }
    
    public function testGetXpath() {
        $this->assertEquals('/Report/table1/Detail_Collection/Detail', $this->helper->getXpath());
    }
    
    public function testGetSerialsBySku() {
        $serials = $this->helper->getSerialsBySku('INBTRESERVATION');
    }

    public function testCreateOption() {
        $serials = $this->helper->getSerialsBySku('INBTRESERVATION');

        $option = $this->helper->createOption($serials);
    }
    
    public function testUpdate() {
        
        $this->helper->update();
        
    }

}
