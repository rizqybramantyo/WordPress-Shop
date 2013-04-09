<?php
class couponsModel extends model {
    protected $_alphabeth = array();
    protected $_numbers = array();
    public function __construct() {
        $this->_alphabeth = range('A', 'Z');
        $this->_numbers = range(0, 9);
    }
    /**
     * Create list of coupons from coupons pattern
     * @param numeric $d['id'] coupon pattern id
     */
    public function createFromPattern($d = array()) {
        $d['id'] = (int) $d['id'];
        if($d['id']) {
            $pattern = frame::_()->getModule('coupons')->getModel('coupons_patterns')->get(array('id' => $d['id']));
            if(!empty($pattern) && !empty($pattern[0])) {
                $d['generate_amount'] = (int) $d['generate_amount'];
                $amount = $d['generate_amount'] ? $d['generate_amount'] : $pattern['generate_amount'];
                if($amount) {
                    $pattern = $pattern[0];
                    $query = 'INSERT INTO @__coupons (pattern_id, code) VALUES ';
                    for($i = 0; $i < (int)$amount; $i++) {
                        $query .= '('. $d['id']. ', "'. $this->_generateCode($pattern['type'], $pattern['length']).'"), ';
                    }
                    $query = substr($query, 0, -2);
                    return db::query($query);
                } else
                    $this->pushError (lang::_('Invalid amount of coupons to be generated'));
            } else 
                $this->pushError( lang::_('No mathing pattern were found') );
        } else
            $this->pushError( lang::_('Empty or invalid coupon pattern ID') );
        return false;
    }
    public function get($d = array()) {
        $data = frame::_()->getTable('coupons')->get('*', $d);
        return $data;
    }
    protected function _generateCode($type, $len) {
        $symbols = array();
        $name = '';
        switch($type) {//'numeric','alphabetic','both'
            case 'numeric':
                $symbols = $this->_numbers;
                break;
            case 'alphabetic':
                $symbols = $this->_alphabeth;
                break;
            case 'both':
            default:
                $symbols = array_merge($this->_numbers, $this->_alphabeth);
                break;
        }
        $symbolsCount = count($symbols);
        for($i = 0; $i < $len; $i++) {
            $name .= $symbols[ mt_rand(0, $symbolsCount) ];
        }
        return $name;
    }
    public function delete($d = array()) {
        if(isset($d['id']))
            $d['id'] = (int) $d['id'];
        if(isset($d['pattern_id']))
            $d['pattern_id'] = (int) $d['pattern_id'];
        $res = false;
        if($d['id'] || $d['pattern_id']) {
            if(frame::_()->getTable('coupons')->delete($d)) {
                $res = $d['id'] ? $d['id'] : $d['pattern_id'];
            } else
                $this->pushError( frame::_()->getTable('coupons')->getErrors() );
        } else 
            $this->pushError(lang::_('Invalid ID'));
        return $res;
    }
    public function apply($d = array()) {
        $errors = array();
        if(empty($d['coupon']))
            $errors[] = lang::_('Empty coupon code');
        $d['coupon'] = validator::prepareInput($d['coupon']);
        if(empty($errors)) {
            $pattern = db::get('SELECT cp.*, c.id AS coupon_id FROM @__coupons c, @__coupons_patterns cp 
                WHERE c.pattern_id = cp.id
                AND c.used = 0
                AND c.code = "'. $d['coupon']. '"');
            if(empty($pattern)) {
                $errors[] = lang::_('Invalid coupon code');
            } else {
                $pattern = frame::_()->getModule('coupons')->getModel('coupons_patterns')->afterGet($pattern);
                $pattern = $pattern[0];
                $this->store(array(
                    'id' => $pattern['coupon_id'],
                    'used' => 1
                ));
                
                frame::_()->getModule('coupons')->useCouponPattern($pattern);
            }
        }
        if(!empty($errors))
            $this->pushError($errors);
        return empty($errors);
    }
    public function store($d = array()) {
        $d = frame::_()->getTable('coupons')->prepareInput($d);
        $res = false;
        if(empty($errors)) {
            if($d['id']) {
                $dbRes = frame::_()->getTable('coupons')->update($d, $d['id']);
            } else {
                $dbRes = frame::_()->getTable('coupons')->insert($d);
            }
            if($dbRes) {
                if($d['id']) {
                    $res = $d['id'];
                } else {
                    $res = $dbRes;
                }
            } else
                $this->pushError( frame::_()->getTable('coupons')->getErrors() );
        } else
            $this->pushError($errors);
        return $res;
    }
}
?>
