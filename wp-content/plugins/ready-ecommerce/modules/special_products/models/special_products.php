<?php
class special_productsModel extends model {
    public function get($d = array()) {
        $data = frame::_()->getTable('special_products')->get('*', $d);
        if(!empty($data)) {
            $temp = $data;
            foreach($temp as $i => $sp) {
                $apply_to = utils::jsonDecode($sp['apply_to']);
                if(empty($apply_to['categories'])) {
                    $apply_to['categories'] = array();
                } else {
                    $apply_to['categories'] = array_map('intval', $apply_to['categories']);
                }
                if(empty($apply_to['products'])) {
                    $apply_to['products'] = array();
                } else {
                    $apply_to['products'] = array_map('intval', $apply_to['products']);
                }
                $data[$i]['apply_to'] = $apply_to;
            }
        }
        return $data;
    }
    public function store($d = array()) {
        $d = frame::_()->getTable('special_products')->prepareInput($d);
        $res = false;
        if(isset($d['price_change'])) {
            $d['price_change'] = (float) $d['price_change'];
            if($d['price_change'] > 0 && (int) $d['sign']) {    //$d['sign'] = 0 equals to "+", $d['sign'] = 1 equals to "-"
                $d['price_change'] *= -1;
            }
        }
        if(empty($d['ignore']) || !in_array('apply_to', $d['ignore'])) {
            if((int) $d['apply_to']['categories']) {
                $d['apply_to']['categories'] = $d['categories'];
            } else {
                $d['apply_to']['categories'] = array();
            }
            if((int) $d['apply_to']['products']) {
                $d['apply_to']['products'] = $d['products'];
            } else {
                $d['apply_to']['products'] = array();
            }
            $d['apply_to'] = utils::jsonEncode($d['apply_to']);
        }
        if($d['id']) {
            $dbRes = frame::_()->getTable('special_products')->update($d, $d['id']);
        } else {
            $dbRes = frame::_()->getTable('special_products')->insert($d);
        }
        if($dbRes) {
            if($d['id']) {
                $res = $d['id'];
            } else {
                $res = $dbRes;
            }
        } else
            $this->pushError( frame::_()->getTable('special_products')->getErrors() );
        return $res;
    }
    public function delete($d = array()) {
        $d['id'] = (int) $d['id'];
        $res = false;
        if($d['id']) {
            if(frame::_()->getTable('special_products')->delete($d['id'])) {
                $res = $d['id'];
            } else
                $this->pushError( frame::_()->getTable('special_products')->getErrors() );
        } else 
            $this->pushError(lang::_('Invalid ID'));
        return $res;
    }
}
?>
