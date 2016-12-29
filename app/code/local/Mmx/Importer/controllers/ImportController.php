<?php

class Mmx_Importer_ImportController extends Mage_Core_Controller_Front_Action {

    const BT_WEBSITE_ID = 2;
    const INDIGO_WEBSITE_ID = 3;

    public function indexAction() {

        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $starttime = $mtime[1] + $mtime[0];

        // Checks
        $store_id = Mage::app()->getStore()->getStoreId();
        if ($store_id != 1) {
            throw new Exception('This importer is only designed for use in the main website/store');
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
            $this->log('Found BT stock file: ' . $bt_stock_xml_filename);
            $this->processStock($bt_stock_xml_filename, self::BT_WEBSITE_ID, $bt_category_id);
            Mmx_Importer_Helper_Data::moveFile($bt_stock_xml_filename, $processed_dir);
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
            $this->log('Found Indigo stock file: ' . $indigo_stock_xml_filename);
            $this->processStock($indigo_stock_xml_filename, self::INDIGO_WEBSITE_ID, $indigo_category_id);
            Mmx_Importer_Helper_Data::moveFile($indigo_stock_xml_filename, $processed_dir);
        }

        if (is_file($indigo_serial_xml_filename)) {
            $this->log('Found Indigo serial file: ' . $indigo_serial_xml_filename);
            $this->processSerials($indigo_serial_xml_filename);
            Mmx_Importer_Helper_Data::moveFile($indigo_serial_xml_filename, $processed_dir);
        }

        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $endtime = $mtime[1] + $mtime[0];
        $totaltime = $endtime - $starttime;
        echo "Run complete in " . $totaltime . " seconds";
        exit;

    }

    public function processStock($stock_xml_filename, $website_id, $category_id) {

        $helper = new Mmx_Importer_Helper_StockImporter();
        $helper->setXmlFilename($stock_xml_filename)
                ->setXpath('/Report/table1/Detail_Collection/Detail')
                ->setWebsiteId($website_id)
                ->setCategoryId($category_id)
                ->update();
    }

    public function processSerials($serial_xml_filename) {

        $helper = new Mmx_Importer_Helper_SerialImporter();
        $helper->setXmlFilename($serial_xml_filename)
                ->setXpath('/Report/table1/Detail_Collection/Detail')
                ->setSkus(array('INCIENABOM', 'INBTRESERVATION'))
                ->update();
    }

    public function log($message) {
        Mage::log($message, Zend_Log::INFO, 'mmx_importer.log', true);
    }

}
