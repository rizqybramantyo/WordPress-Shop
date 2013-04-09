<?php
class paypalpro extends paymentModule {
    protected $_ccTypes = array('VISA' => 'Visa',
                              'VISA_DEBIT' => 'Visa Debit',
                              'VISA_ELECTRON' => 'Visa Electron',
                              'MASTERCARD' => 'MasterCard',
                              'DISCOVER' => 'Discover Card',
                              'AMEX' => 'American Express',
                              'SWITCH' => 'Maestro',
                              'SOLO' => 'Solo'
    );
    protected $_inputFields = array(
        'cc_owner' => array('type' => 'text', 'label' => 'Card Owner', 'validate' => 'notNull'),
        'cc_type' => array('type' => 'selectbox', 'label' => 'Card Type', 'validate' => 'notNull'),
        'cc_number_nh-dns' => array('type' => 'text', 'label' => 'Card Number', 'validate' => 'notNull'),
        'cc_starts_month' => array('type' => 'selectbox', 'label' => 'Card Valid From Date: Month', 'validate' => 'notNull'),
        'cc_starts_year' => array('type' => 'selectbox', 'label' => 'Card Valid From Date: Year', 'validate' => 'notNull'),
        'cc_expires_month' => array('type' => 'selectbox', 'label' => 'Card Expiry Date: Month', 'validate' => 'notNull'),
        'cc_expires_year' => array('type' => 'selectbox', 'label' => 'Card Expiry Date: Year', 'validate' => 'notNull'),
        'cc_cvc_nh-dns' => array('type' => 'text', 'label' => 'Card Security Code (CVV2)'),
        'cc_issue_nh-dns' => array('type' => 'text', 'label' => 'Card Issue Number')
    );
    public function __construct($d, $params = array()) {
        parent::__construct($d, $params);
        $this->_haveProcessHtml = true;
    }
    public function getProcessHtml() {
        return $this->getView()->getForm();
    }
    public function isCardAccepted($card) {
      return isset($this->_ccTypes[$card]);
    }
    public function getCcTypes() {
        return $this->_ccTypes;
    }
    public function validateInput($d = array()) {
        $res = new response();
        foreach($this->_inputFields as $key => $attrs) {
            if(!isset($attrs['validate'])) continue;
            switch($attrs['validate']) {
                case 'notNull':
                    if(empty($d[$key])) {
                        $res->addError(array(
                            $key => lang::_($attrs['label']
                        )));
                    }
                    break;
            }
        }
        return $res;
    }
    public function processPayment($d = array()) {
        $res = new response();
        $order = frame::_()->getModule('order')->getCurrent();
        
        $this->_params = $this->_params[0];
        if(!isset($this->_params->transMethod)) {   //this must be added to module options
            $this->_params->transMethod = NULL;
        }
        if($this->_params->testMode) {
            $api_url = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $api_url = 'https://api-3t.paypal.com/nvp';
        }
        $card_type = $d['cc_type'];
        if ( ($card_type == 'VISA_DEBIT') || ($card_type == 'VISA_ELECTRON') ) {
            $card_type = 'VISA';
        }

        $params = array('USER' => $this->_params->username,
                        'PWD' => $this->_params->password,
                        'VERSION' => '3.2',
                        'SIGNATURE' => $this->_params->signature,
                        'METHOD' => 'DoDirectPayment',
                        'PAYMENTACTION' => (($this->_params->transMethod == 'Sale') ? 'Sale' : 'Authorization'),
                        'IPADDRESS' => utils::getIP(),
                        'AMT' => $order['total'],
                        'CREDITCARDTYPE' => $card_type,
                        'ACCT' => $d['cc_number_nh-dns'],
                        'STARTDATE' => $d['cc_starts_month'] . $d['cc_starts_year'],
                        'EXPDATE' => $d['cc_expires_month'] . $d['cc_expires_year'],
                        'CVV2' => $d['cc_cvc_nh-dns'],
                        'FIRSTNAME' => substr($d['cc_owner'], 0, strpos($d['cc_owner'], ' ')),
                        'LASTNAME' => substr($d['cc_owner'], strpos($d['cc_owner'], ' ')+1),
                        /*'STREET' => $order->billing['street_address'],
                        'CITY' => $order->billing['city'],
                        'STATE' => tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], $order->billing['state']),
                        'COUNTRYCODE' => $order->billing['country']['iso_code_2'],
                        'ZIP' => $order->billing['postcode'],
                        'EMAIL' => $order->customer['email_address'],
                        'PHONENUM' => $order->customer['telephone'],*/
                        'CURRENCYCODE' => $order['currency']['code'],
                        'BUTTONSOURCE' => 'Ready_ecomerce');
        
        if ( (($d['cc_type'] == 'SWITCH') && $this->isCardAccepted('SWITCH')) || (($d['cc_type'] == 'SOLO') && $this->isCardAccepted('SOLO')) ) {
            $params['ISSUENUMBER'] = $d['cc_issue_nh-dns'];
        }

        /*if (is_numeric($sendto) && ($sendto > 0)) {
          $params['SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
          $params['SHIPTOSTREET'] = $order->delivery['street_address'];
          $params['SHIPTOCITY'] = $order->delivery['city'];
          $params['SHIPTOSTATE'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
          $params['SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
          $params['SHIPTOZIP'] = $order->delivery['postcode'];
        }*/

        $post_string = '';
        foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
        }
        $post_string = substr($post_string, 0, -1);
        
        $response = $this->sendTransactionToGateway($api_url, $post_string);
        
        $response_array = array();
        parse_str($response, $response_array);
        
        if (($response_array['ACK'] != 'Success') && ($response_array['ACK'] != 'SuccessWithWarning')) {
            $res->addError($response_array['L_LONGMESSAGE0']);
        } else {
            req::setVar('from', 'payments', 'get');
            req::setVar('reqType', 'ajax', 'get');
            $res = frame::_()->getModule('order')->addSuccess();
            $res->addMessage(lang::_('Payment Success'));
            $res->addData('redirect', frame::_()->getModule('pages')->getLink());
        }
        return $res;
    }
}
?>
