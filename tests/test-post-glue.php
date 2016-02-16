<?php

/**
 * @coversDefaultClass Post_Glue
 */
class Test_PostGlue extends WP_UnitTestCase {

	/**
	 * Setup a test method for the CASServerPlugin class.
	 */
	function setUp () {
		parent::setUp();
	}

	/**
	 * Finish a test method for the te class.
	 */
	function tearDown () {
		parent::tearDown();
	}
	/**
	 * The plugin should be installed and activated.
	 */
	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'post-glue/post-glue.php' ),
			'Plugin is active.' );
	}

	/**
	 * Test the activation callback.
	 * @covers ::activation
	 */
	function test_activation() {
		$post_ids   = $this->factory->post->create_many( 10 );
		$sticky_ids = array();

		foreach ( $post_ids as $post_id ) {
			if ( $post_id % 2 ) {
				stick_post( $post_id );
				delete_post_meta( $post_id, '_sticky' );
				$sticky_ids[] = $post_id;
			}
		}

		foreach ( $post_ids as $post_id ) {
			$actual   = get_post_meta( $post_id, '_sticky', true );
			
			$this->assertEquals( '', $actual,
		 		'The `_sticky` meta value is empty before activation.' );
		}

		Post_Glue::activation();

		foreach ( $post_ids as $post_id ) {
			$actual   = get_post_meta( $post_id, '_sticky', true );
			$expected = in_array( $post_id, $sticky_ids ) ? '1' : '';

			$this->assertEquals( $expected, $actual,
		 		'A `_sticky` meta value of 1 is set on sticky posts on activation.' );
		}
	}

	/**
	 * Test plugin action callbacks.
	 * @group action
	 * @covers ::plugins_loaded
	 */
	function test_plugins_loaded() {
		$actions = array(
			'plugins_loaded' => array(
				'callback' => array( 'Post_Glue', 'plugins_loaded' ),
				'priority' => 10,
			),
			'admin_init' => array(
				'callback' => array( 'Post_Glue', 'admin_init' ),
				'priority' => 10,
			),
			'update_option_sticky_posts' => array(
				'callback' => array( 'Post_Glue', 'update_option_sticky_posts' ),
				'priority' => 10,
			),
			'pre_get_posts' => array(
				'callback' => array( 'Post_Glue', 'pre_get_posts' ),
				'priority' => 10,
			),
		);

		$filters = array(
			'post_class' => array(
				'callback' => array( 'Post_Glue', 'post_class' ),
				'priority' => 10,
			),
		);

		foreach ( $actions as $name => $action ) {
			$this->assertEquals(
				$action['priority'], has_action( $name, $action['callback'] ),
			 	"Add '{$name}' action with priority {$action['priority']} on load."
			);
		}

		foreach ( $filters as $name => $filter ) {
			$this->assertEquals(
				$filter['priority'], has_filter( $name, $filter['callback'] ),
			 	"Add '{$name}' filter with priority {$action['priority']} on load."
			);
		}
	}

	/**
	 * Test admin initialization.
	 * @covers ::admin_init
	 */
	function test_admin_init() {
		global $wp_meta_boxes;

		$post_types = get_post_types( array( 'hierarchical' => false, 'public' => true ) );

		foreach ( $post_types as $post_type ) {
			$this->assertEquals(
				has_filter( 'views_edit-' . $post_type, array( 'Post_Glue', 'views_edit' ) ), false,
				"views_edit-{$post_type}' not hooked before 'admin_init'."
			);

			$this->assertTrue( empty( $wp_meta_boxes[ $post_type ]['side']['high']['post_glue_meta'] ),
			 	"Meta box for post type '{$post_type}' not registered before 'admin_init'." );
		}

		Post_Glue::admin_init();

		foreach ( $post_types as $post_type ) {
			$expected = $post_type === 'post' ? false : 10;

			$this->assertEquals(
				$expected, has_filter( 'views_edit-' . $post_type, array( 'Post_Glue', 'views_edit' ) ),
		 		"Add 'views_edit-{$post_type}' filter with priority {$expected} on 'admin_init'."
			);

			$this->assertEquals(
				(bool) $expected, ! empty( $wp_meta_boxes[ $post_type ]['side']['high']['post_glue_meta'] ),
			 	"Meta box for post type '{$post_type}' not registered before 'admin_init'." );
		}
	}

	/**
	 * Test the post_glue_post_types filter.
	 * @covers ::admin_init
	 */
	function test_post_glue_post_types() {
		$action = new MockAction();

		add_filter( 'post_glue_post_types', array( &$action, 'filter' ) );
		add_filter( 'post_glue_post_types', array( __CLASS__, 'post_glue_post_types' ) );

		Post_Glue::admin_init();

		$this->assertEquals(
			10, has_filter( 'views_edit-unicorns', array( 'Post_Glue', 'views_edit' ) ),
			"The 'post_glue_post_types' filter allows modifying the supported post type list."
		);

		$this->assertEquals( 1, $action->get_call_count(),
	 		"The 'post_glue_post_types' filter is called once on init." );
	}

	/**
	 * Filter the post types supported by Post Glue.
	 * @param  array $post_types Post types supported.
	 * @return array             Post types supported.
	 */
	static function post_glue_post_types( $post_types ) {
		$post_types[] = 'unicorns';
		return $post_types;
	}

	/**
	 * Test the sticky metabox on the post edit screen.
	 * @covers ::admin_meta_box
	 */
	function test_admin_meta_box() {
		global $post;

		$post_id = $this->factory->post->create();
		$post    = get_post( $post_id );

		unstick_post( $post_id );

		ob_start();
		Post_Glue::admin_meta_box();
		$meta_box = ob_get_clean();

		$this->assertNotRegExp( '/checked/', $meta_box,
			'Sticky metabox is unchecked.' );

		stick_post( $post_id );

		ob_start();
		Post_Glue::admin_meta_box();
		$meta_box = ob_get_clean();

		$this->assertRegExp( '/checked/', $meta_box,
			'Sticky metabox is checked.' );
	}

	/**
	 * Test the Sticky views filter on the admin post list.
	 * @covers ::views_edit
	 */
	function test_views_edit() {
		global $wp_query;

		$wp_query = new WP_Query( 'post_type=post' );
		$post_ids = $this->factory->post->create_many( 10 );
		$expected = 0;

		$views = Post_Glue::views_edit( array() );

		$this->assertTrue( empty( $views['sticky'] ),
	 		'No sticky posts view.' );

		foreach ( $post_ids as $post_id ) {
			if ( $post_id % 2 ) {
				stick_post( $post_id );
				$expected++;
			}
		}

		$views = Post_Glue::views_edit( array() );

		$this->assertRegExp( '/\(' . $expected . '\)/', $views['sticky'],
			"View link indicates {$expected} posts." );
	}

	/**
	 * Test the 'sticky_posts' option update action.
	 * @covers ::update_option_sticky_posts
	 * @covers ::stick_posts
	 * @covers ::unstick_posts
	 */
	function test_update_option_sticky_posts() {
		$post_ids   = $this->factory->post->create_many( 10 );
		$sticky_ids = array();

		foreach ( $post_ids as $post_id ) {
			if ( $post_id % 2 ) {
				$sticky_ids[] = $post_id;
			}
		}

		Post_Glue::update_option_sticky_posts( array(), $sticky_ids, 'sticky_posts' );

		foreach ( $post_ids as $post_id ) {
			$actual   = get_post_meta( $post_id, '_sticky', true );
			$expected = in_array( $post_id, $sticky_ids ) ? '1' : '';

			$this->assertEquals( $expected, $actual,
		 		'A `_sticky` meta value of 1 is set on sticky posts.' );
		}

		Post_Glue::update_option_sticky_posts( $sticky_ids, array(), 'sticky_posts' );

		foreach ( $post_ids as $post_id ) {
			$actual   = get_post_meta( $post_id, '_sticky', true );
			$expected = '';

			$this->assertEquals( $expected, $actual,
		 		'Removing sticky_posts option clears `_sticky` meta value.' );
		}
	}

	/**
	 * Test the query preprocessor.
	 * @covers ::pre_get_posts
	 */
	function test_pre_get_posts() {
		$wp_query = new WP_Query( 'post_type=post' );

		Post_Glue::pre_get_posts( $wp_query );

		$this->assertEquals( 1, $wp_query->get( 'ignore_sticky_posts' ),
	 		'Ignore stickiness when sorting posts by post meta.' );

		$this->assertNotEmpty( $wp_query->get( 'meta_query' ),
			'Change the query instance to set post meta clause for sorting.' );

		$this->assertArrayHasKey( 'sticky_clause', $wp_query->get( 'orderby' ),
			'Change the query instance to sort by post meta.' );

		$this->assertArrayHasKey( 'date', $wp_query->get( 'orderby' ),
			'Change the query instance to sort by date as a secondary criterium.' );

		$wp_query = new WP_Query( 'p=1' );

		Post_Glue::pre_get_posts( $wp_query );

		$this->assertEmpty( $wp_query->get( 'orderby' ),
			'Do not change the query instance when getting a single post.' );

		$wp_query = new WP_Query( array( 'post__in' => array( 1, 2, 3 ) ) );

		Post_Glue::pre_get_posts( $wp_query );

		$this->assertEmpty( $wp_query->get( 'orderby' ),
			'Do not change the query instance when querying specific posts.' );

		$wp_query = new WP_Query( 'post_type=post&ignore_sticky_posts=1' );

		Post_Glue::pre_get_posts( $wp_query );

		$this->assertEmpty( $wp_query->get( 'orderby' ),
			'Do not change the query instance when ignoring sticky posts.' );
	}

	/**
	 * Test the post_class filter callback.
	 * @covers ::pre_get_posts
	 */
	function test_post_class() {
		global $wp_query;

		$post_id  = $this->factory->post->create();
		$wp_query = new WP_Query( 'post_type=post' );

		$classes = Post_Glue::post_class( array(), '', $post_id );

		$this->assertEmpty( $classes,
	 		"Regular post in loop is given a 'sticky' class." );

		stick_post( $post_id );

		$classes = Post_Glue::post_class( array(), '', $post_id );

		$this->assertContains( 'sticky', $classes,
	 		"Sticky post in loop is given a 'sticky' class." );
	}

}
