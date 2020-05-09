<?php
/**
 * Runs on uninstall
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

delete_option( 'feed-collector-setting' );
