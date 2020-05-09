<?php

namespace Feed_Collector;

class Fetch {

	public function __construct() {
		//
	}

	public function fetch_feeds() {
		$post_feed_channel = new PostFeedChannel();

		// Get feed channels
		$args  = [
			'post_type'     => $post_feed_channel->post_type,
			'post_per_page' => - 1,
		];
		$feeds = get_posts( $args );
		foreach ( $feeds as $feed ) {
			// Fetch feed items
			$this->fetch_items( $feed );
		}
	}

	/**
	 * Fetch feed items.
	 *
	 * @param \WP_Post_Type $feed feed channel
	 */
	public function fetch_items( $feed ) {
		$post_feed_item    = new PostFeedItem();

		// Get feed channel detail
		$feed_url = get_post_meta( $feed->ID, '_fc_url', TRUE );
		if ( ! $feed_limit = get_post_meta( $feed->ID, '_fc_limit', TRUE ) ) {
			$feed_limit = 20;
		}
		$feed_excluded_keywords = get_post_meta( $feed->ID, '_fc_excluded_keywords', TRUE );

		// Fetch feed items
		$rss = fetch_feed( $feed_url );
		if ( is_wp_error( $rss ) ) {
			return;
		}

		/**
		 * WP has a SimplePie error.
		 * "Warning: A non-numeric value encountered in /wordpress/wp-includes/SimplePie/Parse/Date.php on line 694"
		 * @link https://core.trac.wordpress.org/ticket/42515
		 * @link https://github.com/michaeluno/_fix-simplepie-errors
		 */
		$maxitems  = $rss->get_item_quantity( $feed_limit );
		$rss_items = $rss->get_items( 0, $maxitems );
		foreach ( $rss_items as $item ) {

			// TODO:すでに登録済みならスキップ
			//

			$post_date = $item->get_date( 'U' ) ? date_i18n( get_option( 'date_format' ), $item->get_date( 'U' ) ) : date_i18n( get_option( 'date_format' ) );

			$new_feed_item = [
				'post_type'    => $post_feed_item->post_type,
				'post_title'   => $item->get_title(),
				'post_content' => '',
				'post_status'  => 'publish',
				'post_date'    => $post_date,
			];

			/**
			 * Filters inserted post feed item
			 *
			 * @param array $new_feed_item
			 * @param \WP_Post_Type $feed
			 * @param mixed $item Simple Pie object
			 */
			$new_feed_item = apply_filters( 'fc_insert_post_feed_item', $new_feed_item, $feed, $item );

			if ( ! $post_id = wp_insert_post( $new_feed_item ) ) {
				continue;
			}

			if ($enclosure = $item->get_enclosure()){
				$enclosure = $enclosure->get_link();
			}

			$metas = [
				'_fc_feed_channel_id'  => $feed->ID,
				'_fc_item_published'   => $post_date,
				'_fc_item_updated'     => $item->get_updated_date( 'U' ) ? date_i18n( get_option( 'date_format' ), $item->get_updated_date( 'U' ) ) : $post_date,
				'_fc_item_title'       => $item->get_title(),
				'_fc_item_permalink'   => $item->get_permalink(),
				'_fc_item_enclosure'   => $enclosure,
				'_fc_item_description' => $item->get_description(),
			];

			/**
			 * Filters inserted post meta value
			 *
			 * @param array $metas
			 * @param \WP_Post_Type $feed
			 * @param mixed $item Simple Pie object
			 */
			$metas = apply_filters( 'fc_insert_post_metas', $metas, $feed, $item );

			foreach ( $metas as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}
	}
}