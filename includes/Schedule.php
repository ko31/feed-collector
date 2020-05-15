<?php

namespace GS\Feed_Collector;

/**
 * Class Schedule
 * @package Feed_Collector
 */
class Schedule {

	private $options;

	private $option_name = 'feed-collector-setting';

	/**
	 * Schedule constructor.
	 */
	public function __construct() {
		$this->run();
	}

	/**
	 * Run.
	 */
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
	 * Get next event datetime.
	 *
	 * @param string $datetime_format
	 */
	public function next_scheduled_datetime( $datetime_format = '' ) {
		if ( ! $timestamp = wp_next_scheduled( 'fc_fetch_feed' ) ) {
			return;
		}
		if ( ! $datetime_format ) {
			$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return get_date_from_gmt( date( $datetime_format, $timestamp ), $datetime_format );
	}

	/**
	 * Fires when cron schedulee event runs.
	 */
	public function fc_fetch_feed() {
		$fetch = new Fetch();
		$fetch->fetch_feeds();
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
