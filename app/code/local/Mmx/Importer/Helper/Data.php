<?php

class Mmx_Importer_Helper_Data extends Mage_Core_Helper_Abstract {
    
    public static function moveFile($file, $destination_dir) {
        $pathParts = pathinfo($file);
        copy($file, $destination_dir . DIRECTORY_SEPARATOR . $pathParts['basename']);
        unlink($file);
    }    
    
}
