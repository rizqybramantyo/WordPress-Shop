<?php
class coupons_patternsModel extends model {
    public function store($d = array()) {
        $d = frame::_()->getTable('coupons_patterns')->prepareInput($d);
        $res = false;
        $errors = array();
        if(empty($d['length']))
            $errors[] = lang::_('Please enter length for coupons codes');
        if(empty($d['generate_amount']))
            $errors[] = lang::_('Please enter Number to be generated');
        if(empty($errors)) {
            if(!empty($d['gifts']))
                $d['gifts'] = utils::jsonEncode($d['gifts']);

            if($d['id']) {
                $dbRes = frame::_()->getTable('coupons_patterns')->update($d, $d['id']);
            } else {
                $dbRes = frame::_()->getTable('coupons_patterns')->insert($d);
            }

            if($dbRes) {
                if($d['id']) {
                    $res = $d['id'];
                } else {
                    $res = $dbRes;
                }
            } else
                $this->pushError( frame::_()->getTable('coupons_patterns')->getErrors() );
        } else
            $this->pushError($errors);
        return $res;
    }
    public function get($d = array()) {
        $data = frame::_()->getTable('coupons_patterns')->get('*', $d);
        if(!empty($data)) {
            $data = $this->afterGet($data);
        }
        return $data;
    }
    public function afterGet($data) {
        $temp = $data;
        foreach($temp as $i => $t) {
            $data[$i] = frame::_()->getTable('coupons_patterns')->prepareOutput($data[$i]);
            $data[$i]['gifts'] = utils::jsonDecode($t['gifts']);
        }
        return $data;
    }
    public function delete($d = array()) {
        $d['id'] = (int) $d['id'];
        $res = false;
        if($d['id']) {
            if(frame::_()->getTable('coupons_patterns')->delete($d['id'])) {
                $res = $d['id'];
            } else
                $this->pushError( frame::_()->getTable('coupons_patterns')->getErrors() );
        } else 
            $this->pushError(lang::_('Invalid ID'));
        return $res;
    }
}
?>
