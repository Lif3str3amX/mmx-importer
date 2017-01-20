<?php

class Mmx_Importer_Model_StockImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Model_StockImporter
     */
    protected $model;

    public function setUp() {

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $xmlHelper = new Mmx_Importer_Helper_Xml();
        $xmlHelper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/BTStock.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail');

        $this->model = new Mmx_Importer_Model_StockImporter();
        $this->model->setHelper($xmlHelper)
                ->setWebsiteId(2)
                ->setCategoryId(30);
    }
    
    public function testGetHelper() {
        $this->assertEquals('Mmx_Importer_Helper_Xml', get_class($this->model->getHelper()));
    }

    public function testGetWebsiteId() {
        $this->assertEquals(2, $this->model->getWebsiteId());
    }

    public function testGetCategoryId() {
        $this->assertEquals(30, $this->model->getCategoryId());
    }
    
    public function testCreateInStockProduct() {
        $sku = 'TEST101-' . uniqid();
        
        $product = $this->model->createProduct($sku, 30, 2, 'Test 101 In Stock', 'This is a test IN STOCK product', 'This is a test IN STOCK product', 10);
        $this->assertEquals($sku, $product->getSku());
        $this->assertEquals(10, $this->model->getStockQty($product));
        
        $product->delete();
    }

    public function testCreateOutOfStockProduct() {
        $sku = 'TEST101-' . uniqid();
        
        $product = $this->model->createProduct($sku, 30, 2, 'Test 101 Out Of Stock', 'This is a test OUT OF STOCK product', 'This is a test OUT OF STOCK product', 0);
        $this->assertEquals($sku, $product->getSku());
        $this->assertEquals(0, $this->model->getStockQty($product));

        $product->delete();
    }
    
    public function testUpdateStock() {
        $sku = 'TEST101-' . uniqid();

        $product = $this->model->createProduct($sku, 30, 2, 'Test 101 Update Stock', 'This is a test product', 'This is a test product', 0);
        $this->assertEquals(0, $this->model->getStockQty($product));
        
        $this->model->updateStock($product, 100);
        $this->assertEquals(100, $this->model->getStockQty($product));

        $product->delete();
    }
    
    public function testStockChangedFrom100to50IsTrue() {
        $sku = 'TEST101-' . uniqid();

        $product = $this->model->createProduct($sku, 30, 2, 'Test 101 Stock Changed Test', 'This is a test product', 'This is a test product', 100);
        $this->assertTrue($this->model->stockChanged($product, 50));
        
        $product->delete();
    }

}
