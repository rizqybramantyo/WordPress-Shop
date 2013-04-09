<?php
class tableGifts_conditions extends table {
    public function __construct() {
        $this->_table = '@__gifts_conditions';
        $this->_id = 'id';
        $this->_alias = 'toe_gc';
        $this->_addField('id', 'text', 'int', 0, lang::_('ID'))
                ->_addField('label', 'text', 'varchar', '', lang::_('Label'), 255);
    }
}
?>
