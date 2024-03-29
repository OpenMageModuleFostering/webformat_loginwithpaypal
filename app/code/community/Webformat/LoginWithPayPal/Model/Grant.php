<?php
/**
 * WEBFORMAT s.r.l.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Webformat
 * @package     LoginWithPayPal
 * @copyright   Copyright (c) 2014 WEBFORMAT s.r.l. (http://www.webformat.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>
<?php

/** Grant token from authorization code. */
class Webformat_LoginWithPayPal_Model_Grant extends Webformat_LoginWithPayPal_Model_AbstractRequest {
    /**
     * Grant token from authorization code.
     */
    public function grant() {
        $username = Mage::helper('webformat_loginwithpaypal')->getClientId();
        $password = Mage::helper('webformat_loginwithpaypal')->getSecret();
        $post = array('grant_type' => 'authorization_code',
            'code' => $this->getCode(),
            'redirect_uri' => Mage::getUrl('webformat_loginwithpaypal'));

        $ch = curl_init($this->getServiceBaseUrl('/v1/identity/openidconnect/tokenservice'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (($output = curl_exec($ch)) === FALSE) {
            throw new Zend_Exception("Could not grant authorization code");
        }
        curl_close($ch);
        $this->setResponse(json_decode($output));
        $this->validateResponse();
        Mage::getSingleton('customer/session')
            ->setPayPalAccessToken($this->getResponse()->access_token);

        return true;
    }


    /**
     * Validate response.
     * @throws Zend_Exception
     */
    protected function validateResponse() {
        if (!$this->getResponse()) {
            throw new Zend_Exception("Could not obtain PayPal Grant Access Token");
        }
        if (isset($this->getResponse()->error)) {
            throw new Zend_Exception($this->getResponse()->error_description);
        }
        if (!isset($this->getResponse()->access_token)) {
            throw new Zend_Exception("Could not obtain PayPal Grant Access Token");
        }
    }
}
