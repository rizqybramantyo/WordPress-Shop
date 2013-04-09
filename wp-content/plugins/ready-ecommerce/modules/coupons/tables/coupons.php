<?php
class tableCoupons extends table {
    public function __construct()
    {
        $this->_table = '@__coupons';
        $this->_id = 'id';
        $this->_alias = 'toe_cpns';
        $this->_addField('id', 'hidden', 'int', 0, lang::_('ID'))
                ->_addField('pattern_id', 'text', 'int', 0, lang::_('Pattern ID'))
                ->_addField('code', 'text', 'varchar', '', lang::_('Code'), 128)
                ->_addField('used', 'text', 'tinyint', 0, lang::_('Used'));
    }
    public function install($d = array())
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta("CREATE TABLE `".S_WPDB_PREF.S_DB_PREF."coupons` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pattern_id` int(11) NOT NULL DEFAULT '0',
          `code` varchar(128) NOT NULL,
          `used` tinyint(1) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        )DEFAULT CHARSET=utf8");
    }

    public function uninstall($d = array())
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `" . S_WPDB_PREF . S_DB_PREF . "coupons`");
    }
}
?>
