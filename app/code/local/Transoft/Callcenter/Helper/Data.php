<?php

/**
 * Callcenter default helper
 */
class Transoft_Callcenter_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * convert array to options
     *
     * @access public
     * @param array $options
     * @return array
     */
    public function convertOptions($options)
    {
        $converted = array();
        foreach ($options as $option) {
            if (!is_array($option['value']) && !is_array($option['label'])) {
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }

}

