<?php

class Mmx_Importer_Helper_SerialImporter {

    protected $xml_filename;
    protected $xpath;
    protected $skus;

    public function getXmlFilename() {
        return $this->xml_filename;
    }

    public function setXmlFilename($xml_filename) {
        $this->xml_filename = $xml_filename;
        return $this;
    }
    
    public function getXpath() {
        return $this->xpath;
    }

    public function setXpath($xpath) {
        $this->xpath = $xpath;
        return $this;
    }

    public function getSkus() {
        return $this->skus;
    }

    public function setSkus($skus) {
        $this->skus = $skus;
        return $this;
    }
    
    public function getXml() {

        // Load filename contents
        $string = file_get_contents($this->xml_filename);

        // Last minute namespace workaround - xmlns namespaces not seen in the original test files
        $dom_xml = Mmx_Importer_Helper_Data::removeNameSpaces($string);

        // Process
        return simplexml_load_string($dom_xml);
    }

    /**
     * 
     * @param string $sku
     * @return array
     */
    public function getSerialsBySku($sku) {

        $serials = array();
        
        $xml = $this->getXml();
        $nodes = $xml->xpath($this->xpath);
        if ($nodes) {
            foreach ($nodes as $node) {
                
                $textbox6 = trim((string) $node->attributes()->textbox6);
                if ($textbox6 == $sku) {
                    $serials[] = trim((string) $node->attributes()->textbox17);
                }
            }
        }

        return $serials;
    }

    /**
     * 
     */
    public function update() {
        
        foreach ($this->skus as $sku) {
            
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if (!is_object($product) || !$product->getId()) {
                $this->log("Product with SKU {$sku} not found");
            }
            else {

                $this->log("Found product with SKU {$sku}");

                // http://blog.adin.pro/2015-06-05/magento-product-options-create-and-delete/
                $product->load($product->getId());
                
                $serials = $this->getSerialsBySku($sku);
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
