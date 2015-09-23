<?php
/**
 * Class Featured Content Manager.
 *
 * @package Featured_Content_Manager
 * @author  Jesper Nilsson <jesper@klandestino.se>
 */
class Featured_Content_Manager {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	const VERSION = '0.1.0';

	/**
	 * Unique identifier for featured item post type.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	const POST_TYPE = 'featured_item';

	/**
	 * Unique identifier for featured area taxonomy.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	const TAXONOMY  = 'featured_area';

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'featured-content-manager';

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Add theme support for featured content manager
		add_action( 'after_setup_theme', array( $this, 'theme_support' ) );

		// If the theme supports it, register post_type and taxonomy
		add_action( 'init', array( $this, 'init' ) );

		// Add filter for featured item permalink.
		add_filter( 'post_type_link', array( $this, 'filter_featured_item_permalink'), 10, 3 );

	}

	/**
	 * Turns theme support on when Featured Content Manager is installed
	 *
	 * @since    0.1.0
	 */
	function theme_support() {
		add_theme_support( $this->plugin_slug );
	}

	/**
	 * If the theme supports it, register post_type and taxonomy
	 *
	 * @since     0.1.0
	 */
	function init() {

		// If theme support is inactive halt here.
		if ( ! current_theme_supports( $this->plugin_slug ) )
			return;

		// Register the featured item post type.
		register_post_type( self::POST_TYPE, array(
			'public'               => false,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'taxonomies'           => array( self::TAXONOMY ),
			'supports'             => array( 'title', 'thumbnail', 'excerpt' ),
			'labels'               => array(
				'name'               => _x( 'Featured Items', 'post type general name', $this->plugin_slug ),
				'singular_name'      => _x( 'Featured Item', 'post type singular name', $this->plugin_slug ),
				'add_new'            => _x( 'Add New', 'Featured Item' ),
				'add_new_item'       => __( 'Add New Featured Item', $this->plugin_slug ),
				'edit_item'          => __( 'Edit Featured Item', $this->plugin_slug ),
				'new_item'           => __( 'New Featured Item', $this->plugin_slug ),
				'view_item'          => __( 'View Featured Item', $this->plugin_slug ),
				'search_items'       => __( 'Search Featured Items', $this->plugin_slug ),
				'not_found'          => __( 'No Featured Items found', $this->plugin_slug ),
				'not_found_in_trash' => __( 'No Featured Items found in Trash', $this->plugin_slug ),
			),
		) );

		// Register the featured area taxonomy.
		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE, 'post' ), array(
			'public'            => false,
			'hierarchical'      => true,
			'show_ui'           => false,
			'show_admin_column' => false,
			'show_in_menu'      => false,
			'description'       => __( 'This is some text explaining that the theme has this featured location.' ),
			'labels'            => array(
				'name'              => _x( 'Featured Area', 'taxonomy general name', $this->plugin_slug ),
				'singular_name'     => _x( 'Featured Area', 'taxonomy singular name', $this->plugin_slug ),
				'search_items'      => __( 'Search Featured Areas', $this->plugin_slug ),
				'all_items'         => __( 'All Featured Areas', $this->plugin_slug ),
				'parent_item'       => __( 'Parent Featured Area', $this->plugin_slug ),
				'parent_item_colon' => __( 'Parent Featured Area:', $this->plugin_slug ),
				'edit_item'         => __( 'Edit Featured Area', $this->plugin_slug ),
				'update_item'       => __( 'Update Featured Area', $this->plugin_slug ),
				'add_new_item'      => __( 'Add New Featured Area', $this->plugin_slug ),
				'new_item_name'     => __( 'New Featured Area Name', $this->plugin_slug ),
				'menu_name'         => __( 'Featured Area' ),
			),
		) );

		// If featured area taxonomy is registred add Main Area term.
		if ( ! term_exists( 'Main Area', self::TAXONOMY ) )
			wp_insert_term(
				'Main Area',
				self::TAXONOMY,
				array(
					'description'=> __( 'A default and predefined term for the Featured Area taxonomy.', $this->plugin_slug ),
				)
			);

	}

	/**
	 * Return featured content items for a specified featured area.
	 *
	 * @uses $wp_query
	 * @param string $area
	 * @param string/array $post_status
	 * @return object
	 *
	 * @since     0.1.0
	 */
	public static function get_featured_content( $area, $post_status = 'publish' ) {
		if( $post_status == '' ) $post_status = 'publish';
		if( isset($_REQUEST['wp_customize']) ) $post_status = 'draft';
		if( !is_int( $area ) ) $area = get_term_by( 'name', $area, self::TAXONOMY );

		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( 'wp_get_featured_content()', "Cannot be called before the 'init' action.", '3.8' );
			return array();
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_parent' => 0,
			'post_status' => $post_status,
			'orderby'   => 'menu_order',
			'order'		=> 'ASC',
		);

		if ( ! empty( $area ) )
			$args['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'id',
					'terms'    => $area,
				),
			);
		$query = new WP_Query( $args );
		return $query;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.1.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    0.1.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    0.1.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    0.1.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load Featured Content Manager text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;

		load_plugin_textdomain(
			$domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * AJAX callback for adding a featured area.
	 *
	 * @since    0.1.0
	 */
	public static function add_featured_area( ) {
		$area_title = $_POST['area'];
		if( wp_insert_term( $area_title, self::TAXONOMY ) ){
			echo json_encode( array( 'error' => null ) );
			die();
		}
		echo json_encode( array( 'error' => true ) );
		die();
	}

	/**
	 * AJAX callback for removing a featured area.
	 *
	 * @since    0.1.0
	 */
	public static function remove_featured_area( ) {
		$area_id = (int)$_POST['area'];
		if( wp_delete_term( $area_id, self::TAXONOMY ) ){
			echo json_encode( array( 'error' => null ) );
			die();
		}
		echo json_encode( array( 'error' => true ) );
		die();
	}

	/**
	 * AJAX callback for geting a post.
	 *
	 * @since    0.1.0
	 */
	public static function get_featured_content_post( ) {
		$post_id = $_POST['post_id'];
		$target = $_POST['target'];

		$post = get_post( $post_id );
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );
		$post_thumbnail = get_post( $post_thumbnail_id );
		if(!$post_thumbnail){
			$post_thumbnail = new stdClass();
			$post_thumbnail->ID = '';
		}

		$output = array(
			'error' => 0,
			'post' => $post,
			'post_original' => array( 'ID' => $post_id ),
			'post_thumbnail' => $post_thumbnail,
			'term' => $target
		);

		echo json_encode( $output );

		die();
	}

	/**
	 * Callback for cleaning up and saving the posts in the given order.
	 *
	 * @param string $post_status
	 *
	 * @since    0.1.0
	 */
	public static function save_order( $post_status = 'publish', $values ){
		if( $post_status == '' ) {
			$post_status = 'publish';
		}
		if(isset($values['_wpnonce'])) {
			if( wp_verify_nonce( $values['_wpnonce'], 'fcm_save_posts' ) ) {
				$post_ids = $values['post_id'];
				$featured_area = $values['featured_area'];

				$trash_posts = new WP_Query(
					array(
						'post_type' => self::POST_TYPE,
						'post_status' => ($post_status == 'publish' ? array('publish', 'future') : 'draft'),
						'tax_query' => array(
							array(
								'taxonomy' => self::TAXONOMY,
								'field'    => 'id',
								'terms'    => $featured_area,
							),
						)
					)
				);

				if ( $trash_posts->have_posts() ) {
					while ( $trash_posts->have_posts() ) {
						$trash_posts->the_post();
						wp_delete_post( get_the_ID(), true );
						wp_delete_object_term_relationships( get_the_ID(), self::TAXONOMY, TRUE );
					}
					wp_reset_postdata();
				}

				$index = 0;
				$parent_id = '';
				if( is_array( $post_ids ) ) {
					foreach ($post_ids as $post_id) {
						$index++;
						$parent = 0;
						if( $values['child'][$index] == 'true' ) {
							$parent = $parent_id;
						}
						$post =
							array(
								'ID' => '',
								'post_title' => $values['post_title'][$index],
								'post_content' => $values['post_content'][$index],
								'menu_order' => $values['menu_order'][$index],
								'post_parent' => $parent,
								'post_type' => self::POST_TYPE,
								'post_status' => $post_status,
								'post_date' => date( 'Y-m-d H:i:s', strtotime( $values['post_date'][$index] ) )
							);
						$featured_content_id = wp_insert_post( $post );
						if( $parent == 0 ) {
							$parent_id = $featured_content_id;
						}
						update_post_meta( $featured_content_id, 'cfm_post_parent', $values['post_original'][$index] );
						wp_set_post_terms( $featured_content_id, array( $featured_area ), self::TAXONOMY, TRUE );
						if( $values['post_thumbnail'][$index] != '' ) {
							set_post_thumbnail( $featured_content_id, $values['post_thumbnail'][$index] );
						}
					}
				}
			}
		}
	}

	/**
	 * Filter that returns the original post url when permalink for featured
	 * item is requested.
	 *
	 * @param string $url
	 * @param string $post
	 * @return string
	 *
	 * @since    0.1.0
	 */
	public function filter_featured_item_permalink( $url, $post ){

		// Only do this if post type is featured item
		if ( $post->post_type == 'featured_item' ) {

			// Get original post id
			$post_parent = get_post_meta( $post->ID, 'cfm_post_parent', TRUE );

			// If original post is a featured item this will loop infinitly, halt.
			if( get_post_type( $post_parent ) != 'featured_item' ){
				$url = get_permalink( $post_parent );
			}
		}
		return $url;
	}
}

/**
 * Public function that return featured content items for a specified
 * featured area from theme or other plugins.
 *
 * @param string $area
 * @param string/array $post_status
 * @return object
 *
 * @since    0.1.0
 */
function get_featured_content( $area, $post_status = array( 'publish' ) ){
	return Featured_Content_Manager::get_featured_content( $area, $post_status );
}