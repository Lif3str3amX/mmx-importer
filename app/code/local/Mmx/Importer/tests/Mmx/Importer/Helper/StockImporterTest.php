<?php

class Mmx_Importer_Helper_StockImporterTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Mmx_Importer_Helper_StockImporter
     */
    protected $helper;

    public function setUp() {

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $this->helper = new Mmx_Importer_Helper_StockImporter();
        $this->helper->setXmlFilename('/Users/gn/Sites/magento/magento-mmx/SageData/In/BTStock.xml')
                ->setXpath('/Report/table1/Detail_Collection/Detail')
                ->setWebsiteId(2)
                ->setCategoryId(30);
    }
    
    public function testGetXmlFilename() {
        $file = '/Users/gn/Sites/magento/magento-mmx/SageData/In/BTStock.xml';
        $this->assertEquals($file, $this->helper->getXmlFilename());
    }
    
    public function testGetXpath() {
        $this->assertEquals('/Report/table1/Detail_Collection/Detail', $this->helper->getXpath());
    }

    public function testGetWebsiteId() {
        $this->assertEquals(2, $this->helper->getWebsiteId());
    }

    public function testGetCategoryId() {
        $this->assertEquals(30, $this->helper->getCategoryId());
    }
    
    public function testCreateInStockProduct() {
        $sku = 'TEST101-' . uniqid();
        
        $product = $this->helper->createProduct($sku, 30, 2, 'Test 101 In Stock', 'This is a test IN STOCK product', 'This is a test IN STOCK product', 10);
        $this->assertEquals($sku, $product->getSku());
        $this->assertEquals(10, $this->helper->getStockQty($product));
        
        $product->delete();
    }

    public function testCreateOutOfStockProduct() {
        $sku = 'TEST101-' . uniqid();
        
        $product = $this->helper->createProduct($sku, 30, 2, 'Test 101 Out Of Stock', 'This is a test OUT OF STOCK product', 'This is a test OUT OF STOCK product', 0);
        $this->assertEquals($sku, $product->getSku());
        $this->assertEquals(0, $this->helper->getStockQty($product));

        $product->delete();
    }
    
    public function testUpdateStock() {
        $sku = 'TEST101-' . uniqid();

        $product = $this->helper->createProduct($sku, 30, 2, 'Test 101 Update Stock', 'This is a test product', 'This is a test product', 0);
        $this->assertEquals(0, $this->helper->getStockQty($product));
        
        $this->helper->updateStock($product, 100);
        $this->assertEquals(100, $this->helper->getStockQty($product));

        $product->delete();
    }
    
    public function testStockChangedFrom100to50IsTrue() {
        $sku = 'TEST101-' . uniqid();

        $product = $this->helper->createProduct($sku, 30, 2, 'Test 101 Stock Changed Test', 'This is a test product', 'This is a test product', 100);
        $this->assertTrue($this->helper->stockChanged($product, 50));
        
        $product->delete();
    }
    
    public function testUpdate() {
        $this->helper->update();
    }

}
