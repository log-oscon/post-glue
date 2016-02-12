<?php

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
	function test_plugin_activated () {
		$this->assertTrue( is_plugin_active( 'post-glue/post-glue.php' ),
			'Plugin is active.' );
	}

	/**
	 * Test plugin action callbacks.
	 * @group action
	 */
	function test_actions () {
		$this->markTestIncomplete();
	}

	/**
	 * Test plugin filter callbacks.
	 * @group filter
	 */
	function test_filters () {
		$this->markTestIncomplete();
	}

}
