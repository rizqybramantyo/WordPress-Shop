<?php
class tableGifts_to_orders extends table {
    public function __construct() {
        $this->_table = '@__gifts_to_orders';
        $this->_id = 'id';
        $this->_alias = 'toe_g_to_o';
        $this->_addField('id', 'text', 'int', 0, lang::_('ID'))
                //->_addField('gift_id', 'text', 'int', 0, lang::_('Gift ID'))
                ->_addField('order_id', 'text', 'int', 0, lang::_('Order ID'))
                ->_addField('gift_data', 'text', 'text', '', lang::_('Gift Data'));
    }
}
?>
