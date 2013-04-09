<?php
class tableSpecial_products extends table {
    public function __construct() {
        $this->_table = '@__special_products';
        $this->_id = 'id';
        $this->_alias = 'toe_sp';
        $this->_addField('id', 'hidden', 'int', 0, lang::_('ID'))
                ->_addField('label', 'text', 'varchar', '', lang::_('Label'))
                ->_addField('price_change', 'text', 'float', 0, lang::_('Price Change'))
                ->_addField('absolute', 'checkbox', 'tinyint', 0, lang::_('Absolute'))
                ->_addField('apply_to', 'text', 'text', '', lang::_('Apply To'))
                ->_addField('mark_as_sale', 'checkbox', 'tinyint', 0, lang::_('Mark as Sale'))
                ->_addField('active', 'checkbox', 'tinyint', 1, lang::_('Active'));
    }
    public function install($d = array()) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        /* special_products */
        $q = "CREATE TABLE IF NOT EXISTS `".S_WPDB_PREF.S_DB_PREF."special_products` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `label` varchar(255),
          `price_change` float(10,2) NOT NULL DEFAULT '0.00',
          `absolute` tinyint(1) NOT NULL DEFAULT '0',
          `apply_to` text,
          `mark_as_sale` tinyint(1) NOT NULL DEFAULT '0',
          `active` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8;";
        dbDelta($q);
    }
    public function uninstall($d = array()) {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `".S_WPDB_PREF.S_DB_PREF."special_products`");
    }
}
?>
