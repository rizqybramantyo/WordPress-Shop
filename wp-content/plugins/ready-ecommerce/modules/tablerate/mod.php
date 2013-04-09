<?php
class toecTablerate extends shippingModule {
    public function init() {
        parent::init();
    }
    protected function _calcRate() {
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        if(!empty($cart)) {
            $totalWeight = 0;
            foreach($cart as $pid => $p) {
                $totalWeight += (int)$p['qty'] * (float)$p['weight'];
            }
            if(!empty($this->_params->table_rate) && is_array($this->_params->table_rate)) {
                foreach($this->_params->table_rate as $rowRate) {
                    if($totalWeight <= (float)$rowRate->weight) {
                        $this->_rate = (float)$rowRate->price;
                        break;
                    }
                }
            }
        }
    }
}
?>