<?php

namespace GS\Feed_Collector;

/**
 * Class core
 * @package Feed_Collector
 */
class core {

	/**
	 * core constructor.
	 */
	public function __construct() {
		$this->run();
	}

	/**
	 * Run.
	 */
	public function run() {
		$this->set_locale();
		$this->load_modules();
	}

	/**
	 * Load translated strings.
	 */
	public function set_locale() {
		load_plugin_textdomain(
			FEED_COLLECTOR_TEXT_DOMAIN,
			false,
			plugin_basename( FEED_COLLECTOR_PATH ) . '/languages/'
		);
	}

	/**
	 * Load this plugin modules.
	 */
	public function load_modules() {
		if ( is_admin() ) {
			new Admin();
		}
		new PostFeedChannel();
		new PostFeedItem();
		new Schedule();
	}

	/**
	 * Activate plugin.
	 */
	public function activation() {
		$schedule = new Schedule();
		$schedule->update_cron_schedule();
		flush_rewrite_rules();
	}

	/**
	 * Deactivate plugin.
	 */
	public function deactivation() {
		$schedule = new Schedule();
		$schedule->clear_cron_schedule();
	}
}
