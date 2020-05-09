<?php

namespace Feed_Collector;

class core {

	public function __construct() {
		$this->run();
	}

	public function run() {
		$this->set_locale();
		$this->load_modules();
	}

	public function set_locale() {
		load_plugin_textdomain(
			FEED_COLLECTOR_TEXT_DOMAIN,
			FALSE,
			plugin_basename( FEED_COLLECTOR_PATH ) . '/languages/'
		);
	}

	public function load_modules() {
		if ( is_admin() ) {
			new Admin();
		}
		new PostFeedChannel();
		new PostFeedItem();
		new Schedule();
	}

	public function activation() {
		$schedule = new Schedule();
		$schedule->update_cron_schedule();
		flush_rewrite_rules();
	}

	public function deactivation() {
		$schedule = new Schedule();
		$schedule->clear_cron_schedule();
	}
}
