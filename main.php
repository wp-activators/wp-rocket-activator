<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP Rocket Activ@tor
 * Plugin URI:        https://bit.ly/rkt-act
 * Description:       WP Rocket Plugin Activ@tor
 * Version:           1.2.0
 * Requires at least: 5.9.0
 * Requires PHP:      7.2
 * Author:            moh@medhk2
 * Author URI:        https://bit.ly/medhk2
 **/

defined( 'ABSPATH' ) || exit;
$PLUGIN_NAME   = 'WP Rocket Activ@tor';
$PLUGIN_DOMAIN = 'wp-rocket-activ@tor';
extract( require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php' );
if (
	$admin_notice_ignored()
	|| $admin_notice_plugin_install( 'wp-rocket/wp-rocket.php', null, 'WP Rocket', $PLUGIN_NAME, $PLUGIN_DOMAIN )
	|| $admin_notice_plugin_activate( 'wp-rocket/wp-rocket.php', $PLUGIN_NAME, $PLUGIN_DOMAIN )
) {
	return;
}
define( 'WP_ROCKET_KEY', 'free4all' );
define( 'WP_ROCKET_EMAIL', $consumer_email = 'free4all@wp-activ-ators.github' );
set_transient( 'wp_rocket_customer_data', $wp_rocket_customer_data = (object) [
	'licence_account'     => '-1',
	'license_type'        => 'Infinite',
	'has_one-com_account' => false,
	'licence_expiration'  => strtotime( '+1000 year' ),
], 1000 * YEAR_IN_SECONDS );
set_transient( 'rocket_analytics_optin', 0, 1000 * YEAR_IN_SECONDS );

$rocket_boxes = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );
if ( ! in_array( 'rocket_activation_notice', $rocket_boxes, true ) ) {
	$rocket_boxes[] = 'rocket_activation_notice';
	update_user_meta( get_current_user_id(), 'rocket_boxes', $rocket_boxes );
}
add_action( 'plugins_loaded', function () use ( $consumer_email ) {
	update_rocket_option( 'secret_key', hash( 'crc32', $consumer_email ) );
	update_rocket_option( 'analytics_enabled', 0 );
}, 99 );

use WP_Rocket\Engine\License\API\UserClient;

add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) use ( $wp_rocket_customer_data, $json_response ) {
	switch ( $url ) {
		case UserClient::USER_ENDPOINT:
			return $json_response( $wp_rocket_customer_data );
		case WP_ROCKET_WEB_API . 'pause-licence.php':
		case WP_ROCKET_WEB_API . 'activate-licence.php':
			return $json_response( [] );

	}

	return $pre;
}, 99, 3 );
