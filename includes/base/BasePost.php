<?php

namespace Feed_Collector\base;

/**
 * Class BasePost
 * @package Feed_Collector\base
 */
abstract class BasePost {

	private $prefix;

	/**
	 * BasePost constructor.
	 */
	public function __construct() {
		$this->prefix = FEED_COLLECTOR_TEXT_DOMAIN;
	}

	/**
	 * Render meta box.
	 *
	 * @param $post
	 */
	public function render_meta_box_callback( $post ) {

		wp_nonce_field( $this->prefix . '_meta_box', $this->prefix . '_meta_box_nonce' );

		$fields = $this->get_meta_fields();

		?>
		<table class="form-table fc-form-table">
			<tbody>
			<?php
			foreach ( $fields as $field ) {
				$_value = get_post_meta( $post->ID, $field['id'], TRUE );
				?>
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
				<?php
			}
			?>
			</tbody>
		</table>
		<?php
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
		if ( ! isset( $_POST[ $this->prefix . '_meta_box_nonce' ] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		$nonce = $_POST[ $this->prefix . '_meta_box_nonce' ];
		if ( ! wp_verify_nonce( $nonce, $this->prefix . '_meta_box' ) ) {
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
	 * @return array
	 */
	public function get_meta_fields() {
		return [];
	}
}
