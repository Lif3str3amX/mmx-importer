<?php

class Mmx_Importer_ImportController extends Mage_Core_Controller_Front_Action {

    const BT_WEBSITE_ID = 2;
    const INDIGO_WEBSITE_ID = 3;
    const NOKIA_WEBSITE_ID = 4;

    public function indexAction() {

        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $starttime = $mtime[1] + $mtime[0];

        // Checks
        $store_id = Mage::app()->getStore()->getStoreId();
        if ($store_id != 1) {
            throw new Exception('This importer is only designed for use in the main website/store');
        }

        $lastOrder = $this->getLastOrder();
        if (!$lastOrder) {
            throw new Exception('Could not get last order');
        }

        // Workaround for save problem?
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $processed_dir = Mage::getStoreConfig('mmx_importer/general/processed_dir');
        if (!$processed_dir || !is_dir($processed_dir)) {
            throw new Exception('Processed files directory not configured or not found');
        }

        // BT
        $bt_stock_xml_filename = Mage::getStoreConfig('mmx_importer/bt/stock_xml_filename');
        if (!$bt_stock_xml_filename) {
            throw new Exception('Stock XML file location has not been configured for BT');
        }

        $bt_category_id = Mage::getStoreConfig('mmx_importer/bt/category_id');
        if (!$bt_category_id) {
            throw new Exception('Destination Category ID has not been configured for BT');
        }

        if (is_file($bt_stock_xml_filename)) {
            if ($this->fileIsNewerThanLastOrder($bt_stock_xml_filename, $lastOrder)) {
                $this->log('Found BT stock file: ' . $bt_stock_xml_filename);
                $this->processStock($bt_stock_xml_filename, self::BT_WEBSITE_ID, $bt_category_id);
                Mmx_Importer_Helper_Data::moveFile($bt_stock_xml_filename, $processed_dir);
            }
            else {
                $this->log('BT stock file is not 15 mins newer than last order - skipping');                
            }
        }

        // Nokia
        $nokia_stock_xml_filename = Mage::getStoreConfig('mmx_importer/nokia/stock_xml_filename');
        if (!$nokia_stock_xml_filename) {
            throw new Exception('Stock XML file location has not been configured for Nokia');
        }

        $nokia_serial_xml_filename = Mage::getStoreConfig('mmx_importer/nokia/serial_xml_filename');
        if (!$nokia_serial_xml_filename) {
            throw new Exception('Serial XML file location has not been configured for Nokia');
        }
        
        $nokia_category_id = Mage::getStoreConfig('mmx_importer/nokia/category_id');
        if (!$nokia_category_id) {
            throw new Exception('Destination Category ID has not been configured for Nokia');
        }

        if (is_file($nokia_stock_xml_filename)) {
            if ($this->fileIsNewerThanLastOrder($nokia_stock_xml_filename, $lastOrder)) {
                $this->log('Found Nokia stock file: ' . $nokia_stock_xml_filename);
                $this->processStock($nokia_stock_xml_filename, self::NOKIA_WEBSITE_ID, $nokia_category_id);
                Mmx_Importer_Helper_Data::moveFile($nokia_stock_xml_filename, $processed_dir);
            }
            else {
                $this->log('Nokia stock file is not 15 mins newer than last order - skipping');
            }
        }        

        if (is_file($nokia_serial_xml_filename)) {
            if ($this->fileIsNewerThanLastOrder($nokia_serial_xml_filename, $lastOrder)) {
                $this->log('Found Nokia serial file: ' . $nokia_serial_xml_filename);
                $this->processSerials($nokia_serial_xml_filename);
                Mmx_Importer_Helper_Data::moveFile($nokia_serial_xml_filename, $processed_dir);
            }
            else {
                $this->log('Nokia serial file is not 15 mins newer than last order - skipping');
            }
        }
        
        // Indigo
        $indigo_stock_xml_filename = Mage::getStoreConfig('mmx_importer/indigo/stock_xml_filename');
        if (!$indigo_stock_xml_filename) {
            throw new Exception('Stock XML file location has not been configured for Indigo');
        }

        $indigo_serial_xml_filename = Mage::getStoreConfig('mmx_importer/indigo/serial_xml_filename');
        if (!$indigo_serial_xml_filename) {
            throw new Exception('Serial XML file location has not been configured for Indigo');
        }

        $indigo_category_id = Mage::getStoreConfig('mmx_importer/indigo/category_id');
        if (!$indigo_category_id) {
            throw new Exception('Destination Category ID has not been configured for Indigo');
        }

        if (is_file($indigo_stock_xml_filename)) {
            if ($this->fileIsNewerThanLastOrder($indigo_stock_xml_filename, $lastOrder)) {
                $this->log('Found Indigo stock file: ' . $indigo_stock_xml_filename);
                $this->processStock($indigo_stock_xml_filename, self::INDIGO_WEBSITE_ID, $indigo_category_id);
                Mmx_Importer_Helper_Data::moveFile($indigo_stock_xml_filename, $processed_dir);
            }
            else {
                $this->log('Indigo stock file is not 15 mins newer than last order - skipping');
            }
        }

        if (is_file($indigo_serial_xml_filename)) {
            if ($this->fileIsNewerThanLastOrder($indigo_serial_xml_filename, $lastOrder)) {            
                $this->log('Found Indigo serial file: ' . $indigo_serial_xml_filename);
                $this->processSerials($indigo_serial_xml_filename);
                Mmx_Importer_Helper_Data::moveFile($indigo_serial_xml_filename, $processed_dir);
            }
            else {
                $this->log('Indigo serial file is not 15 mins newer than last order - skipping');
            }
        }

        // Order Status
        $order_status_xml_filename = Mage::getStoreConfig('mmx_importer/general/order_status_xml_filename');
        if (is_file($order_status_xml_filename)) {
            $this->log('Found Order Status file: ' . $order_status_xml_filename);
            $this->processOrderStatus($order_status_xml_filename);
            Mmx_Importer_Helper_Data::moveFile($order_status_xml_filename, $processed_dir);
        }
        
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $endtime = $mtime[1] + $mtime[0];
        $totaltime = $endtime - $starttime;
        echo "Run complete in " . $totaltime . " seconds";
        exit;

    }

    public function processStock($stock_xml_filename, $website_id, $category_id) {

        $helper = new Mmx_Importer_Helper_Xml();
        $helper->setXmlFilename($stock_xml_filename)
                ->setXpath('/Report/table1/Detail_Collection/Detail');
        
        $model = new Mmx_Importer_Model_StockImporter();
        $model->setHelper($helper)
                ->setWebsiteId($website_id)
                ->setCategoryId($category_id)
                ->update();
    }

    public function processSerials($serial_xml_filename) {

        $helper = new Mmx_Importer_Helper_XmlSerial();
        $helper->setXmlFilename($serial_xml_filename)
                ->setXpath('/Report/table1/Detail_Collection/Detail');

        $model = new Mmx_Importer_Model_SerialImporter();
        $model->setHelper($helper)
                ->update();
    }

    public function processOrderStatus($order_status_xml_filename) {

        $helper = new Mmx_Importer_Helper_Xml();
        $helper->setXmlFilename($order_status_xml_filename)
                ->setXpath('/Report/table1/Detail_Collection/Detail');
        
        $model = new Mmx_Importer_Model_OrderStatusImporter();
        $model->setHelper($helper)
                ->update();
    }
    
    public function log($message) {
        Mage::log($message, Zend_Log::INFO, 'mmx_importer.log', true);
    }
    
    /**
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getLastOrder() {
        $orders = Mage::getModel('sales/order')->getCollection()
             ->setOrder('created_at','DESC')
             ->setPageSize(1)
             ->setCurPage(1);

        $order = $orders->getFirstItem();
        
        return $order;
    }
    
    /**
     * Checks if file time (+15 minutes) is greater than last order date
     * 
     * @param string $file
     * @param Mage_Sales_Model_Order $order
     */
    public function fileIsNewerThanLastOrder($file, $order) {
        
        $tolerance = 60 * 15;   // +15 mins req by JP
        $modificationTime = filemtime($file);
        $orderTime = strtotime($order->getCreatedAt());

        if (($modificationTime + $tolerance) > $orderTime) {
            return true;
        }
        else {
            return false;
        }
    }

}
