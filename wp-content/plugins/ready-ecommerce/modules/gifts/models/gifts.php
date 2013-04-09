<?php
class giftsModel extends model {
    /*public function getTypes($d = array()) {
        
    }*/
    public function get($d = array()) {
        $data = frame::_()->getTable('gifts')->get('*', $d);
        if(!empty($data)) {
            $temp = $data;
            foreach($temp as $i => $t) {
                $data[$i]['conditions'] = utils::jsonDecode($t['conditions']);
                $data[$i]['type_params'] = utils::jsonDecode($t['type_params']);
                
                if($data[$i]['date_from'] == '0000-00-00')
                    $data[$i]['date_from'] = '';
                else
                    $data[$i]['date_from'] = date(S_DATE_FORMAT, db::dateToTime($data[$i]['date_from']));
                
                if($data[$i]['date_to'] == '0000-00-00')
                    $data[$i]['date_to'] = '';
                else
                    $data[$i]['date_to'] = date(S_DATE_FORMAT, db::dateToTime($data[$i]['date_to']));
            }
        }
        return $data;
    }
    public function store($d = array()) {
        $d = frame::_()->getTable('gifts')->prepareInput($d);
        $res = false;
        /*if(empty($d['date_from'])) 
            $d['date_from'] = '0000-00-00';
        else
            $d['date_from'] = db::timeToDate($d['date_from']);
        
        if(empty($d['date_to'])) 
            $d['date_to'] = '0000-00-00';
        else
            $d['date_to'] = db::timeToDate($d['date_to']);*/
        
        if(!empty($d['conditions']))
            $d['conditions'] = utils::jsonEncode($d['conditions']);
        if(!empty($d['type_params']))
            $d['type_params'] = utils::jsonEncode($d['type_params']);
        
        if($d['id']) {
            $dbRes = frame::_()->getTable('gifts')->update($d, $d['id']);
        } else {
            $dbRes = frame::_()->getTable('gifts')->insert($d);
        }
        
        if($dbRes) {
            if($d['id']) {
                $res = $d['id'];
            } else {
                $res = $dbRes;
            }
        } else
            $this->pushError( frame::_()->getTable('gifts')->getErrors() );
        return $res;
    }
    public function delete($d = array()) {
        $d['id'] = (int) $d['id'];
        $res = false;
        if($d['id']) {
            if(frame::_()->getTable('gifts')->delete($d['id'])) {
                $res = $d['id'];
            } else
                $this->pushError( frame::_()->getTable('gifts')->getErrors() );
        } else 
            $this->pushError(lang::_('Invalid ID'));
        return $res;
    }
    public function selectProduct($d = array()) {
        $d['gid'] = (int) $d['gid'];
        $d['pid'] = (int) $d['pid'];
        if($d['gid']) {
            if($d['pid']) {
                frame::_()->getModule('user')->getModel('cart')->put(array('pid' => $d['pid'], 'gift' => $d['gid']));
                frame::_()->getModule('gifts')->setUsed($d);
            } else
                $this->pushError(lang::_('Invalid Product ID'));
        } else 
            $this->pushError(lang::_('Invalid gift ID'));
        return false;
    }
    public function connectToOrder($orderId) {
        $orderId = (int) $orderId;
        if(!empty($orderId)) {
            $current = frame::_()->getModule('gifts')->getCurrent();
            if(!empty($current)) {
                frame::_()->getTable('gifts_to_orders')->delete(array('order_id' => $orderId));
                frame::_()->getTable('gifts_to_orders')->insert(array(
                    'order_id' => $orderId,
                    'gift_data' => utils::jsonEncode($current),
                ));
            }
        }
    }
    public function postOrderItemsParams($params, $item) {
        if(empty($params))
            $params = array();
        else
            $params = utils::jsonDecode($params);
        if(!empty($item['gifts'])) {
            $params['gifts'] = $item['gifts'];
        }
        if(!empty($item['gift'])) {
            $params['gift'] = $item['gift'];
        }
        return utils::jsonEncode($params);
    }
}
?>
