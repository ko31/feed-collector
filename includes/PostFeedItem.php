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

		/**
		 * Filter the post type name of feed channel
		 *
		 * @param string $post_type
		 */
		$this->post_type = apply_filters( 'fc_feed_item_post_type_name', $this->post_type );

		$this->run();
	}

	/**
	 * Run.
	 */
	public function run() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_boxes' ] );
		add_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this, 'manage_posts_columns' ] );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [
			$this,
			'manage_posts_custom_column'
		], 10, 2 );
		add_action( 'restrict_manage_posts', [ $this, 'restrict_manage_posts' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}

	/**
	 * Fires when init action runs.
	 */
	public function init() {
		$args = [
			'label'        => __( 'Feed Items', 'feed-collector' ),
			'public'       => true,
			'supports'     => [ 'title' ],
			'has_archive'  => true,
			'menu_icon'    => 'dashicons-rss',
			'show_in_rest' => true,
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
	 * Fires when manage_posts_columns action of custom post type runs.
	 */
	public function manage_posts_columns( $columns ) {
		$new_columns = [];

		foreach ( $columns as $column_name => $column_display_name ) {
			if ( $column_name === 'date' ) {
				$new_columns['permalink'] = __( 'Permalink', 'feed-collector' );
				$new_columns['channel']   = __( 'Channel', 'feed-collector' );
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
			case 'permalink' :
				$permalink = get_post_meta( $post_id, '_fc_item_permalink', true );
				echo sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $permalink ), esc_html( $permalink ) );
				break;
			case 'channel':
				$feed_channel_id = get_post_meta( $post_id, '_fc_feed_channel_id', true );
				echo sprintf( '<a href="%s">%s</a>', get_edit_post_link( $feed_channel_id ), get_the_title( $feed_channel_id ) );
				break;
			default:
				break;
		}
	}

	/**
	 * Fires when restrict_manage_posts action runs.
	 *
	 * @param $post_type
	 */
	public function restrict_manage_posts( $post_type ) {
		if ( $post_type !== $this->post_type ) {
			return;
		}

		$selected_feed_id = isset( $_GET['_fc_feed_channel_id'] ) ? $_GET['_fc_feed_channel_id'] : '';

		$post_feed_channel = new PostFeedChannel();

		$args  = [
			'post_type'      => $post_feed_channel->post_type,
			'posts_per_page' => - 1,
		];
		$feeds = get_posts( $args );
		?>
		<select id="_fc_feed_channel_id" name="_fc_feed_channel_id">
			<option value=""><?php _e( 'All channels', 'feed-collector' ); ?></option>
			<?php
			foreach ( $feeds as $feed ) {
				$selected = selected( $selected_feed_id, $feed->ID, false );
				?>
				<option
					value="<?php echo esc_attr( $feed->ID ); ?>" <?php echo $selected; ?>><?php echo esc_html( $feed->post_title ); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}

	/**
	 * Fires when pre_get_posts action runs.
	 *
	 * @param \WP_Query $query
	 */
	public function pre_get_posts( $query ) {

		if ( is_admin() && $query->get( 'post_type' ) === $this->post_type && $query->is_main_query() ) {
			$args = $query->get( 'meta_query', [] );
			if ( ! empty( $_GET['_fc_feed_channel_id'] ) ) {
				$args[] = [
					'key'   => '_fc_feed_channel_id',
					'value' => $_GET['_fc_feed_channel_id'],
				];
			}
			$query->set( 'meta_query', $args );
		}
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
				return null;
				break;

		}
	}
}
