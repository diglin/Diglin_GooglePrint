<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin
 * @copyright   Copyright (c) Diglin (http://www.diglin.com)
 */

/**
 * Class Diglin_GooglePrint_Adminhtml_GooglePrint_ApiController
 */
class Diglin_GooglePrint_Adminhtml_Googleprint_ApiController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = ['oauth'];

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/diglin_googleprint');
    }

    public function authorizeAction()
    {
        $client = Mage::helper('diglin_googleprint')->getClient();
        $url = $client->createAuthUrl();

        $this->getResponse()->setHeader('Location', filter_var($url, FILTER_SANITIZE_URL));
    }

    public function oauthAction()
    {
        $code = $this->getRequest()->getParam('code');

        if (!empty($code)) {

            /* @var $client \Google_Client */
            $client = Mage::helper('diglin_googleprint')->getClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (is_array($token) && isset($token['access_token']) && isset($token['refresh_token'])) {
                Mage::getConfig()->saveConfig(\Diglin_GooglePrint_Helper_Data::CFG_TOKEN, json_encode($token));
                Mage::getConfig()->cleanCache();
            }

            $this->_getSession()->addSuccess($this->__('Google Authorization Process was successful.'));
        } else {
            $this->_getSession()->addError($this->__('Google Authorization Process has failed.'));
        }

        $this->_redirectUrl($this->getUrl('*/system_config/edit', array('section' => 'diglin_googleprint')));
    }

    public function revokeAction()
    {
        $client = Mage::helper('diglin_googleprint')->getClient();

        $result = $client->revokeToken();

        Mage::getConfig()->saveConfig(\Diglin_GooglePrint_Helper_Data::CFG_TOKEN, '');
        Mage::app()->cleanCache();

        if ($result) {
            $this->_getSession()->addSuccess($this->__('Token revocation has succeed'));
        } else {
            $this->_getSession()->addError($this->__('Token revocation has failed.'));
        }

//        $this->_redirect('*/system_config/edit', array('section' => 'diglin_googleprint'));
        $this->_redirectReferer();
    }
}