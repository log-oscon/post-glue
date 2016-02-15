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
		$this->markTestIncomplete();
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
				has_action( $name, $action['callback'] ), $action['priority'],
			 	"Add '{$name}' action with priority {$action['priority']} on load."
			);
		}

		foreach ( $filters as $name => $filter ) {
			$this->assertEquals(
				has_filter( $name, $filter['callback'] ), $filter['priority'],
			 	"Add '{$name}' filter with priority {$action['priority']} on load."
			);
		}
	}

	/**
	 * Test admin initialization.
	 * @covers ::admin_init
	 */
	function test_admin_init() {
		$post_types = get_post_types( array( 'hierarchical' => false, 'public' => true ) );

		foreach ( $post_types as $post_type ) {
			$this->assertEquals(
				has_filter( 'views_edit-' . $post_type, array( 'Post_Glue', 'views_edit' ) ), false,
				"views_edit-{$post_type}' not hooked before 'admin_init'."
			);
		}

		Post_Glue::admin_init();

		foreach ( $post_types as $post_type ) {
			$expected = $post_type === 'post' ? false : 10;

			$this->assertEquals(
				has_filter( 'views_edit-' . $post_type, array( 'Post_Glue', 'views_edit' ) ), $expected,
		 		"Add 'views_edit-{$post_type}' filter with priority {$expected} on 'admin_init'."
			);
		}

		// TODO: Test meta box.

		$this->markTestIncomplete();
	}

	/**
	 * Test the post_glue_post_types filter.
	 * @covers ::admin_init
	 */
	function test_post_glue_post_types() {
		add_filter( 'post_glue_post_types', array( __CLASS__, 'post_glue_post_types' ) );

		Post_Glue::admin_init();

		$this->assertEquals(
			has_filter( 'views_edit-unicorns', array( 'Post_Glue', 'views_edit' ) ), 10,
			"The 'post_glue_post_types' filter allows modifying the supported post type list."
		);
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
		$this->markTestIncomplete();
	}

	/**
	 * Test the Sticky views filter on the admin post list.
	 * @covers ::views_edit
	 */
	function test_views_edit() {
		$this->markTestIncomplete();
	}

	/**
	 * Test the 'sticky_posts' option update action.
	 * @covers ::update_option_sticky_posts
	 * @covers ::stick_posts
	 * @covers ::unstick_posts
	 */
	function test_update_option_sticky_posts() {
		$this->markTestIncomplete();
	}

	/**
	 * Test the query preprocessor.
	 * @covers ::pre_get_posts
	 */
	function test_pre_get_posts() {
		$this->markTestIncomplete();
	}

	/**
	 * Test the post_class filter callback.
	 * @covers ::pre_get_posts
	 */
	function test_post_class() {
		$this->markTestIncomplete();
	}

}
