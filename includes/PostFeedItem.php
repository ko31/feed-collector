<?php

namespace Feed_Collector;

use Feed_Collector\base\BasePost;

/**
 * Class PostFeedItem
 * @package Feed_Collector
 */
class PostFeedItem extends BasePost {

	private $post_type = 'fc-feed-item';

	/**
	 * PostFeedItem constructor.
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
			'label'        => __( 'Feed Items', 'feed-collector' ),
			'public'       => TRUE,
			'supports'     => [ 'title' ],
			'has_archive'  => TRUE,
			'menu_icon'    => 'dashicons-rss',
			'show_in_rest' => TRUE,
			'show_in_menu' => 'edit.php?post_type=fc-feed-channel',
		];

		/**
		 * Filter the post type arguments of feed item
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_feed_item_register_post_type_args', $args );

		register_post_type( $this->post_type, $args );
	}

	/**
	 * Fires when add_meta_boxes action runs.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			$this->post_type . '_meta_box',
			__( 'Feed Item Details', 'feed-collector' ),
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

		$fields['feed_channel_id'] = [
			'label' => __( 'Feed channel id', 'feed-collector' ),
			'id'    => '_fc_feed_channel_id',
			'type'  => 'text',
			'class' => 'small-text',
		];

		$fields['published'] = [
			'label' => __( 'Published date', 'feed-collector' ),
			'id'    => '_fc_item_published',
			'type'  => 'text',
			'class' => 'regular-text',
		];

		$fields['updated'] = [
			'label' => __( 'Updated date', 'feed-collector' ),
			'id'    => '_fc_item_updated',
			'type'  => 'text',
			'class' => 'regular-text',
		];

		$fields['title'] = [
			'label' => __( 'Title', 'feed-collector' ),
			'id'    => '_fc_item_title',
			'type'  => 'text',
			'class' => 'large-text',
		];

		$fields['permalink'] = [
			'label' => __( 'Permalink', 'feed-collector' ),
			'id'    => '_fc_item_permalink',
			'type'  => 'text',
			'class' => 'large-text',
		];

		$fields['enclosure'] = [
			'label' => __( 'Enclosure', 'feed-collector' ),
			'id'    => '_fc_item_enclosure',
			'type'  => 'text',
			'class' => 'large-text',
		];

		$fields['description'] = [
			'label' => __( 'Description', 'feed-collector' ),
			'id'    => '_fc_item_description',
			'type'  => 'text',
			'class' => 'large-text',
		];

		/**
		 * Filter the meta fields.
		 *
		 * @param array $args
		 */
		return apply_filters( 'fc_feed_item_get_meta_fields', $fields );
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
			default:
				return NULL;
				break;

		}
	}
}
