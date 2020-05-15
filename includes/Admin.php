<?php

namespace GS\Feed_Collector;

/**
 * Class Admin
 * @package Feed_Collector
 */
class Admin {

	private $options;

	private $option_name = 'feed-collector-setting';

	private $option_group = 'feed-collector-group';

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->run();
	}

	/**
	 * Run
	 */
	public function run() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'updated_option', [ $this, 'updated_option' ] );
	}

	/**
	 * Fires when admin_menu action runs.
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=fc-feed-channel',
			__( 'Feed Collector Settings', 'feed-collector' ),
			__( 'Settings', 'feed-collector' ),
			'manage_options',
			'feed-collector-settings',
			[ $this, 'display_settings' ]
		);

		// Hide the submenu "Add New"
		global $submenu;
		if ( isset( $submenu['edit.php?post_type=fc-feed-channel'][10] ) ) {
			unset( $submenu['edit.php?post_type=fc-feed-channel'][10] );
		}
	}

	/**
	 * Fires when admin_init action runs.
	 */
	public function admin_init() {
		register_setting(
			$this->option_group,
			$this->option_name
		);

		add_settings_section(
			'general_settings',
			__( 'General Settings', 'feed-collector' ),
			null,
			$this->option_group
		);

		add_settings_field(
			'cron_interval',
			__( 'Cron interval', 'feed-collector' ),
			[ $this, 'cron_interval_callback' ],
			$this->option_group,
			'general_settings'
		);

		add_settings_field(
			'feed_cache_time',
			__( 'Feed cache time', 'feed-collector' ),
			[ $this, 'feed_cache_time_callback' ],
			$this->option_group,
			'general_settings'
		);
	}

	/**
	 * Render cron_interval field.
	 */
	public function cron_interval_callback() {
		$cron_interval = isset( $this->options['cron_interval'] ) ? $this->options['cron_interval'] : '';
		?>
		<input name="<?php echo $this->option_name; ?>[cron_interval]" type="number" step="1" min="1" max="24"
		       id="cron_interval" value="<?php echo esc_attr( $cron_interval ); ?>"
		       placeholder="<?php echo esc_attr( fc_default_cron_interval() ); ?>"
		       class="small-text">
		<p class="description"><?php printf( __( 'Cron interval in hours. Default is <code>%s</code>.', 'feed-collector' ), fc_default_cron_interval() ); ?></p>
		<?php
		// Show next run time.
		$schedule = new Schedule();
		if ( $next_run = $schedule->next_scheduled_datetime() ) {
			?>
			<p class="description"><?php printf( __( 'The next run is at <code>%s</code>.', 'feed-collector' ), $next_run ); ?></p>
			<?php
		}
	}

	/**
	 * Render feed_cache_time field.
	 */
	public function feed_cache_time_callback() {
		$feed_cache_time = isset( $this->options['feed_cache_time'] ) ? $this->options['feed_cache_time'] : '';
		?>
		<input name="<?php echo $this->option_name; ?>[feed_cache_time]" type="number" step="1" min="60"
		       id="feed_cache_time" value="<?php echo esc_attr( $feed_cache_time ); ?>"
		       placeholder="43200"
		       class="small-text">
		<p class="description"><?php _e( 'lifetime of the feed cache in seconds. Default is <code>43200</code> seconds (12 hours).', 'feed-collector' ); ?></p>
		<?php
	}

	/**
	 * Render settings.
	 */
	public function display_settings() {
		$this->options = get_option( $this->option_name );
		?>
		<form action="options.php" method="post">
			<h1><?php _e( 'Feed Collector Settings', 'feed-collector' ); ?></h1>
			<?php
			settings_fields( $this->option_group );
			do_settings_sections( $this->option_group );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Fires when updated_option action runs.
	 *
	 * @param $option
	 */
	public static function updated_option( $option ) {
		if ( $option === $this->option_name ) {
			$schedule = new Schedule();
			$schedule->update_cron_schedule();
		}
	}
}
