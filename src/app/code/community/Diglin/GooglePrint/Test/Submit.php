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

$pdfContent = file_get_contents(dirname(__FILE__) . '/file/PackingSlip.pdf');

//$result = Mage::helper('diglin_googleprint')->submitPrinterJob('My Text is ok', 'text/plain', 'PackingSlip Plain Text');
$result = Mage::helper('diglin_googleprint')->submitPrinterJob(($pdfContent), 'application/pdf', 'PackingSlip PDF');

print_r($result);
