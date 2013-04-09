<?php
class tableExtrafieldsvalue extends table {
    public function __construct() {
        $this->_table = '@__ef_val';
        $this->_id = 'id';
        $this->_alias = 'toe_ef_val';
        $this->_addField('parent_id', 'selectbox', 'int', '', lang::_('Parent'))
                ->_addField('parent_type', 'text', 'varchar','', lang::_('Parent Type'), 255)
                ->_addField('ef_id', 'text', 'int','', lang::_('Extrafield ID'), 11)
                ->_addField('opt_id', 'text', 'int','', lang::_('Option ID'), 11)
                ->_addField('value', 'textarea', 'longtext','', lang::_('Value'))
                ->_addField('price', 'text', 'float', 0, lang::_('Price'))
                ->_addField('price_absolute', 'text', 'tinyint', 0, lang::_('Absolute'))
                ->_addField('disabled', 'text', 'tinyint', 1, lang::_('Disabled'));
        
    }
}
?>
