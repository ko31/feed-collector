<?php

namespace Feed_Collector;

class Schedule {

	private $options;

	private $option_name = 'feed-collector';

	public function __construct() {
		$this->run();
	}

	public function run() {
		$this->options = get_option( $this->option_name );
		add_filter( 'cron_schedules', [ $this, 'cron_schedules' ] );
		add_action( 'fc_fetch_feed', [ $this, 'fc_fetch_feed' ] );
	}

	/**
	 * Filter cron schedule.
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function cron_schedules( $schedules ) {
		$cron_interval = $this->get_cron_interval();

		$schedules['fc_interval'] = [
			'interval' => $cron_interval * HOUR_IN_SECONDS,
			'display'  => sprintf( __( 'Every %d hours', 'feed-collector' ), $cron_interval )
		];

		return $schedules;
	}

	/**
	 * Update cron schedule event.
	 */
	public function update_cron_schedule() {
		$this->clear_cron_schedule();
		wp_schedule_event( current_time( 'timestamp', 1 ), 'fc_interval', 'fc_fetch_feed' );
	}

	/**
	 * Clear cron schedule event.
	 */
	public function clear_cron_schedule() {
		if ( wp_next_scheduled( 'fc_fetch_feed' ) ) {
			wp_clear_scheduled_hook( 'fc_fetch_feed' );
		}
	}

	/**
	 * Run cron schedulee event.
	 */
	public function fc_fetch_feed() {

		// TODO:
		// Fetch feed items.

	}

	/**
	 * Get cron interval.
	 */
	public function get_cron_interval() {
		if ( ! $cron_interval = $this->options['cron_interval'] ) {
			$cron_interval = fc_default_cron_interval();
		}

		return $cron_interval;
	}
}
