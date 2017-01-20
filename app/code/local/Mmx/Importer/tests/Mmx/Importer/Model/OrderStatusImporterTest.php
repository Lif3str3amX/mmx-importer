<?php

class Mmx_Importer_Model_OrderStatusImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Model_OrderStatusImporter
     */
    protected $model;
    
    public function setUp() {

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $xmlHelper = new Mmx_Importer_Helper_Xml();
        $xmlHelper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/OrderStatus.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail');

        $this->model = new Mmx_Importer_Model_OrderStatusImporter();
        $this->model->setHelper($xmlHelper);
    }
    
    public function testGetHelper() {
        $this->assertEquals('Mmx_Importer_Helper_Xml', get_class($this->model->getHelper()));
    }

    public function testSageToMagentoOrderStatusLookup() {

        $this->assertEquals('processing', $this->model->sageToMagentoStatus(1));
        $this->assertEquals('processing', $this->model->sageToMagentoStatus(2));
        $this->assertEquals('processing', $this->model->sageToMagentoStatus(3));
        $this->assertEquals('processing', $this->model->sageToMagentoStatus(4));
        $this->assertEquals('processing', $this->model->sageToMagentoStatus(5));
        
        $this->assertEquals('picking', $this->model->sageToMagentoStatus(6));
        
        $this->assertEquals('complete', $this->model->sageToMagentoStatus(7));
        $this->assertEquals('complete', $this->model->sageToMagentoStatus(8));

        $this->assertEquals('canceled', $this->model->sageToMagentoStatus(9));
    }

}
