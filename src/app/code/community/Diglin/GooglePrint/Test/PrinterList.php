<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

define('MAGENTO_ROOT', dirname(__FILE__) . '/../../../../../Mage.php');

require_once MAGENTO_ROOT;

Mage::app();

$printers = Mage::helper('diglin_googleprint')->getPrinterList();

print_r($printers);

