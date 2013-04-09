<?php
class tableExtraoptions extends table {
    public function __construct() {
        $this->_table = '@__ef_options';
        $this->_id = 'id';
        $this->_alias = 'toe_ef_o';
        $this->_addField('ef_id', 'selectbox', 'int', '', lang::_('Extrafield'))
                ->_addField('value', 'text', 'varchar','', lang::_('Option label'), 255)
                ->_addField('data', 'text', 'text','', lang::_('Option Data'), 255);
    }
}
?>
