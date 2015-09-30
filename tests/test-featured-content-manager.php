<?php

require_once( 'featured-content-manager.php' );

class Featured_Content_Tests extends WP_UnitTestCase {

	function setUp(){
		parent::setUp();
		$this->plugin = Featured_Content_Manager::get_instance();
	}

	/**
	 * Initial tests
	 * Here we test that our plugin exists and creates all
	 * the needed conditions for it to work.
	 */
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
		$query = $this->plugin->get_featured_content( 'Main Area' );
		$this->assertObjectHasAttribute( 'posts', $query );
	}

}