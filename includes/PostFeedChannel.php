<?php

namespace GS\Feed_Collector;

use GS\Feed_Collector\base\BasePost;

/**
 * Class PostFeedChannel
 * @package Feed_Collector
 */
class PostFeedChannel extends BasePost {

	/**
	 * PostFeedChannel constructor.
	 */
	public function __construct() {
		$this->run();
	}

	/**
	 * Run.
	 */
	public function run() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'add_meta_boxes_fc-feed-channel', [ $this, 'add_meta_boxes' ] );
		add_filter( 'manage_fc-feed-channel_posts_columns', [ $this, 'manage_posts_columns' ] );
		add_action( 'manage_fc-feed-channel_posts_custom_column', [ $this, 'manage_posts_custom_column' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}

	/**
	 * Fires when init action runs.
	 */
	public function init() {
		$args = [
			'labels'       => [
				'name'      => __( 'Feed Channels', 'feed-collector' ),
				'all_items' => __( 'Feed Channels', 'feed-collector' ),
				'menu_name' => __( 'Feed Collector', 'feed-collector' ),
			],
			'public'       => true,
			'supports'     => [ 'title', 'thumbnail' ],
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-rss',
			'show_in_rest' => true,
		];

		/**
		 * Filter the post type arguments of feed channel
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_feed_channel_register_post_type_args', $args );

		register_post_type( 'fc-feed-channel', $args );

		$args = [
			'label'             => __( 'Feed Channel Category', 'feed-collector' ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
		];

		/**
		 * Filter the taxonomy arguments of feed channel
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_feed_channel_category_register_taxonomy_args', $args );

		register_taxonomy( 'fc-feed-channel-cat', 'fc-feed-channel', $args );
	}

	/**
	 * Fires when add_meta_boxes action runs.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'fc-feed-channel_meta_box',
			__( 'Feed Channel Details', 'feed-collector' ),
			[ $this, 'render_meta_box_callback' ],
			'fc-feed-channel'
		);
	}

	/**
	 * Fires when manage_posts_columns action of custom post type runs.
	 */
	public function manage_posts_columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $column_name => $column_display_name ) {
			if ( $column_name === 'date' ) {
				$new_columns['items'] = __( 'Feed items', 'feed-collector' );
			}
			$new_columns[ $column_name ] = $column_display_name;
		}

		return $new_columns;
	}

	/**
	 * Fires when manage_posts_custom_column action of custom post type runs.
	 */
	public function manage_posts_custom_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'items' :
				$args  = [
					'post_type'      => 'fc-feed-item',
					'posts_per_page' => - 1,
					'meta_query'     => [
						[
							'key'   => '_fc_feed_channel_id',
							'value' => $post_id,
						],
					],
				];
				$items = get_posts( $args );
				echo sprintf( '<a href="%s">%d %s</a>', admin_url( 'edit.php?post_type=fc-feed-item&_fc_feed_channel_id=' . $post_id ), count( $items ), __( 'counts', 'feed-collector' ) );
				break;
			default:
				break;
		}
	}

	/**
	 * Get meta fields.
	 *
	 * @return mixed|void
	 */
	public function get_meta_fields() {
		$fields = [];

		$fields['url'] = [
			'label'       => __( 'Feed URL', 'feed-collector' ),
			'id'          => '_fc_channel_url',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'https://', 'feed-collector' ),
			'description' => __( 'Enter the URL of the RSS feed.', 'feed-collector' ),
		];

		$fields['site_url'] = [
			'label'       => __( 'Site URL', 'feed-collector' ),
			'id'          => '_fc_channel_site_url',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'https://', 'feed-collector' ),
		];

		$fields['description'] = [
			'label' => __( 'Description', 'feed-collector' ),
			'id'    => '_fc_channel_description',
			'type'  => 'text',
			'class' => 'large-text',
		];

		$fields['limit'] = [
			'label'       => __( 'Limit', 'feed-collector' ),
			'id'          => '_fc_channel_limit',
			'type'        => 'number',
			'class'       => 'small-text',
			'placeholder' => 20,
			'description' => __( 'Set this if you want to change the maximum number of items you can acquire at one time. Default is <code>20</code>.', 'feed-collector' ),
		];

		$fields['included_keywords'] = [
			'label'       => __( 'Keywords to include', 'feed-collector' ),
			'id'          => '_fc_channel_included_keywords',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'Input excluded keywords', 'feed-collector' ),
			'description' => __( 'Only items that contain this keyword in the title will be retrieved. If you have more than one keyword, please enter them separated by commas.', 'feed-collector' ),
		];

		$fields['excluded_keywords'] = [
			'label'       => __( 'Keywords to exclude', 'feed-collector' ),
			'id'          => '_fc_channel_excluded_keywords',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'Input keywords to exclude', 'feed-collector' ),
			'description' => __( 'If the title of the item contains this keyword, it will be excluded. If you have more than one keyword, please enter them separated by commas.', 'feed-collector' ),
		];

		/**
		 * Filter the meta fields.
		 *
		 * @param array $args
		 */
		return apply_filters( 'fc_feed_channel_get_meta_fields', $fields );
	}
}
