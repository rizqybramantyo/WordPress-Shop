<?php

class tableCoupons_patterns extends table
{
    public function __construct()
    {
        $this->_table = '@__coupons_patterns';
        $this->_id = 'id';
        $this->_alias = 'toe_cps_patterns';
        $this->_addField('id', 'hidden', 'int', 0, lang::_('ID'))
                ->_addField('label', 'text', 'varchar', '', lang::_('Label'))
                ->_addField('type', 'selectbox', 'enum', '', lang::_('Type'))
                ->_addField('length', 'text', 'int', 0, lang::_('Length'))
                ->_addField('date_from', 'datepicker', 'date', '', lang::_('Date From'))
                ->_addField('date_to', 'datepicker', 'date', '', lang::_('Date To'))
                ->_addField('num_times_used', 'text', 'int', 0, lang::_('Can be used number of times'))
                ->_addField('gifts', 'selectlist', 'text', 0, lang::_('Gifts'))
                ->_addField('generate_amount', 'text', 'int', 0, lang::_('Number to be generated'));
        $this->_fields['type']->addHtmlParam('options', array(
            'numeric' => 'numeric',
            'alphabetic' => 'alphabetic',
            'both' => 'both'
        ));
    }

    public function install($d = array())
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta("CREATE TABLE `".S_WPDB_PREF.S_DB_PREF."coupons_patterns` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `label` varchar(255) DEFAULT NULL,
          `type` enum('numeric','alphabetic','both') NOT NULL DEFAULT 'both',
          `length` int(3) unsigned NOT NULL DEFAULT '0',
          `date_from` date DEFAULT NULL,
          `date_to` date DEFAULT NULL,
          `num_times_used` int(11) unsigned NOT NULL DEFAULT '0',
          `gifts` text,
          `generate_amount` int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        )DEFAULT CHARSET=utf8");
    }

    public function uninstall($d = array())
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `" . S_WPDB_PREF . S_DB_PREF . "coupons_patterns`");
    }

}

?>
