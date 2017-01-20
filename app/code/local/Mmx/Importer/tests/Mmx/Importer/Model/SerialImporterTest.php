<?php

class Mmx_Importer_Model_SerialImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Model_SerialImporter
     */
    protected $model;

    public function setUp() {

        $xmlHelper = new Mmx_Importer_Helper_XmlSerial();
        $xmlHelper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/IndigoSerialised.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail');

        $this->model = new Mmx_Importer_Model_SerialImporter();
        $this->model->setHelper($xmlHelper);
    }
    
    public function testGetHelper() {
        $this->assertEquals('Mmx_Importer_Helper_XmlSerial', get_class($this->model->getHelper()));
    }

    public function testCreateOption() {
//        $option = $this->model->createOption($serials);
    }
    
    public function testUpdate() {
        
  //      $this->model->update();
        
    }

}
