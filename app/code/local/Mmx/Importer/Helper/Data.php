<?php

class Mmx_Importer_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public static function removeNameSpaces($xml) {

        // http://stackoverflow.com/questions/15223224/how-to-remove-all-namespaces-from-xml-in-php-tags-and-attributes/18994815#18994815

        // Some hacks to remove warnings
        $xml = str_replace('xmlns="Web_x0020_Portal_x0020_Current_x0020_Stock"', '', $xml);
        $xml = str_replace('xmlns="Web_x0020_Portal_x0020_Free_x0020_Stock_x0020_-_x0020_Serialised"', '', $xml);
        $xml = str_replace('xmlns="Web_x0020_Portal_x0020_Indigo_x0020_Order_x0020_Status"', '', $xml);

        $sxe = new SimpleXMLElement($xml);
        $dom_sxe = dom_import_simplexml($sxe);

        $dom = new DOMDocument('1.0');
        $dom_sxe = $dom->importNode($dom_sxe, true);
        $dom_sxe = $dom->appendChild($dom_sxe);

        $element = $dom->childNodes->item(0);

        // See what the XML looks like before the transformation
        //echo "<pre>\n" . htmlspecialchars($dom->saveXML()) . "\n</pre>";
        foreach ($sxe->getDocNamespaces() as $name => $uri) {
            $element->removeAttributeNS($uri, $name);
        }
        // See what the XML looks like after the transformation
        //echo "<pre>\n" . htmlspecialchars($dom->saveXML()) . "\n</pre>";

        return $dom->saveXML();
    }
    
    public static function moveFile($file, $destination_dir) {
        $pathParts = pathinfo($file);
        copy($file, $destination_dir . DIRECTORY_SEPARATOR . $pathParts['basename']);
        unlink($file);
    }    
    
}
