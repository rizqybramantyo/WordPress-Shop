<?php
class paypalproController extends controller {
    public function sendPayments() {
        $res = new response();
        $res = frame::_()->getModule('paypalpro')->validateInput(req::get('post'));
        if(!$res->error) {
            $res = frame::_()->getModule('paypalpro')->processPayment(req::get('post'));
        }
        return $res->ajaxExec();
    }
}
?>
