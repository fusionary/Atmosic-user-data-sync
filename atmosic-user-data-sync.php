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

// Register a POST endpoint to save JSON user data to the user data table
add_action('rest_api_init', function () {
  register_rest_route('atmosic', 'update', [
		'methods'  => 'POST',
		'callback' => 'atmosic_save_user_data',
    'permission_callback' => '__return_true',
    'accept_json' => true,
	]);
});

function atmosic_save_user_data($data) {
  global $wpdb;

  $user_id = $data['user_id'];
  $country = $data['country'];
  $job_title = $data['job_title'];
  $company = $data['company'];

  $wpdb->query(
    $wpdb->prepare("INSERT INTO {$wpdb->prefix}app_user_data (userID, country, job_title, company) VALUES (%d, %s, %s, %s)
      ON DUPLICATE KEY UPDATE userID = %d, country = %s, job_title = %s, company = %s",
      $user_id, $country, $job_title, $company, $user_id, $country, $job_title, $company)
  );

  $res = new WP_REST_Response();
  $res->set_status($wpdb->last_error ? 500 : 200);
  $res->set_data($wpdb->last_error);

  return $res;
}

// Register a GET endpoint to retrieve user data for the mobile app
add_action('rest_api_init', function () {
  register_rest_route('atmosic', 'user', [
		'methods'  => 'GET',
		'callback' => 'atmosic_get_user_data',
    'args' => [
			'user_id' => [
				'required' => true,
				'validate_callback' => function ($param) {return is_numeric($param);}
			]
    ],
    'permission_callback' => '__return_true',
	]);
});

function atmosic_get_user_data($data) {
  global $wpdb;

  $user_id = $data['user_id'];

  $user_data = $wpdb->get_row("SELECT * FROM wp_users JOIN {$wpdb->prefix}app_user_data ON {$wpdb->prefix}app_user_data.userID = wp_users.id WHERE wp_users.id = $user_id;", OBJECT);

  $res = new WP_REST_Response();
  $res->set_status($wpdb->last_error ? 500 : 200);
  $res->set_data($wpdb->last_error ? $wpdb->last_error : $user_data);

  return $res;
}
