<?php
class gifts extends module {
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    protected $_allGifts = array();
    protected $_applicableGifts = array();
    protected $_loaded = false;
    
    public function init() {
        parent::init();
        dispatcher::addAction('orderPost', array($this->getModel(), 'connectToOrder'));
        dispatcher::addAction('onSuccessOrder', array($this, 'clearCurrent'));
        dispatcher::addFilter('checkoutSubtotalDisplay', array($this->getView(), 'checkoutDisplay'));
        dispatcher::addFilter('postOrderItemParams', array($this->getModel(), 'postOrderItemsParams'), 10, 2);
        dispatcher::addFilter('addProdOrderDisplayData', array($this->getView(), 'addProdOrderDisplayData'), 10, 3);
        dispatcher::addFilter('orderView::showOne[addSubtotal]', array($this->getView(), 'addGiftsDataToOrder'), 10, 2);
    }
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('Gifts'), $this->getCode());
        $tab->setView('gifts');
        $tabs[] = $tab;
        return $tabs;
    }
    

    public function getGifts() {
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        $return = array('data' => array(), 'total' => 0);
        if(!empty($cart)) {
             $this->_loadGifts();
             if(!empty($this->_allGifts)) {
                 $couponsGiftIds = array();
                 if(frame::_()->getModule('coupons')) {
                     $usedCoupons = frame::_()->getModule('coupons')->getUsedCouponPatterns();
                     if(!empty($usedCoupons)) {
                         foreach($usedCoupons as $uc) {
                             if(!empty($uc['gifts']) && is_array($uc['gifts'])) {
                                 $couponsGiftIds = array_merge($couponsGiftIds, $uc['gifts']);
                             }
                         }
                     }
                 }
                 $totalOrders = NULL;
                 $totalFromAllOrders = NULL;
                 $subtotal = 0;
                 
                 $today = strtotime('today');
                 foreach($this->_allGifts as $g) {
                    $applicable = false;
                    $dateSuitable = true;
                    if(!empty($g['date_from']) && ($today < dateToTimestamp($g['date_from']))) {
                        $dateSuitable = false;
                        echo 111;
                    }
                    if(!empty($g['date_to']) && ($today > dateToTimestamp($g['date_to']))) {
                        
                        $dateSuitable = false;
                    }
                    if($dateSuitable) {
                        foreach($g['conditions'] as $cid => $c) {
                            if($applicable)
                                break;
                            if($c['enabled']) {
                                switch($cid) {  //see gifts_conditions table
                                    case 1:
                                        if(empty($subtotal))
                                            $subtotal = frame::_()->getModule('checkout')->getSubTotal();
                                        $c['price_from'] = (float) $c['price_from'];
                                        $c['price_to'] = (float) $c['price_to'];
                                        if(!$c['price_from'] && !$c['price_to'])
                                            break;
                                        if($subtotal >= (float) $c['price_from'] && $subtotal <= $c['price_to'])
                                            $applicable = true;
                                        break;
                                    case 2:
                                        if(count($cart) >= (int) $c['products_count'])
                                            $applicable = true;
                                        break;
                                    case 3:
                                        if(db::dateToTime($c['date']) == strtotime('today'))
                                            $applicable = true;
                                        break;
                                    case 4:
                                        if(!empty($c['categories'])) {
                                            foreach($cart as $p) {
                                                if($applicable) break;
                                                foreach($c['categories'] as $categoryId) {
                                                    if(isset($p['categories'][$categoryId])) {
                                                        $applicable = true;
                                                        break;
                                                    }
                                                 }
                                            }
                                        }
                                        break;
                                    case 5:
                                        if(!empty($c['products'])) {
                                            foreach($c['products'] as $productId) {
                                                if(isset($cart[$productId])) {
                                                    $applicable = true;
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                    case 6:
                                        if(frame::_()->getModule('user')->getCurrentID())
                                            $applicable = true;
                                        break;
                                    case 7:
                                        if(($uid = frame::_()->getModule('user')->getCurrentID()) && (int) $c['orders_count']) {
                                            if($totalOrders === NULL) 
                                                $totalOrders = frame::_()->getTable('orders')->get('COUNT(*) AS total', array('user_id' => $uid), '', 'one');
                                            if((int) $totalOrders >= (int) $c['orders_count'])
                                                $applicable = true;
                                        }
                                        break;
                                    case 8:
                                        if(($uid = frame::_()->getModule('user')->getCurrentID()) && (float) $c['total_amount']) {
                                            if($totalFromAllOrders === NULL)
                                                $totalFromAllOrders = frame::_()->getTable('orders')->get('SUM(total) AS total_summ', array('user_id' => $uid), '', 'one');
                                            if((float) $totalFromAllOrders >= (float) $c['total_amount'])
                                                $applicable = true;
                                        }
                                        break;
                                }
                            }
                        }
                    }
                    /* Check if gift is in coupon */
                    if(!$applicable && in_array($g['id'], $couponsGiftIds))
                            $applicable = true;
                    if($applicable && !empty($g['type'])) {
                        switch($g['type']) {
                            case 'price':
                                if(empty($subtotal))
                                    $subtotal = frame::_()->getModule('checkout')->getSubTotal();
                                $return['total'] += ((int) $g['type_params']['absolute_price'] ? (float) $g['type_params']['price'] : ((float) $g['type_params']['price'] * $subtotal / 100));
                                break;
                            case 'product':
                                $current = $this->getCurrent(array('id' => $g['id']));
                                if($current[0] && $current[0]['used'] && $this->checkProduct(array('pid' => $current[0]['pid'], 'gid' => $g['id']))) {
                                    $g['freeProductLink'] = lang::_('Product selected');
                                    $g['used'] = $current[0]['used'];
                                    $g['pid'] = $current[0]['pid'];
                                } else {
                                    $g['freeProductLink'] = $this->getFreeProductLink($g);
                                }
                                break;
                        }
                        $return['data'][] = $g;
                    }
                }
                $this->setCurrent($return);
            }
        }
        $this->checkExpiredProductsInCart();
        return $return;
    }
    public function getFreeProductLink($gift) {
        return $this->getController()->getView()->getFreeProductLink($gift);
    }
    public function getCurrent($d = array()) {
        if(!empty($d['id'])) {
            $all = req::getVar('currentGifts', 'session');
            $res = array();
            if(!empty($all)) {
                foreach($all['data'] as $g) {
                    if($g['id'] == $d['id'])
                        $res[] = $g;
                }
            }
            return $res;
        } else {
            return req::getVar('currentGifts', 'session');
        }
    }
    public function setCurrent($data) {
        req::setVar('currentGifts', $data, 'session');
    }
    public function clearCurrent() {
        req::clearVar('currentGifts', 'session');
    }
    /**
     * Set gift to used state if product was selected (for product type now)
     */
    public function setUsed($d = array()) {
        if($d['gid']) {
            $current = $this->getCurrent();
            if(!empty($current)) {
                $tempData = $current['data'];
                foreach($tempData as $i => $cg) {
                    if($cg['id'] == $d['gid']) {
                        $current['data'][$i]['used'] = true;
                        $current['data'][$i]['pid'] = $d['pid'];
                    }
                }
                
                $this->setCurrent($current);
            }
        }
    }
    /**
     * Check is in cart gift products that must be deleted
     */
    public function checkExpiredProductsInCart() {
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        //var_dump($cart);
        if(!empty($cart)) {
            
            $currentGifts = $this->getCurrent();
            foreach($cart as $pid => $p) {
                if(!empty($p['gifts'])) {
                    //var_dump($p['gifts']);
                    foreach($p['gifts'] as $gid) {
                        //var_dump($gid);
                        $gift = $this->getCurrent(array('id' => $gid));
                        //var_dump($gift);
                        if(empty($gift)) {
                            frame::_()->getModule('user')->getModel('cart')->put(array('pid' => $pid, 'qty' => ($p['qty']-1)));
                        }
                    }
                }
            }
        }
    }
    /**
     * Check if gift product are still in shopping cart
     */
    public function checkProduct($d = array()) {
        $cart = frame::_()->getModule('user')->getModel('cart')->get();
        if(!empty($cart)) {
            if(isset($cart[ $d['pid'] ]) && in_array($d['gid'], $cart[ $d['pid'] ]['gifts'])) {
                return true;
            }
        }
        return false;
    }
    protected function _loadGifts() {
        if(!$this->_loaded) {
            $this->_allGifts = $this->getModel()->get();
            $this->_loaded = true;
        }
    }
}
?>
