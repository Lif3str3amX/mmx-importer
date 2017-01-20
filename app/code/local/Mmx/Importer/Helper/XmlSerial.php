<?php

class Mmx_Importer_Helper_XmlSerial extends Mmx_Importer_Helper_Xml {

    /**
     *
     * @var array
     */
    protected $lookup;

    /**
     * 
     * @return array
     */
    public function getSkus() {

        if (empty($this->lookup)) {
            $this->createLookup();
        }

        $skus = array();

        if ($nodes = $this->getNodes()) {
            foreach ($this->lookup as $key => $value) {
                $skus[$key] = $key;
            }
        }

        return $skus;
    }

    /**
     *
     * @param string $sku
     * @return array
     */
    public function getSerialsBySku($sku) {

        if (empty($this->lookup)) {
            $this->createLookup();
        }

        if (isset($this->lookup[$sku])) {
            return $this->lookup[$sku];
        }
        else {
            return array();
        }

    }

    public function createLookup() {
        if ($nodes = $this->getNodes()) {
            foreach ($nodes as $node) {
                $textbox6 = trim((string) $node->attributes()->textbox6);   // textbox6 is sku
                $this->lookup[$textbox6][] = trim((string) $node->attributes()->textbox17); // textbox17 is serial
            }
        }

        return;
    }

}
