<?php

require_once( 'featured-content.php' );

class Featured_Content_Tests extends WP_UnitTestCase {

	function setUp(){
		parent::setUp();
		$this->plugin = new Featured_Content;
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

	function test_current_theme_supports_featured_content() {
		$this->assertTrue( current_theme_supports( 'featured-content' ) );
	}


	/**
	 * Conditional tests
	 * Here we test that our plugin does what we want it to do
	 * in respect to different conditions.
	 */
	function test_get_featured_content_does_return_object() {
		$query = $this->plugin->get_featured_content('2');
		$this->assertObjectHasAttribute( 'posts', $query );
	}
	
}