<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin
 * @copyright   Copyright (c) Diglin (http://www.diglin.com)
 */

class Diglin_GooglePrint_Adminhtml_GooglePrint_TestController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = ['oauth', 'test'];

    protected function _isAllowed()
    {
        return Mage::getIsDeveloperMode() && parent::_isAllowed();
    }

    public function authorizeAction()
    {
        $helper = Mage::helper('diglin_googleprint');

        $client = $helper->getClient(['approval_prompt' => 'force']);
        $client->setRedirectUri($this->getUrl('*/googleprint_test/oauth/', ['_nosecret' => true]));

        $auth_url = $client->createAuthUrl();
        $this->getResponse()->setHeader('Location', filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    public function oauthAction()
    {
        $helper = Mage::helper('diglin_googleprint');

        $code = $this->getRequest()->getParam('code');

        if ($code) {
            $client = $helper->getClient();
            $client->setRedirectUri($this->getUrl('*/googleprint_test/oauth/', ['_nosecret' => true]));

            $token = $client->fetchAccessTokenWithAuthCode($code);

            print_r($this->getRequest()->getParams());

            if (is_array($token)) {
                echo json_encode($token) . PHP_EOL;
            }
        }
    }

    public function testAction()
    {
        echo 'Great !';
    }
}