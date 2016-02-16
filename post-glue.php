<?php
/**
 * Plugin Name: Post Glue
 * Plugin URI: https://github.com/log-oscon/post-glue/
 * Description: Sticky posts for WordPress, improved.
 * Version: 1.0.0
 * Author: log.OSCON, Lda.
 * Author URI: https://log.pt/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: post-glue
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/log-oscon/post-glue
 * GitHub Branch: master
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Implements plugin functionality.
 */
class Post_Glue {

	/**
	 * Set sticky meta values on plugin activation.
	 */
	public static function activation() {
		self::stick_posts( get_option( 'sticky_posts', array() ) );
	}

	/**
	 * Initialize the plugin.
	 */
	public static function plugins_loaded() {
		$plugin_basename = plugin_basename( dirname( __FILE__ ) );

		load_plugin_textdomain( 'post-glue', false, $plugin_basename . '/languages' );

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'update_option_sticky_posts', array( __CLASS__, 'update_option_sticky_posts' ), 10, 3 );
		add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
		add_filter( 'post_class', array( __CLASS__, 'post_class' ), 10, 3 );
	}

	/**
	 * Initialize the admin-specific parts of the plugin.
	 *
	 * Registers a Sticky metabox for every non-hierarchical post type and
	 * adds a view filter to the post edit screen.
	 */
	public static function admin_init() {

		// Get all public, non-hierarchical post types:
		$post_types = get_post_types( array( 'hierarchical' => false, 'public' => true ) );

		// Bypass the core post type:
		$post_types = array_diff( $post_types, array( 'post' ) );

		/**
		 * Filter the list of post types that support stickiness.
		 *
		 * Defaults to the list of public, non-hierarchical post types.
		 *
		 * @param array $post_types Post types that support stickiness.
		 */
		$post_types = apply_filters( 'post_glue_post_types', $post_types );

		add_meta_box(
			'post_glue_meta',
			__( 'Post Glue', 'post-glue' ),
			array( __CLASS__, 'admin_meta_box' ),
			$post_types,
			'side',
			'high'
		);

		foreach( $post_types as $post_type ) {
			add_filter( 'views_edit-' . $post_type, array( __CLASS__, 'views_edit' ) );
		}
	}

	/**
	 * Render the sticky meta box.
	 */
	public static function admin_meta_box() {
		?>
		<label for="post-glue-sticky" class="selectit">
			<input id="post-glue-sticky" name="sticky" type="checkbox"
				value="sticky" <?php checked( is_sticky() ) ?>>
			<?php _e( 'Make this post sticky', 'post-glue' ) ?>
		</label>
		<?php
	}

	/**
	 * Add sticky post view to the post edit page in the admin.
	 *
	 * @param  array $views Admin post edit views.
	 * @return array        Filtered admin post edit views.
	 */
	public static function views_edit( $views ) {
		global $wp_query;

		$post_type    = $wp_query->get( 'post_type' );
		$sticky_posts = array();

		foreach( get_option( 'sticky_posts', array() ) as $post_id ) {
			if ( get_post_type( $post_id ) === $post_type ) {
				$sticky_posts[] = $post_id;
			}
		}

		$sticky_posts_count = count( $sticky_posts );

		if ( ! $sticky_posts_count ) {
			return $views;
		}

		$sticky_inner_html = sprintf(
			_nx(
				'Sticky <span class="count">(%s)</span>',
				'Sticky <span class="count">(%s)</span>',
				$sticky_posts_count,
				'sticky view link',
				'post-glue'
			),
			number_format_i18n( $sticky_posts_count )
		);

		$views['sticky'] = sprintf(
			'<a href="%sedit.php?post_type=%s&show_sticky=1">%s</a>',
			get_admin_url(),
			$post_type,
			$sticky_inner_html
		);

		return $views;
	}

	/**
	 * Saves post stickiness to the `_sticky` post meta key.
	 *
	 * @param  mixed  $old_value Previous option value.
	 * @param  mixed  $value     New option value.
	 * @param  string $option    Option name.
	 */
	public static function update_option_sticky_posts( $old_value, $value, $option ) {
		$added   = array_diff( $value, $old_value );
		$removed = array_diff( $old_value, $value );

		self::stick_posts( $added );
		self::unstick_posts( $removed );
	}

	/**
	 * Sort posts by stickiness.
	 *
	 * Changes queries to include sticky posts on the default sort order.
	 * Honours the `ignore_sticky_posts` query argument.
	 *
	 * The meta query added by this action translates to a `LEFT JOIN` where the
	 * `_sticky` meta key is checked for both existence and non-existence. The
	 * point is to force the WordPress SQL builder to perform a `CAST(meta_value
	 * AS SIGNED)` in the `ORDER BY` clause as a sort of poor man's `COALESCE()`.
	 *
	 * @param WP_Query $query The current query instance, passed by reference.
	 */
	public static function pre_get_posts( $query ) {

		// Don't alter admin queries:
		if ( is_admin() ) {
			return;
		}

		// Ignore sticky posts:
		if ( $query->get( 'ignore_sticky_posts' ) ) {
			return;
		}

		// Don't show stickies outside of home, post type or taxonomy archives:
		if ( ! $query->is_home() && ! $query->is_post_type_archive() && ! $query->is_tax() ) {
			return;
		}

		// Ignore when querying specific posts:
		if ( $query->get( 'post__in' ) ) {
			return;
		}

		// Ignore queries that already provide an order:
		if ( $query->get( 'orderby' ) ) {
			return;
		}

		// Ignore queries that already provide a meta query:
		if ( $query->get( 'meta_query' ) ) {
			return;
		}

		// Ignore core stickies now:
		$query->set( 'ignore_sticky_posts', 1 );

		$query->set( 'meta_query', array(
			array(
				'relation' => 'OR',
				array(
					'key'     => '_sticky',
					'type'    => 'BINARY',
					'compare' => 'EXISTS',
				),
				'sticky_clause' => array(
					'key'     => '_sticky',
					'type'    => 'BINARY',
					'compare' => 'NOT EXISTS',
				),
			),
		) );

		$query->set( 'orderby', array(
			'sticky_clause' => 'DESC',
			'date'          => 'DESC',
		) );
	}

	/**
	 * Add a `sticky` HTML class to posts.
	 *
	 * @param  array  $classes  An array of post classes.
	 * @param  array  $class    An array of additional classes added to the post.
	 * @param  int    $post_id  The post ID.
	 * @return array            Filtered class list.
	 */
	public static function post_class( $classes, $class, $post_id ) {
		if ( is_sticky( $post_id ) ) {
			if ( is_home() || is_post_type_archive() || is_tax() ) {
				$classes[] = 'sticky';
			}
		}

		return $classes;
	}

	/**
	 * Bulk update _sticky meta values for a group of post IDs.
	 *
	 * @param  array  $posts List of post IDs.
	 */
	private static function stick_posts( $posts ) {
		foreach ( $posts as $post_id ) {
			update_post_meta( $post_id, '_sticky', 1 );
		}
	}

	/**
	 * Bulk delete _sticky meta values for a group of post IDs.
	 *
	 * @param  array  $posts List of post IDs.
	 */
	private static function unstick_posts( $posts ) {
		foreach ( $posts as $post_id ) {
			delete_post_meta( $post_id, '_sticky' );
		}
	}

}

register_activation_hook( __FILE__, array( 'Post_Glue', 'activation' ) );

add_action( 'plugins_loaded', array( 'Post_Glue', 'plugins_loaded' ) );
