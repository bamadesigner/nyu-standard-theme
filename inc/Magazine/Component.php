<?php
/**
 * WP_Rig\WP_Rig\Magazine\Component class
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig\Magazine;

use WP_Rig\WP_Rig\Component_Interface;
use WP_Rig\WP_Rig\Templating_Component_Interface;
use WP_Post;
use WP_Query;
use WP_Customize_Manager;

/**
 * Class for managing Magazine support.
 */
class Component implements Component_Interface, Templating_Component_Interface {

	const FP_MAG_POST_TYPE = 'post';

	const FP_MAG_SECTION = 'front_page_magazine_settings';

	const FP_MAG_NAME = 'show_front_page_magazine';
	const FP_MAG_DEFAULT = false;

	const FP_MAG_POST_DATE = 'front_page_magazine_post_date';
	const FP_MAG_POST_DATE_DEFAULT_VALUE = true;

	const FP_MAG_FEATURED_NAME = 'front_page_magazine_featured';

	const FP_MAG_FEATURED_MB_NAME = 'wp_rig_magazine_featured';
	const FP_MAG_FEATURED_MB_NONCE = 'wp_rig_magazine_featured_nonce';
	const FP_MAG_FEATURED_MB_NONCE_ACTION = 'wp_rig_magazine_featured_action';

	/**
	 * Holds the index for the magazine layout.
	 *
	 * @var int
	 */
	private $index = 0;

	/**
	 * Is true if set to use magazine layout.
	 *
	 * @var bool
	 */
	private $use_magazine_layout;

	/**
	 * Holds the setting for magazine display post date.
	 *
	 * @var bool
	 */
	private $display_post_date;

	/**
	 * Holds known magazine post IDs.
	 *
	 * @var array
	 */
	private $magazine_post_ids;

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'magazine';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {

		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		add_filter( 'body_class', array( $this, 'filter_body_classes' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_meta_box_magazine_options' ), 10, 2 );

		add_action( 'customize_register', array( $this, 'action_customize_register' ) );

		add_action( 'pre_get_posts', array( $this, 'modify_pre_get_posts' ) );
		add_filter( 'posts_clauses', array( $this, 'filter_posts_clauses' ), 100, 2 );
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `wp_rig()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags() : array {
		return array(
			'use_magazine_layout' => array( $this, 'use_magazine_layout' ),
			'display_magazine'    => array( $this, 'display_magazine' ),
			'magazine_display_post_date' => array( $this, 'magazine_display_post_date' ),
		);
	}

	/**
	 * Determines whether we want to display the magazine layout.
	 *
	 * @return bool Whether we want to display the magazine layout.
	 */
	public function use_magazine_layout() : bool {
		if ( isset( $this->use_magazine_layout ) ) {
			return $this->use_magazine_layout;
		}
		$this->use_magazine_layout = (bool) get_theme_mod( self::FP_MAG_NAME, self::FP_MAG_DEFAULT );
		return $this->use_magazine_layout;
	}

	/**
	 * Registers the image size for the magazine layout.
	 */
	public function add_image_sizes() {
		add_image_size( 'wp-rig-magazine', 600, 600, true );
	}

	/**
	 * Returns true if magazine display is set to show the post date.
	 *
	 * @return bool
	 */
	public function magazine_display_post_date() : bool {
		if ( isset( $this->display_post_date ) ) {
			return $this->display_post_date;
		}

		$this->display_post_date = (bool) get_theme_mod( self::FP_MAG_POST_DATE, self::FP_MAG_POST_DATE_DEFAULT_VALUE );

		return $this->display_post_date;
	}

	/**
	 * Adds a 'has-magazine' class to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 * @return array Filtered body classes.
	 */
	public function filter_body_classes( array $classes ) : array {
		if ( $this->use_magazine_layout() ) {
			$classes[] = 'has-magazine';
		}
		return $classes;
	}

	/**
	 * Adds a setting and control for using the magazine layout.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	public function action_customize_register( WP_Customize_Manager $wp_customize ) {

		$wp_customize->add_section(
			self::FP_MAG_SECTION,
			array(
				'title'    => __( '"Magazine" Settings', 'wp-rig' ),
				'priority' => 121,
			)
		);

		$wp_customize->add_setting(
			self::FP_MAG_NAME,
			array(
				'default'    => self::FP_MAG_DEFAULT,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FP_MAG_NAME,
			array(
				'label'   => __( 'Display "Magazine" on homepage', 'wp-rig' ),
				'section' => self::FP_MAG_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'Do you want to display the "Magazine" layout of recent posts on your homepage?', 'wp-rig' ),
			)
		);

		$wp_customize->add_setting(
			self::FP_MAG_POST_DATE,
			array(
				'default'    => self::FP_MAG_POST_DATE_DEFAULT_VALUE,
				'capability' => 'manage_options',
				'type'       => 'theme_mod',
				'sanitize_callback' => function ( $checked ) : bool {
					return ( ( isset( $checked ) && true == $checked ) ? true : false );
				},
			)
		);

		$wp_customize->add_control(
			self::FP_MAG_POST_DATE,
			array(
				'label'   => __( 'Display post date', 'wp-rig' ),
				'section' => self::FP_MAG_SECTION,
				'type'    => 'checkbox',
				'description' => __( 'Do you want to the show the post date in the magazine layout?', 'wp-rig' ),
			)
		);
	}

	/**
	 * Prints the markup for the magazine.
	 *
	 * @TODO:
	 *  - Don't run if magazine isnt enabled
	 *  - Setup magazine query
	 *
	 * @return  bool - true if magazine is printed.
	 */
	public function display_magazine() {

		if ( ! $this->use_magazine_layout() ) {
			return false;
		}

		$magazine_query = array(
			'post_type' => self::FP_MAG_POST_TYPE,
			'posts_per_page' => 4,
			'is_magazine' => true,
		);

		$magazine_post_ids = $this->get_magazine_post_ids();

		if ( ! empty( $magazine_post_ids ) ) {
			$magazine_query['post__in'] = $this->get_magazine_post_ids();
		}

		$magazines = new WP_Query( $magazine_query );

		if ( ! $magazines->have_posts() ) {
			return;
		}

		$this->index = 0;

		?>
		<aside class="site-magazine" aria-label="<?php esc_attr_e( 'Featured posts', 'wp-rig' ); ?>">
			<?php

			while ( $magazines->have_posts() ) {
				$magazines->the_post();

				get_template_part( 'template-parts/content/entry', 'magazine' );

				$this->index++;
			}

			?>
		</aside>
		<?php

		wp_reset_postdata();

		return true;
	}

	/**
	 * Returns true if post is a featured magazine post.
	 *
	 * @param WP_Post $post the post object.
	 *
	 * @return string
	 */
	public function is_magazine_featured_post( WP_Post $post ) : string {
		return (bool) ( self::FP_MAG_POST_TYPE == $post->post_type && (bool) $post->__get( self::FP_MAG_FEATURED_NAME ) );
	}

	/**
	 * Adds the meta boxes for our magazine options.
	 *
	 * @param string  $post_type The post type.
	 * @param WP_Post $post The post object.
	 */
	public function add_meta_boxes( string $post_type, WP_Post $post ) {

		if ( self::FP_MAG_POST_TYPE != $post_type ) {
			return;
		}

		if ( $this->use_magazine_layout() ) {

			add_meta_box(
				'wp-rig-magazine',
				__( 'Magazine Options', 'wp-rig' ),
				array(
					$this,
					'print_meta_box_magazine_options',
				),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Prints the meta box for our Magazine options.
	 *
	 * @param WP_Post $post The post object.
	 * @param array   $metabox The meta box information.
	 */
	public function print_meta_box_magazine_options( WP_Post $post, array $metabox ) {

		if ( self::FP_MAG_POST_TYPE != $post->post_type ) {
			return;
		}

		// Ensures security when we save the post meta.
		wp_nonce_field( self::FP_MAG_FEATURED_MB_NONCE_ACTION, self::FP_MAG_FEATURED_MB_NONCE );

		$featured = $this->is_magazine_featured_post( $post );

		?>
		<p><?php _e( 'The "Magazine" layout is used on the home page.', 'wp-rig' ); //phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction ?></p>
		<label for="wp-rig-post-featured" class="components-base-control__label" style="display:block;">
			<input id="wp-rig-post-featured" type="checkbox" name="<?php echo esc_attr( self::FP_MAG_FEATURED_MB_NAME ); ?>" class="components-select-control__input"<?php checked( $featured ); ?>> <?php esc_html_e( 'Feature this post in the magazine layout', 'wp-rig' ); ?>
		</label>
		<?php
	}

	/**
	 * Manages saving the magazine options meta box.
	 *
	 * @TODO:
	 * - Check if user has permissions to save data?
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post The post object.
	 */
	public function save_meta_box_magazine_options( $post_id, $post ) {

		if ( self::FP_MAG_POST_TYPE != $post->post_type ) {
			return;
		}

		if ( empty( $_POST[ self::FP_MAG_FEATURED_MB_NONCE ] ) ) {
			return;
		}

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::FP_MAG_FEATURED_MB_NONCE ] ) ), self::FP_MAG_FEATURED_MB_NONCE_ACTION ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$featured = ! empty( $_POST[ self::FP_MAG_FEATURED_MB_NAME ] );

		update_post_meta( $post_id, self::FP_MAG_FEATURED_NAME, $featured );

	}

	/**
	 * Store known magazine featured post IDs.
	 *
	 * @param array $post_ids post IDs.
	 *
	 * @return void;
	 */
	private function set_magazine_post_ids( $post_ids ) {
		$this->magazine_post_ids = $post_ids;
	}

	/**
	 * Get known magazine featured post IDs.
	 *
	 * @return array - post IDs
	 */
	private function get_magazine_post_ids() {
		return $this->magazine_post_ids;
	}

	/**
	 * If we're displaying the magazine layout, modify the main
	 * query so it doesn't show posts displayed in the "magazine".
	 *
	 * @param WP_Query $query The WP_Query instance (passed by reference).
	 *
	 * @return void
	 */
	public function modify_pre_get_posts( $query ) {

		if ( ! $query->is_home() ) {
			return;
		}

		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( ! $this->use_magazine_layout() ) {
			return;
		}

		$magazine_posts = get_posts(
			array(
				'post_type' => self::FP_MAG_POST_TYPE,
				'posts_per_page' => 4,
				'is_magazine' => true,
				'suppress_filters' => false,
			)
		);

		if ( empty( $magazine_posts ) ) {
			return;
		}

		$magazine_post_ids = wp_list_pluck( $magazine_posts, 'ID' );

		if ( empty( $magazine_post_ids ) ) {
			return;
		}

		$this->set_magazine_post_ids( $magazine_post_ids );

		// Make sure magazine posts aren't in main query.
		$query->set( 'post__not_in', $magazine_post_ids );

	}

	/**
	 * If querying for the magazine layout, we adjust the query
	 * to orderby "if the post is featured".
	 *
	 * @param string[] $clauses Associative array of the clauses for the query.
	 * @param WP_Query $query    The WP_Query instance (passed by reference).
	 *
	 * @return array
	 */
	public function filter_posts_clauses( $clauses, $query ) {
		global $wpdb;

		// Only for the magazine query.
		if ( ! $query->get( 'is_magazine' ) ) {
			return $clauses;
		}

		if ( ! $this->use_magazine_layout() ) {
			return $clauses;
		}

		$order = $query->get( 'order' );

		if ( ! in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
			$order = 'DESC';
		}

		// Join to get magazine featured.
		$clauses['join'] .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} fp_mag_featured ON fp_mag_featured.post_id = {$wpdb->posts}.ID AND fp_mag_featured.meta_key = %s", self::FP_MAG_FEATURED_NAME );

		// Order by featured posts first.
		$clauses['orderby'] = "IF ( fp_mag_featured.meta_value IS NOT NULL AND fp_mag_featured.meta_value != '', 1, 0 ) {$order}, {$wpdb->posts}.post_date {$order}";

		return $clauses;
	}
}
