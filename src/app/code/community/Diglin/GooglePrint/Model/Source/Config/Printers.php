<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin
 * @copyright   Copyright (c) Diglin (http://www.diglin.com)
 */

class Diglin_GooglePrint_Model_Source_Config_Printers
{
    public function getAllOptions()
    {
        $output = array();
        foreach ($this->toOptionArray() as $key => $value) {
            $output[] = array(
                'value' => $key,
                'label' => $value,
            );
        }

        return $output;
    }

    public function toOptionArray()
    {
        $result = [];
        $helper = Mage::helper('diglin_googleprint');

        if (!$helper->isConfigured()) {
            return $result;
        }

        $printers = $helper->getPrinterList();
        foreach ($printers as $printer) {
            $result[$printer['id']] = $printer['displayName'];
        }

        return $result;
    }
}