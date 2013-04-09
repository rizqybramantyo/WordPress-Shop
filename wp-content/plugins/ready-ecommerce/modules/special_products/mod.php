<?php
class special_products extends module {
    protected $_pids = array();
    protected $_allSpecials = array();
    protected $_loaded = false;
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    public function getTabs(){
        $tabs = array();
        $tab = new tab(lang::_('Special Products'), $this->getCode());
        $tab->setView('special_products');
        $tabs[] = $tab;
        return $tabs;
    }
    public function isSpecial($pid) {
        $this->_loadData();
    }
    public function getPrice($pid, $price) {
        $this->_loadData();
        if(($specials = $this->getSpecialByPid($pid))) {
            foreach($specials as $s) {
                $price += ((int)$s['absolute'] ? (float) $s['price_change'] : ((float) $s['price_change'] * $price / 100));
            }
        }
        return $price;
    }
    protected function _loadData() {
        if(!$this->_loaded) {
            $this->_allSpecials = $this->getModel()->get();
            $categories = array();
            if(!empty($this->_allSpecials)) {
                $catToPid = frame::_()->getModule('products')->getModel()->postsToCategories();
                $temp = $this->_allSpecials;
                foreach($temp as $i => $s) {
                    if(!empty($s['apply_to']['products'])) {
                        $this->_pids = array_merge($this->_pids, $s['apply_to']['products']);
                    }
                    if(!empty($s['apply_to']['categories'])) {
                        foreach($s['apply_to']['categories'] as $cid) {
                            if(!empty($catToPid[$cid]['products'])) {
                                $catToPid[$cid]['products'] = array_map('intval', $catToPid[$cid]['products']);
                                $this->_pids = array_merge($this->_pids, $catToPid[$cid]['products']);
                                $this->_allSpecials[$i]['apply_to']['products'] = array_merge($this->_allSpecials[$i]['apply_to']['products'], $catToPid[$cid]['products']);
                            }
                        }
                    }
                }
            }
            $this->_loaded = true;
        }
    }
    public function getSpecialByPid($pid) {
        $this->_loadData();
        $specials = array();
        if(!empty($this->_allSpecials)) {
            foreach($this->_allSpecials as $s) {
                if(in_array((int) $pid, $s['apply_to']['products'])) {
                    $specials[] = $s;
                }
            }
        }
        return $specials;
    }
}
?>
