<?php

class Mmx_Importer_Helper_XmlTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Helper_Xml
     */
    protected $helper;
    protected $xml_filename = '/Users/gn/Sites/magento/magento-mmx/SageData/In/IndigoSerialised.xml';
    protected $xpath = '/Report/table1/Detail_Collection/Detail';

    public function setUp() {

        $this->helper = new Mmx_Importer_Helper_Xml();
        $this->helper->setXmlFilename($this->xml_filename)
                ->setXpath($this->xpath);
    }

    public function testGetXmlFilename() {
        $this->assertEquals($this->xml_filename, $this->helper->getXmlFilename());
    }

    public function testGetXpath() {
        $this->assertEquals($this->xpath, $this->helper->getXpath());
    }

}
