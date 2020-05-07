<?php

namespace Feed_Collector;

class Admin {

	public function __construct() {
		$this->run();
	}

	public function run() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=fc-feed-channel',
			__( 'Feed Collector Settings', 'feed-collector' ),
			__( 'Settings', 'feed-collector' ),
			'manage_options', 'feed-collector-settings',
			[ $this, 'display_settings' ]
		);
	}

	public function admin_init() {
		register_setting(
			'feed-collector',
			'feed-collector'
		);

		add_settings_section(
			'general_settings',
			__( 'General Settings', 'feed-collector' ),
			NULL,
			'feed-collector'
		);
	}

	public function display_settings() {
		?>
		<h1><?php _e( 'Feed Collector Settings', 'feed-collector' ); ?></h1>
		<?php
		settings_fields( 'feed-collector' );
		do_settings_sections( 'feed-collector' );
		?>
		<?php
	}
}
