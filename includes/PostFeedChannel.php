<?php

namespace Feed_Collector;

use Feed_Collector\base\BasePost;

/**
 * Class PostFeedChannel
 * @package Feed_Collector
 */
class PostFeedChannel extends BasePost {

	private $post_type = 'fc-feed-channel';

	private $taxonomy = 'fc_feed_channel_cat';

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
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_boxes' ] );
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

		register_post_type( $this->post_type, $args );

		$args = [
			'label'        => __( 'Feed Channel Category', 'feed-collector' ),
			'hierarchical' => true,
			'show_in_rest' => true,
			'rewrite'      => false,
		];

		/**
		 * Filter the taxonomy arguments of feed channel
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_category_register_taxonomy_args', $args );

		register_taxonomy( $this->taxonomy, 'fc-feed-channel', $args );
	}

	/**
	 * Fires when add_meta_boxes action runs.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			$this->post_type . '_meta_box',
			__( 'Feed Channel Details', 'feed-collector' ),
			[ $this, 'render_meta_box_callback' ],
			$this->post_type
		);
	}

	/**
	 * Get meta fields.
	 *
	 * @return mixed|void
	 */
	public function get_meta_fields() {
		$fields = [];

		$fields['url'] = [
			'label'       => __( 'URL', 'feed-collector' ),
			'id'          => '_fc_url',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'Input feed url', 'feed-collector' ),
		];

		$fields['limit'] = [
			'label'       => __( 'Limit', 'feed-collector' ),
			'id'          => '_fc_limit',
			'type'        => 'number',
			'class'       => 'small-text',
			'placeholder' => 20,
		];

		$fields['excluded_keywords'] = [
			'label'       => __( 'Excluded keywords', 'feed-collector' ),
			'id'          => '_fc_excluded_keywords',
			'type'        => 'text',
			'class'       => 'large-text',
			'placeholder' => __( 'Input excluded keywords', 'feed-collector' ),
			'description' => __( 'If you have more than one keyword, please enter them separated by commas.', 'feed-collector' ),
		];

		/**
		 * Filter the meta fields.
		 *
		 * @param array $args
		 */
		return apply_filters( 'fc_feed_channel_get_meta_fields', $fields );
	}

	/**
	 * Getter.
	 *
	 * @param $name
	 *
	 * @return string|null
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'post_type':
				return $this->post_type;
				break;
			case 'taxonomy':
				return $this->taxonomy;
				break;
			default:
				return null;
				break;

		}
	}
}
