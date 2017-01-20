<?php

class Mmx_Importer_Model_SerialImporter {

    /**
     *
     * @var Mmx_Importer_Helper_SerialXml
     */
    protected $helper;
    
    /**
     * 
     * @return Mmx_Importer_Helper_SerialXml
     */
    public function getHelper() {
        return $this->helper;
    }

    /**
     * 
     * @param Mmx_Importer_Helper_SerialXml $helper
     * @return $this
     */
    public function setHelper(Mmx_Importer_Helper_SerialXml $helper) {
        $this->helper = $helper;
        return $this;
    }

    /**
     * 
     */
    public function update() {
        
        foreach ($this->helper->getSkus() as $sku) {
            
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if (!is_object($product) || !$product->getId()) {
                $this->log("Product with SKU {$sku} not found");
            }
            else {

                $this->log("Found product with SKU {$sku}");

                // http://blog.adin.pro/2015-06-05/magento-product-options-create-and-delete/
                $product->load($product->getId());
                
                $serials = $this->helper->getSerialsBySku($sku);
                if ($serials) {
                    
                    $options = $product->getOptions();
                    if ($options) {

                        /* @var $option Mage_Catalog_Model_Product_Option */
                        foreach ($options as $option) {
                            if ($option->getDefaultTitle() == 'Serial Code') {

                                $this->log("Found existing 'Serial Code' custom option for SKU {$sku}");

                                if ($this->valuesChanged($option, $serials)) {

                                    $this->log("Serial numbers HAVE changed for SKU {$sku}, updating");
                                    $updatedOption = $this->createOption($serials);

                                    $option->delete();
                                    $product->save();

                                    $this->saveOption($product, $updatedOption);

                                }
                                else {
                                    $this->log("Serial numbers HAVE NOT changed for SKU {$sku}, do nothing");
                                }

                                unset($product);

                            }
                            else {

                                $this->log("'Serial Code' custom option does not exist, creating it and adding all serials now");
                                $newOption = $this->createOption($serials);

                                $this->saveOption($product, $newOption);                    
                                unset($product);
                            }

                        }
                    }
                    else {

                        $this->log("No options found at all, creating first custom option 'Serial Code' and adding all serials now");
                        $newOption = $this->createOption($serials);

                        $this->saveOption($product, $newOption);                    
                        unset($product);
                    }
                    
                }
                else {
                    // TODO: Remove all serials from this product?
                    $this->log("No serials found for SKU {$sku}. What to do?");
                }

            }
            
            $this->log("Finished processing SKU {$sku}");
            
        }
        
    }
    
    /**
     * @param array $newValues Array of serial numbers
     * @return Mage_Catalog_Model_Product_Option[]
     */
    public function createOption($newValues) {
        
        foreach ($newValues as $newValue) {
            $values[] = array(
                'option_type_id' => -1,
                'title' => $newValue,
                'price' => 0,
                'price_type' => 'fixed',
                'sku' => '',
                'sort_order' => 0,
                'is_delete' => 0,
            );
        }

        $option = array(
            'title' => 'Serial Code',
            'type' => 'checkbox',
            'is_require' => 1,
            'values' => $values
        );

        return $option;
    }
    
    /**
     * http://magentolalit.blogspot.co.uk/2015/09/create-and-update-custom-option-in.html
     * 
     * @param type $product Mage_Catalog_Model_Product
     * @param type $option Mage_Catalog_Model_Product_Option
     */
    public function saveOption(Mage_Catalog_Model_Product $product, Mage_Catalog_Model_Product_Option $option) {

        $optionInstance = $product->getOptionInstance()->unsetOptions();
        $product->setHasOptions(1);
        $product->setRequiredOptions(1);
        $optionInstance->addOption($option);
        
        return $product->save();
    }
    
    /**
     * 
     * @param Mage_Catalog_Model_Product_Option $option
     * @param array $newValues Array of serial numbers
     * @return boolean
     */
    public function valuesChanged(Mage_Catalog_Model_Product_Option $option, $newValues) {
        
        $existingValues = $option->getValues();
        foreach ($existingValues as $existingValue) {
            $oldValues[] = $existingValue->getTitle();
        }

        sort($oldValues);
        sort($newValues);

        // Compare the arrays
        if ($oldValues != $newValues) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * 
     * @param string $message
     */
    public function log($message) {
        Mage::log($message, Zend_Log::INFO, 'mmx_importer.log', true);
    }
    
}
