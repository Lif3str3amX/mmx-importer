<?php

class Mmx_Importer_Model_OrderStatusImporter {

    /**
     *
     * @var Mmx_Importer_Helper_Xml
     */
    protected $helper;
    
    /**
     * 
     * @return Mmx_Importer_Helper_Xml
     */
    public function getHelper() {
        return $this->helper;
    }

    /**
     * 
     * @param Mmx_Importer_Helper_Xml $helper
     * @return $this
     */
    public function setHelper(Mmx_Importer_Helper_Xml $helper) {
        $this->helper = $helper;
        return $this;
    }
    
    /**
     * 
     */
    public function update() {

        if ($nodes = $this->helper->getNodes()) {
            foreach ($nodes as $node) {

                $increment_id = trim((string) $node->attributes()->order_no);
                $status = $this->sageToMagentoStatus(trim((string) $node->attributes()->status));

                /* @var $order Mage_Sales_Model_Order */
                $order = Mage::getModel('sales/order')->loadByAttribute('increment_id', $increment_id);
                if (!is_object($order) || !$order->getId()) {
                    $this->log("Order {$increment_id} not found");
                }
                else {
                    // Only change if different and not already canceled
                    if ($order->getStatus() != $status && $order->getStatus() != 'canceled') {
                        
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
