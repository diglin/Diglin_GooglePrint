<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_GooglePrint
 * @copyright   Copyright (c) Diglin (http://www.diglin.com)
 */

/**
 * Class Diglin_GooglePrint_Helper_Data
 */
class Diglin_GooglePrint_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CFG_CLIENT_ID     = 'diglin_googleprint/general/client_id';
    const CFG_CLIENT_SECRET = 'diglin_googleprint/general/client_secret';
    const CFG_CREDENTIALS   = 'diglin_googleprint/general/credentials_json';
    const CFG_PRINTERID     = 'diglin_googleprint/general/printer_id';
    const CFG_TOKEN         = 'diglin_googleprint/general/oauth_token';
    const CFG_ACTIVE        = 'diglin_googleprint/general/active';

    const SCOPE   = 'https://www.googleapis.com/auth/cloudprint';
    const GCP_URL = 'https://www.google.com/cloudprint/interface';

    const LOG = 'googleprint.log';

    /**
     * @var bool
     */
    protected $accessToken = false;

    /**
     * @var bool
     */
    protected $accessType = false;

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::CFG_ACTIVE, $storeId);
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        $credentials = [
            'client_id'     => Mage::getStoreConfig(self::CFG_CLIENT_ID),
            'client_secret' => Mage::getStoreConfig(self::CFG_CLIENT_SECRET),
            'redirect_uri'  => Mage::getModel('adminhtml/url')->getUrl('adminhtml/googleprint_api/oauth/'),
        ];

        return $credentials;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPrinterId($storeId = null)
    {
        return Mage::getStoreConfig(self::CFG_PRINTERID, $storeId);
    }

    /**
     * @return string|null
     */
    public function getOAuthToken()
    {
        return Mage::getStoreConfig(self::CFG_TOKEN);
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return !empty($this->getOAuthToken()) && !empty($this->getCredentials() && $this->isEnabled());
    }

    public function isTokenExpired()
    {
        return $this->getClient()->isAccessTokenExpired();
    }

    /**
     * Get client object
     *
     * @param array $config
     *
     * @return bool|\Google_Client
     */
    public function getClient($config = array())
    {
        $credentials = $this->getCredentials();
        $tokens = $this->getOAuthToken();

        $client = new Google_Client($config);

        if (!empty($credentials)) {
            $client->setAuthConfig($credentials);
        }

        $client->setApplicationName("Google_Print");
        $client->addScope(self::SCOPE);
        $client->setRedirectUri($this->_getUrl('*/googleprint_api/oauth/', true));
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true); // incremental auth

        if (!empty($tokens) && !$client->getAccessToken()) {
            $client->setAccessToken(json_decode($tokens, true));
        }

        $accessToken = $client->getAccessToken();

        if ($client->isAccessTokenExpired() && $accessToken) {
            try {
                $accessToken = $client->fetchAccessTokenWithRefreshToken();
                Mage::getConfig()->saveConfig(self::CFG_TOKEN, json_encode($accessToken));
            } catch (LogicException $e) {
                Mage::logException($e);
            }
        }

        if ($accessToken) {
            $this->accessToken = $accessToken['access_token'];
            $this->accessType = $accessToken['token_type'];
        }

        return $client;
    }

    /**
     * @return bool
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return bool
     */
    public function getTokenType()
    {
        return $this->accessType;
    }

    /**
     * @param array $params
     * @param string $endpoint
     *
     * @return bool|mixed
     * @throws \Diglin_GooglePrint_AccessTokenException
     * @throws \Diglin_GooglePrint_RequestException
     */
    public function gcpRequest($params = [], $endpoint = 'submit')
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // init Google_Client and set AccessToken
        $this->getClient();

        if ($this->getClient()->isAccessTokenExpired()) {
            throw new Diglin_GooglePrint_AccessTokenException();
        }

        $params = (is_array($params)) ? http_build_query($params) : $params;

        $headers = [
            'Authorization: ' . $this->getTokenType() . ' ' . $this->getAccessToken(),
            'Content-Length: ' . strlen($params),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::GCP_URL . '/' . trim($endpoint, " \t\n\r\0\x0B/"));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $head = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $gcpResult = json_decode($result, true);

        if ($head != 200) {
            throw new Diglin_GooglePrint_RequestException();
        }

        return $gcpResult;
    }

    /**
     * @param $document
     * @param string $type
     * @param string $title
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function submitPrinterJob($document, $type = 'application/pdf', $title = 'Print Job', $storeId = null)
    {
        $storeId = $storeId ? $storeId : Mage::app()->getStore()->getId();

        $client = $this->getClient();

        if (!$client) {
            return false;
        }

        $params = [
            'printerid'   => $this->getPrinterId($storeId),
            'title'       => $title,
            'ticket'      => json_encode($this->getTicket()),
            'content'     => $document,
            'contentType' => $type,
        ];

        if ($type == 'application/pdf') {
            $params['contentTransferEncoding'] = 'base64';
            $params['content'] = base64_encode($params['content']);
        }

        $result = $this->gcpRequest($params, 'submit');

        if (!$result['success']) {
            Mage::log($this->__('Error by submitting the document %s: %s - %s', $title, $result['errorCode'], $result['message']), Zend_Log::NOTICE, self::LOG);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getTicket()
    {
        return [
            'version' => '1.0',
            'print' => [
                'vendor_ticket_item' => [],
                'color' => ['type' => 'STANDARD_MONOCHROME'],
                'copies' => ['copies' => '1'],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getPrinterList()
    {
        try {
            $printersResult = $this->gcpRequest([], 'search');
        } catch (Exception $e) {
            return [];
        }

        if ($printersResult) {
            return $printersResult['printers'];
        }

        return [];
    }
}
