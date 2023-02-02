<?php
/*
Plugin Name: Atmosic User Data Sync
Plugin URI:  https://github.com/fusionary/wp-translation-plugin
Description: Save Atmosic mobile app user data to WordPress database
Version:     1.0.0
Author:      Fusionary
Author URI:  https://fusionary.com
*/

register_activation_hook(__FILE__, 'create_user_data_table');

function create_user_data_table() {
  global $wpdb;

  $create_table_query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}app_user_data` (
      `id` bigint(20) unsigned primary key NOT NULL AUTO_INCREMENT,
      `userID` bigint(20) unsigned NOT NULL default '0' UNIQUE,
      `job_title` text NOT NULL,
      `company` text NOT NULL,
      `country` text NOT NULL
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
  ";
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($create_table_query);
}
