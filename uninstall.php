<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Featured_Content
 * @author    Jesper Nilsson <jesper@klandestino.se>
 * @license   GPL-2.0+
 * @link      https://github.com/redundans
 * @copyright 2014 Jesper Nilsson
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	// Trash and delete all featured items and featured areas
	$featured_areas = get_terms( Featured_Content_Manager::TAXONOMY, array( 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC' ) );
	foreach ($featured_areas as $featured_area) :
		Featured_Content_Manager::trash_posts( array( 'draft', 'publish' ), $featured_area->term_id );

		// Delete the featured area
		wp_delete_term( $featured_area->term_id, Featured_Content_Manager::TAXONOMY );
	endforeach;

	exit;
}