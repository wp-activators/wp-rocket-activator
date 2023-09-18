<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WP Rocket Activator
 * Plugin URI:        https://github.com/wp-activators/wp-rocket-activator
 * Description:       WP Rocket Plugin Activator
 * Version:           1.0.0
 * Requires at least: 3.1.0
 * Requires PHP:      7.2
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;
const WP_ROCKET_ACTIVATOR_NAME   = 'WP Rocket Activator';
const WP_ROCKET_ACTIVATOR_DOMAIN = 'wp-rocket-activator';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
if (
	activator_admin_notice_ignored()
	|| activator_admin_notice_plugin_install( 'wp-rocket/wp-rocket.php', null, 'WP Rocket', WP_ROCKET_ACTIVATOR_NAME, WP_ROCKET_ACTIVATOR_DOMAIN )
	|| activator_admin_notice_plugin_activate( 'wp-rocket/wp-rocket.php', WP_ROCKET_ACTIVATOR_NAME, WP_ROCKET_ACTIVATOR_DOMAIN )
) {
	return;
}
define( 'WP_ROCKET_KEY', 'free4all' );
define( 'WP_ROCKET_EMAIL', $consumer_email = 'free4all@wp-activators.github' );
set_transient( 'wp_rocket_customer_data', $wp_rocket_customer_data = (object) [
	'licence_account'     => '-1',
	'license_type'        => 'Infinite',
	'has_one-com_account' => false,
	'licence_expiration'  => strtotime( '+1000 year' ),
], 1000 * YEAR_IN_SECONDS );

$rocket_boxes = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );
if ( ! in_array( 'rocket_activation_notice', $rocket_boxes, true ) ) {
	$rocket_boxes[] = 'rocket_activation_notice';
	update_user_meta( get_current_user_id(), 'rocket_boxes', $rocket_boxes );
}
add_action( 'plugins_loaded', function () use ( $consumer_email ) {
	update_rocket_option( 'secret_key', hash( 'crc32', $consumer_email ) );
}, 99 );

use WP_Rocket\Engine\License\API\UserClient;

add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) use ( $wp_rocket_customer_data ) {
	if ( $url == UserClient::USER_ENDPOINT ) {
		return activator_json_response( $wp_rocket_customer_data );
	}

	return $pre;
}, 99, 3 );
