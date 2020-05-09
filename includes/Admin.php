<?php

namespace Feed_Collector;

class Admin {

	private $options;

	private $option_name = 'feed-collector';

	private $option_group = 'feed-collector-group';

	public function __construct() {
		$this->run();
	}

	public function run() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'updated_option', [ $this, 'updated_option' ] );
	}

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=fc-feed-channel',
			__( 'Feed Collector Settings', 'feed-collector' ),
			__( 'Settings', 'feed-collector' ),
			'manage_options',
			'feed-collector-settings',
			[ $this, 'display_settings' ]
		);
	}

	public function admin_init() {
		register_setting(
			$this->option_group,
			$this->option_name
		);

		add_settings_section(
			'general_settings',
			__( 'General Settings', 'feed-collector' ),
			NULL,
			$this->option_group
		);

		add_settings_field(
			'cron_interval',
			__( 'Cron interval', 'feed-collector' ),
			[ $this, 'cron_interval_callback' ],
			$this->option_group,
			'general_settings'
		);
	}

	public function cron_interval_callback() {
		$cron_interval = isset( $this->options['cron_interval'] ) ? $this->options['cron_interval'] : '';
		?>
		<input name="<?php echo $this->option_name; ?>[cron_interval]" type="number" step="1" min="1" max="24"
		       id="cron_interval" value="<?php echo esc_attr( $cron_interval ); ?>"
		       placeholder="<?php echo esc_attr( fc_default_cron_interval() ); ?>"
		       class="small-text">
		<p class="description"><?php printf( __( 'Cron interval in hours. Default is <code>%s</code>.', 'feed-collector' ), fc_default_cron_interval() ); ?></p>
		<?php
	}

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
	 * Fires after the value of an option has been successfully updated.
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
