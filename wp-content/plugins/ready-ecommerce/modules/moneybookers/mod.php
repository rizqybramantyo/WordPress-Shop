<?php
class moneybookers extends paymentModule {
    protected $_inputFields = array(
        'pay_to_email' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'status_url' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'language' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'amount' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'currency' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'detail1_description' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
        'detail1_text' => array('type' => 'hidden', 'label' => '', 'validate' => 'notNull'),
    );
    protected $_gatewayUrl = 'https://www.moneybookers.com/app/payment.pl';
    public function __construct($d, $params = array()) {
        parent::__construct($d, $params);
        $this->_haveProcessHtml = true;
    }
    public function getProcessHtml() {
        return $this->getView()->getForm();
    }
}
?>
