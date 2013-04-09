<?php
class installerDbUpdater {
	static public function update_0312() {
		db::query("INSERT INTO `".S_WPDB_PREF.S_DB_PREF."options` VALUES
		  (NULL,'ssl_on_checkout','','Use SSL on checkout','If you have already setup correct SSL for your site and want always open checkout with SSL protocol - enable this option',4,NULL,2,0),
		  (NULL,'ssl_on_account','','Use SSL on user account','If you have already setup correct SSL for your site and want always open registration, login and user account pages with SSL protocol - enable this option',4,NULL,2,0),
		  (NULL,'ssl_on_ajax','','Use SSL on AJAX requests','If you have already setup correct SSL for your site and want always use SSL protocol for AJAX requests - enable this option',4,NULL,2,0);");
	}
	static public function update_0313() {
		db::query("INSERT INTO `".S_WPDB_PREF.S_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
			(NULL,'countries',1,1,'',1,'Countries','Countries for your store'),
			(NULL,'states',1,1,'',1,'States','States for your store');");
	}
	static public function update_0314() {	
		// Empty, but we need this - at least for history)
	}
	static public function update_0315() {
		db::query("INSERT INTO `".S_WPDB_PREF.S_DB_PREF."options` VALUES
		  (NULL,'show_subcategories_on_categories_page','','Show sub categories on categories page','We install by default page with all categories. If this option is enables - on that page you will see sub-categories too.',4,NULL,4,0);");
	}
	static public function update_0316() {
		db::query("INSERT INTO `".S_WPDB_PREF.S_DB_PREF."email_templates` VALUES
		(NULL,'New user registration', 'New user registration at :store_name', 'New user registered at :store_name. <br />\r\nUser info: <br />\r\nUsername: :username<br />\r\nPassword: :password<br />\r\nThanks for using our online store!', '[\"username\", \"store_name\", \"password\"]', 1, 'admin_notify', 'user');");
	}
	static public function update_0317() {
		db::query("ALTER TABLE `".S_WPDB_PREF.S_DB_PREF."currency` MODIFY value float(9,3);");
		db::query("INSERT INTO  `".S_WPDB_PREF.S_DB_PREF."options` VALUES (NULL ,  'terms', '' ,  'Terms and conditions',  'Terms and conditions',  3, NULL ,  2,  0);");
		db::query("INSERT INTO  `".S_WPDB_PREF.S_DB_PREF."options` VALUES (NULL ,  'notify_on_reg', '0' ,  'Notify on user registration',  'Notify on user registration',  4, NULL ,  1,  0);");
		db::query("UPDATE `". S_WPDB_PREF.S_DB_PREF. "states` SET country_id='225' WHERE country_id='223'");	// We had some offset in countries table when added 2 countries
	}
	static public function update_0318() {
		db::query("UPDATE `". S_WPDB_PREF.S_DB_PREF. "options` SET label='Skip confirmation step on checkout' WHERE code='checkout_skip_confirm_step'");	// Typo error in database - this can be removed after month maybe, now is April 2013
	}
	static public function runUpdate() {
		self::update_0312();
		self::update_0313();
		self::update_0314();
		self::update_0315();
		self::update_0316();
		self::update_0317();
		self::update_0318();
	}
}