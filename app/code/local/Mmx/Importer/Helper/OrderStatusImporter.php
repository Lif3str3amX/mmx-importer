<?php

class Mmx_Importer_Helper_OrderStatusImporter {

    protected $xml_filename;
    protected $xpath;

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
     */
    public function update() {

        $xml = $this->getXml();
        $nodes = $xml->xpath($this->xpath);
        if ($nodes) {
            foreach ($nodes as $node) {

                $increment_id = trim((string) $node->attributes()->order_no);
                $status = $this->sageToMagentoStatus(trim((string) $node->attributes()->status));

                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->loadByAttribute('increment_id', $increment_id);
                if (!is_object($order) || !$order->getId()) {
                    $this->log("Order {$increment_id} not found");
                }
                else {
                    // Only change if different
                    if ($order->getStatus() != $status) {
                        
                        $this->log(sprintf("Updating status for %s, %s to %s", $order->getIncrementId(), $order->getStatus(), $status));

                        $comment = "Status update: {$status}";
                        $historyItem = $order->addStatusHistoryComment($comment, $status);
                        $historyItem->setIsCustomerNotified(1)->save();

                        $order->save();
                        $order->sendOrderUpdateEmail($notify = true, $comment);
                    }
                }
            }
        }
        
    }

    /**
     * 
     * @param string $status
     * @return type string
     */
    public function sageToMagentoStatus($status) {

        switch ($status) {
            case 1: // Forward order
            case 2: // Credit stopped order
            case 3: // Credit stopped/back order
            case 4: // Back order
            case 5: // Awaiting despatch
                return 'processing';

            case 6: // Despatch note printed
                return 'picking';
            
            case 7: // Despatched order
            case 8: // Invoiced order
                return 'complete';
            
            case 9: // Deleted order
                return 'canceled';
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
