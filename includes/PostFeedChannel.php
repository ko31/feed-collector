<?php

namespace Feed_Collector;

class PostFeedChannel {

	private $post_type = 'fc-feed-channel';

	private $taxonomy = 'fc_feed_channel_cat';

	public function __construct() {
		$this->run();
	}

	public function run() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}

	public function init() {
		$args = [
			'label'        => __( 'Feed Channel', 'feed-collector' ),
			'public'       => TRUE,
			'supports'     => [ 'title', 'thumbnail' ],
			'has_archive'  => TRUE,
			'menu_icon'    => 'dashicons-rss',
			'show_in_rest' => TRUE,
		];

		/**
		 * Filter the post type arguments of feed channel
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_feed_channel_register_post_type_args', $args );

		register_post_type( $this->post_type, $args );

		$args = [
			'label'        => __( 'Feed Category', 'feed-collector' ),
			'hierarchical' => TRUE,
			'show_in_rest' => TRUE,
		];

		/**
		 * Filter the taxonomy arguments of feed channel
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'fc_category_register_taxonomy_args', $args );

		register_taxonomy( $this->taxonomy, 'fc-feed-channel', $args );
	}

	public function add_meta_boxes() {
		add_meta_box(
			$this->post_type . '_meta_box',
			__( 'Feed Channel Details', 'feed-collector' ),
			[ $this, 'render_meta_box_callback' ],
			$this->post_type
		);
	}

	/**
	 * Render meta box.
	 *
	 * @param $post
	 */
	function render_meta_box_callback( $post ) {

		wp_nonce_field( 'feed_collector_meta_box', 'feed_collector_meta_box_nonce' );

		$fields = $this->get_meta_fields();

		foreach ( $fields as $field ) {
			$_value = get_post_meta( $post->ID, $field['id'], TRUE );
			?>
			<table class="form-table fc-form-table">
				<tbody>
				<tr>
					<th scope="row">
						<label
							for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
					</th>
					<td>
						<input type="<?php echo esc_attr( $field['type'] ); ?>"
						       id="<?php echo esc_attr( $field['id'] ); ?>"
						       name="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $_value ); ?>"
						       class="fc-input-text <?php echo esc_attr( $field['class'] ); ?>"
						       placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"/>
						<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
					</td>
				</tr>
				</tbody>
			</table>
			<?php
		}
	}

	/**
	 * Save meta data when the post is saved.
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed
	 */
	public function save_post( $post_id, $post ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['feed_collector_meta_box_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		$nonce = $_POST['feed_collector_meta_box_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'feed_collector_meta_box' ) ) {
			return $post_id;
		}

		// Do nothing if running an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		// Update the meta fields.
		$fields = $this->get_meta_fields();
		foreach ( $fields as $field ) {
			$_name  = $field['id'];
			$_value = $_POST[ $_name ] ?: '';
			update_post_meta( $post_id, $field['id'], $_value );
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
}
