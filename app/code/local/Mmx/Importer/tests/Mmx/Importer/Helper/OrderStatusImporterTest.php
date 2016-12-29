<?php

class Mmx_Importer_Helper_OrderStatusImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Helper_OrderStatusImporter
     */
    protected $helper;

    public function setUp() {

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->helper = new Mmx_Importer_Helper_OrderStatusImporter();
        $this->helper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/OrderStatus.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail');
    }
    
    public function testGetXmlFilename() {
        $file = '/Users/gn/Sites/magento/magento-mmx/SageData/In/OrderStatus.xml';
        $this->assertEquals($file, $this->helper->getXmlFilename());
    }
    
    public function testGetXpath() {
        $this->assertEquals('/Report/table1/Detail_Collection/Detail', $this->helper->getXpath());
    }

    public function testSageToMagentoOrderStatusLookup() {

        $this->assertEquals('processing', $this->helper->sageToMagentoStatus(1));
        $this->assertEquals('processing', $this->helper->sageToMagentoStatus(2));
        $this->assertEquals('processing', $this->helper->sageToMagentoStatus(3));
        $this->assertEquals('processing', $this->helper->sageToMagentoStatus(4));
        $this->assertEquals('processing', $this->helper->sageToMagentoStatus(5));
        
        $this->assertEquals('picking', $this->helper->sageToMagentoStatus(6));
        
        $this->assertEquals('complete', $this->helper->sageToMagentoStatus(7));
        $this->assertEquals('complete', $this->helper->sageToMagentoStatus(8));

        $this->assertEquals('canceled', $this->helper->sageToMagentoStatus(9));
    }

    public function testUpdate() {
        $this->helper->update();
    }

}
