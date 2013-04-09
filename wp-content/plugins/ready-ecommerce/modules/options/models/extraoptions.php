<?php
class extraoptionsModel extends model {
    /**
     * Deletes extra option
     * 
     * @param array $d
     * @return string 
     */
    public function deleteOption($d = array()) {
        $res = new response();
        $id = $d['id'];
        if (is_numeric($id)) {
            $extra_option = frame::_()->getTable('extraoptions')->get('ef_id', array('id'=>$id));
            $delete = frame::_()->getTable('extraoptions')->delete(array('id'=>$id));
            if ($delete) {
                frame::_()->getTable('extrafieldsvalue')->delete(array('value'=>$id, 'ef_id' =>$extra_option[0]['ef_id']));
                return '1';
            } else {
                return lang::_('There was an error while deleting the option. Please try again');
            }
        } else {
            frame::_()->getTable('extraoptions')->delete($d);
        }
        return lang::_('Invalid Extra Field Option');
    }
    /**
     * Saves extra field options 
     * @param array $options
     * @param int $id 
     */
    public function saveOptions($options, $id) {
        
        if (!empty($options)) {
            for($i = 0; $i < count($options['value']); $i++) {
                $options['value'][$i] = trim($options['value'][$i]);
                if(empty($options['value'][$i]))    continue;
                $insert = array(
                    'ef_id'=>$id,
                    'value' => $options['value'][$i], 
                    'data' => utils::jsonEncode(array(
                        'price' => $options['data']['price'][$i], 
                        'weight' => $options['data']['weight'][$i],
                        'onlyForPids' => $options['data']['onlyForPids'][$i],
                    )),
                );
                frame::_()->getTable('extraoptions')->insert($insert);
            }
        }
    }
    protected function _itemFromDb($item) {
        if(is_string($item['data']))
            $item['data'] = utils::jsonDecode($item['data']);
        return $item;
    }
    public function get($d = array()) {
        $items = array();
        $fromDb = frame::_()->getTable('extraoptions')->get('*', $d);
        foreach($fromDb as $k => $v) {
            $items[$k] = $this->_itemFromDb($v);
        }
        return $items;
    }
}
?>
