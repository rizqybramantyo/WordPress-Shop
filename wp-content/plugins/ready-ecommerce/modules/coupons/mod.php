<?php
class coupons extends module {
    public function init() {
        parent::init();
        dispatcher::addFilter('afterCartPrint', array($this->getView(), 'checkoutDisplay'));
    }
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('Coupons'), $this->getCode());
        $tab->setView('coupons');
        $tabs[] = $tab;
        return $tabs;
    }
    public function useCouponPattern($patternData) {
        $allUsed = $this->getUsedCouponPatterns();
        if(!isset($allUsed[$patternData['id']])) {
            $allUsed[$patternData['id']] = $patternData;
        }
        req::setVar('usedCouponPatterns', $allUsed, 'session');
    }
    public function getUsedCouponPatterns() {
        return req::getVar('usedCouponPatterns', 'session');
    }
}
?>
