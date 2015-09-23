<?php
/**
 * @package   Featured_Content_Manager
 * @author    Jesper Nilsson <jesper@klandestino.se>
 * @license   GPL-2.0+
 * @link      https://github.com/redundans
 * @copyright 2014 Jesper Nilsson
 *
 * @wordpress-plugin
 * Plugin Name:       	Featured Content Manager
 * Plugin URI:       	https://github.com/redundans/featured-content-manager
 * Description:       	Featured Content Manager is a plugin highly influenced by (the now dead projekt) wp-featured-content. It lets users create featured items that mirrors posts - then order them and edit their representation inside featured areas.
 * Version:           	0.1.0
 * Author:       		Jesper Nilsson
 * Author URI:      	https://github.com/redundans
 * Text Domain:     	featured-content-manager
 * License:         	GPL-2.0+
 * License URI:     	http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       	/languages
 * GitHub Plugin URI: 	https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * Include the plugin manager.
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-featured-content-manager.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Featured_Content_Manager', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Featured_Content_Manager', 'deactivate' ) );

/*
 * Add action to start instance after plugin is loaded.
 *
 */
add_action( 'plugins_loaded', array( 'Featured_Content_Manager', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'admin/class-featured-content-manager-customizer.php' );
add_action( 'init', array( 'Featured_Content_Manager_Customizer', 'get_instance' ) );