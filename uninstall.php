<?php
/**
 * Runs on Uninstall
 */

namespace Feed_Collector;

// If uninstall is not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

$core = new Core();
$core->deactivation();
