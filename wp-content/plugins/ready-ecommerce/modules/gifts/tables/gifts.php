<?php
class tableGifts extends table {
    public function __construct() {
        $this->_table = '@__gifts';
        $this->_id = 'id';
        $this->_alias = 'toe_gift';
        $this->_addField('id', 'text', 'int', 0, lang::_('ID'))
                ->_addField('label', 'text', 'varchar', '', lang::_('Label'), 255)
                ->_addField('conditions', 'text', 'text', '', lang::_('Conditions'))
                ->_addField('type', 'text', 'varchar', '', lang::_('Type'), 32)
                ->_addField('type_params', 'text', 'text', '', lang::_('Type Params'))
                ->_addField('date_from', 'text', 'date', '', lang::_('Date From'))
                ->_addField('date_to', 'text', 'date', '', lang::_('Date To'));
    }
    public function install($d = array()) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $q = "CREATE TABLE `".S_WPDB_PREF.S_DB_PREF."gifts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `label` varchar(255) NOT NULL,
          `conditions` text,
          `type` varchar(32) DEFAULT NULL,
          `type_params` text,
          `date_from` date DEFAULT NULL,
          `date_to` date DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8";
        dbDelta($q);
        $q = "CREATE TABLE `".S_WPDB_PREF.S_DB_PREF."gifts_conditions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `label` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8 ";
        dbDelta($q);
        $wpdb->query("insert into `".S_WPDB_PREF.S_DB_PREF."gifts_conditions` values
          (1,'For certain Price'),
          (2,'More than X count of products'),
          (3,'When buying in certain date'),
          (4,'Buying product from certain category(es)'),
          (5,'Buiyng certain product(s)'),
          (6,'Register users only'),
          (7,'Customers who have certain count of orders'),
          (8,'Customers who bought for certain total from all orders')");
        $q = "CREATE TABLE `".S_WPDB_PREF.S_DB_PREF."gifts_to_orders` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) NOT NULL DEFAULT '0',
          `gift_data` text,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8 ";
        dbDelta($q);
    }
    public function uninstall($d = array()) {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `".S_WPDB_PREF.S_DB_PREF."gifts`");
        $wpdb->query("DROP TABLE IF EXISTS `".S_WPDB_PREF.S_DB_PREF."gifts_conditions`");
        $wpdb->query("DROP TABLE IF EXISTS `".S_WPDB_PREF.S_DB_PREF."gifts_to_orders`");
    }
}
?>