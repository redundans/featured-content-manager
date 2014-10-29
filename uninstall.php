<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Featured_Content_Manager
 * @author    Jesper Nilsson <jesper@klandestino.se>
 * @license   GPL-2.0+
 * @link      https://github.com/redundans
 * @copyright 2014 Jesper Nilsson
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}