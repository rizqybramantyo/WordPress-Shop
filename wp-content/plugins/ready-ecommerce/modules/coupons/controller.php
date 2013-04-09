<?php
class couponsController extends controller {
    public function storeCouponPattern() {
        $res = new response();
        if($id = $this->getModel('coupons_patterns')->store(req::get('post'))) {
            $res->addData($this->getModel('coupons_patterns')->get(array('id' => $id)));
            $res->addMessage(lang::_('Done'));
        } else {
            $res->pushError($this->getModel('coupons_patterns')->getErrors());
        }
        $res->ajaxExec();
    }
    public function deleteCouponTemplate() {
        $res = new response();
        if($this->getModel('coupons_patterns')->delete(req::get('post'))) {
            $res->addMessage(lang::_('Deleted'));
            $res->addData('id', req::getVar('id'));
        } else
            $res->addError( $this->getModel('coupons_patterns')->getErrors() );
        return $res->ajaxExec();
    }
    public function createCoupons() {
        $res = new response();
        if($this->getModel('coupons')->createFromPattern(req::get('post'))) {
            $res->addMessage(lang::_('Coupons Created'));
            $res->addData( $this->getModel('coupons')->get() );
        } else 
            $res->addError( $this->getModel('coupons')->getErrors() );
        return $res->ajaxExec();
    }
    public function deleteCoupon() {
        $res = new response();
        if($this->getModel('coupons')->delete(req::get('post'))) {
            $res->addMessage(lang::_('Done'));
        } else 
            $res->addError( $this->getModel('coupons')->getErrors() );
        return $res->ajaxExec();
    }
    public function applyCoupon() {
        $res = new response();
        if($this->getModel('coupons')->apply(req::get('post'))) {
            $res->addData('totalHtml', frame::_()->getModule('checkout')->getView()->getTotal());
            $res->addMessage(lang::_('Done'));
        } else
            $res->addError( $this->getModel('coupons')->getErrors() );
        return $res->ajaxExec();
    }
}
?>
