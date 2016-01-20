<?php

require_once( 'featured-content-manager.php' );

class Featured_Content_Tests extends WP_UnitTestCase {

	function setUp(){
		parent::setUp();
		$this->plugin = Featured_Content_Manager::get_instance();
		\WP_Mock::setUp();
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}

	/**
	 * Initial tests
	 * Here we test that our plugin exists and creates all
	 * the needed conditions for it to work.
	 */
	function test_plugin_initialization() {
		$this->assertFalse( null === $this->plugin );
	}

	function test_if_featured_item_post_type_exists() {
		$this->assertTrue( post_type_exists( 'featured_item' ) );
	}

	function test_if_featured_area_texonomy_exists() {
		$this->assertTrue( taxonomy_exists( 'featured_area' ) );
	}


	/**
	 * Conditional tests
	 * Here we test that our plugin does what we want it to do
	 * in respect to different conditions.
	 */
	function test_get_featured_content_does_return_object() {
		// Arrange
		$area = new \stdClass();
		$area->term_id = 1;

		\WP_Mock::wpFunction( 'get_term_by', array(
			'times' => 1,
			'args' => array( 'name', 'Main Area', 'featured_area' ),
			'return' => $area,
		) );

		// Act
		$query = $this->plugin->get_featured_content( 'Main Area' );

		// Assert
		$this->assertObjectHasAttribute( 'posts', $query );
	}

	function test_get_plugin_slug_returns_slug() {
		$slug = $this->plugin->get_plugin_slug();
		$expected_slug = 'featured-content-manager';

		$this->assertEquals( $expected_slug, $slug);
	}
}