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
 * Plugin URI:       	https://plugins.klandestino.se
 * Description:       	Lets users create featured items that mirrors posts - then order them and edit their representation inside featured areas.
 * Version:           	0.4.2
 * Author:       		Klandestino AB
 * Author URI:      	https://klandestino.se
 * Text Domain:     	featured-content-manager
 * License:         	GPL-2.0+
 * License URI:     	http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       	/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

/*
 * Include the plugin.
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'public/class-featured-content-manager.php' );

/*
 * Include the plugin updater.
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'updater/updater.php' );

function fcm_plugin_updater() {

	$license_key = trim( get_option( 'fcm_license_key' ) );

	// setup the updater
	$fcm_updater = new EDD_SL_Plugin_Updater( FCM_STORE_URL, __FILE__, array(
			'version' 	=> '0.4.2', 				// current version number
			'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
			'item_name' => FCM_PRODUCT_NAME, 	// name of this plugin
			'author' 	=> 'Klandestino'  // author of this plugin
		)
	);

}
add_action( 'admin_init', 'fcm_plugin_updater', 0 );

/*
 * Add action to start instance after plugin is loaded.
 *
 */
add_action( 'plugins_loaded', array( 'Featured_Content_Manager', 'get_instance' ) );

/*
 * Add style class for featured items
 */
add_filter( 'post_class', array( 'Featured_Content_Manager', 'fcm_style_post_class' ) );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'admin/class-featured-content-manager-customizer.php' );
add_action( 'init', array( 'Featured_Content_Manager_Customizer', 'get_instance' ) );

add_action( 'admin_menu', array( 'Featured_Content_Manager', 'menu_page' ) );
