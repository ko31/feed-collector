<?php
/**
 * Plugin Name:     Feed Collector
 * Plugin URI:      https://github.com/ko31/feed-collector
 * Description:     This is a plugin.
 * Author:          ko31
 * Author URI:      https://go-sign.info
 * Text Domain:     feed-collector
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Feed_Collector
 */

namespace Feed_Collector;

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FEED_COLLECTOR_VERSION', '1.0.0' );
define( 'FEED_COLLECTOR_TEXT_DOMAIN', 'feed-controller' );
define( 'FEED_COLLECTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'FEED_COLLECTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'FEED_COLLECTOR_INC', FEED_COLLECTOR_PATH . 'includes/' );

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );

require_once( dirname( __FILE__ ) . '/functions.php' );

add_action( 'plugins_loaded', function () {
	new Core();
} );

register_activation_hook( __FILE__, function () {
	$core = new Core();
	$core->activation();
} );

register_deactivation_hook( __FILE__, function () {
	$core = new Core();
	$core->deactivation();
} );
