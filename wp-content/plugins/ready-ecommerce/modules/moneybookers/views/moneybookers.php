<?php
class moneybookersView extends view {
    public function getForm() {
        $inputFields = frame::_()->getModule('moneybookers')->getInputFields();

        $fields = array();
        $order = frame::_()->getModule('order')->getCurrent();
        foreach($inputFields as $key => $inp) {
            $f = new field($key, $inp['type']);
            $fields[$key] = $f;
        }
        $lang = explode('-', get_bloginfo('language'));
        $modParams = frame::_()->getModule('moneybookers')->getParams();
        $fields['pay_to_email']->setValue($modParams->email);
        $fields['status_url']->setValue(uri::mod('order', '', 'addSuccess', array(
                        'from' => 'payments',
                        'redirect' => frame::_()->getModule('pages')->getLink(array('mod' => 'checkout', 'action' => 'getAllHtml', 'data' => array('step' => 3))))));
        $fields['language']->setValue($lang[1]);
        $fields['amount']->setValue($order['total']);
        $fields['currency']->setValue($order['currency']['code']);
        $fields['detail1_description']->setValue('lalala');
        $fields['detail1_text']->setValue('lololo');
        
        $this->assign('fields', $fields);
        $this->assign('gatewayUrl', frame::_()->getModule('moneybookers')->getGatewayUrl());
        return parent::getContent('form');
    }
}
?>
