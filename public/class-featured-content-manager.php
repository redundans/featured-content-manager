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
	const VERSION = '0.3';

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
	 * Unique identifier for featured area taxonomy.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	const STYLE_TAXONOMY  = 'featured_item_style';

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
	public $plugin_slug = 'featured-content-manager';

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

		// If the theme supports it, register post_type and taxonomy
		add_action( 'init', array( $this, 'init' ) );

		// Add filter for featured item permalink.
		add_filter( 'post_type_link', array( $this, 'filter_featured_item_permalink'), 10, 3 );

		/*
		 * Add action for altering main query.
		 *
		 */
		add_action( 'pre_get_posts', array( $this, 'fcm_alter_main_query' ) );

		/*
		 * Add action for population children.
		 *
		 */
		add_filter( 'the_excerpt', array( $this, 'fcm_populate_children' ) );
		add_filter( 'the_content', array( $this, 'fcm_populate_children' ) );

	}

	/**
	 * If the theme supports it, register post_type and taxonomy
	 *
	 * @since     0.1.0
	 */
	function init() {
		global $fcm_registered_styles;

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

		// Register the featured content style
		register_taxonomy( self::STYLE_TAXONOMY, array( self::POST_TYPE ), array(
			'public'            => false,
			'hierarchical'      => true,
			'show_ui'           => false,
			'show_admin_column' => false,
			'show_in_menu'      => false,
			'description'       => __( 'This is some text explaining that the theme has this featured location.' ),
			'labels'            => array(
				'name'              => _x( 'Featured Content Style', 'taxonomy general name', $this->plugin_slug ),
				'singular_name'     => _x( 'Featured Content Style', 'taxonomy singular name', $this->plugin_slug ),
				'search_items'      => __( 'Search Featured Content Styles', $this->plugin_slug ),
				'all_items'         => __( 'All Featured Content Styles', $this->plugin_slug ),
				'parent_item'       => __( 'Parent Featured Content Style', $this->plugin_slug ),
				'parent_item_colon' => __( 'Parent Featured Content Style:', $this->plugin_slug ),
				'edit_item'         => __( 'Edit Featured Content Style', $this->plugin_slug ),
				'update_item'       => __( 'Update Featured Content Style', $this->plugin_slug ),
				'add_new_item'      => __( 'Add New Featured Content Style', $this->plugin_slug ),
				'new_item_name'     => __( 'New Featured Content Style Name', $this->plugin_slug ),
				'menu_name'         => __( 'Featured Content Style' ),
			),
		) );

		// If featured area taxonomy is registred add Main Area term.
		if ( ! term_exists( 'Main Area', self::TAXONOMY ) )
			wp_insert_term(
				'Main Area',
				self::TAXONOMY,
				array(
					'description'=> __( 'A default and predefined term for the Featured Area taxonomy.', $this->plugin_slug ),
					'slug' => 'fcm-main-area',
				)
			);

		// Delete featured content styles if not registred
		$styles = get_terms( self::STYLE_TAXONOMY, array('hide_empty' => 0) );
		if( taxonomy_exists(self::STYLE_TAXONOMY) ) {
			foreach ($styles as $style) {
				if ( !in_array( $style->name, $fcm_registered_styles) ) {
					wp_delete_term( $style->term_id, self::STYLE_TAXONOMY );
				}
			}
		}
		// Insert featured content styles if registred
		if ( $fcm_registered_styles ) {
			foreach ($fcm_registered_styles as $fcm_registered_style) {
				if( !term_exists( $fcm_registered_style, self::STYLE_TAXONOMY ) ){
					$out = wp_insert_term( $fcm_registered_style, self::STYLE_TAXONOMY );
				}
			}
		}
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
		if( !is_int( $area ) ) {
			$area = get_term_by( 'name', $area, self::TAXONOMY );
			$area = $area->term_id;
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
	 * Load Featured Content text domain for translation.
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
	 * AJAX callback for geting a post.
	 *
	 * @since    0.1.0
	 */
	public static function get_featured_content_post( ) {
		$post_id = $_POST['post_id'];
		$target = $_POST['target'];

		$post = get_post( $post_id );
		if( has_excerpt($post_id) )
			$post->post_content = $post->post_excerpt;
		else
			$post->post_content = wp_trim_words( $post->post_content );
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
	 * @since    1.0
	 */
	public static function save_order( $post_status = 'publish', $values ){
		if( $post_status == '' ) {
			$post_status = 'publish';
		}
		if(isset($values['_wpnonce']) && isset($values['post_id']) ) {
			if( wp_verify_nonce( $values['_wpnonce'], 'fcm_save_posts' ) ) {
				$post_ids = $values['post_id'];
				$featured_area = $values['featured_area'];

				$trash_posts = new WP_Query(
					array(
						'post_status' => $post_status,
						'post_type' => self::POST_TYPE,
						'tax_query' => array(
							array(
								'taxonomy' => self::TAXONOMY,
								'field'    => 'id',
								'terms'    => $featured_area,
							)
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
								'post_excerpt' => $values['post_content'][$index],
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
						update_post_meta( $featured_content_id, 'fcm_post_parent', $values['post_original'][$index] );
						wp_set_post_terms( $featured_content_id, array( $featured_area ), self::TAXONOMY, TRUE );
						if ( $values['post_thumbnail'][$index] != '' ) {
							set_post_thumbnail( $featured_content_id, $values['post_thumbnail'][$index] );
						}

						if ( isset( $values['style'][$index] ) && $values['style'][$index] != '' ){
							wp_set_post_terms( $featured_content_id, array( $values['style'][$index] ), self::STYLE_TAXONOMY, TRUE );
						}
					}
				}
			}
		}
	}

	/**
	 * Static function for trashing posts.
	 *
	 * @param string $post_status
	 *
	 * @since    0.1.0
	 */
	public static function trash_posts( $post_status, $featured_area ){
		if( $post_status == 'publish' ) {
			$post_statuses = array('publish');
		} else {
			$post_statuses = array('draft');
		}

		$trash_posts = new WP_Query(
			array(
				'post_type' => self::POST_TYPE,
				'tax_query' => array(
					array(
						'taxonomy' => self::TAXONOMY,
						'field'    => 'id',
						'terms'    => $featured_area,
					)
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
	}

	/**
	 * Altering the main query for home to include featured items.
	 *
	 * @param object $query
	 *
	 * @since    1.0
	 */
	function fcm_alter_main_query( $query ) {

		if ( !current_theme_supports($this->plugin_slug) && term_exists( 'Main Area', Featured_Content_Manager::TAXONOMY ) && $query->is_main_query() && $query->is_home() ){
			global $wp_customize;
			if ( isset( $wp_customize ) )
				$query->set('post_status', 'draft');

			$taxquery = array(
				array(
					'taxonomy' => Featured_Content_Manager::TAXONOMY,
					'field' => 'slug',
					'terms' => 'fcm-main-area',
					'operator'=> 'IN'
				)
			);

			$query->set('post_type', Featured_Content_Manager::POST_TYPE);
			$query->set('tax_query', $taxquery );
			$query->set('post_parent', 0);
			$query->set('orderby', 'menu_order');
			$query->set('order', 'ASC');
		}
	}


	/**
	 * Altering the main query for home to include featured items.
	 *
	 * @param string $content
	 *
	 * @since    1.0
	 */
	function fcm_populate_children( $content ){
		if ( current_theme_supports($this->plugin_slug) )
			return $content;
		if( $GLOBALS['post']->post_type == Featured_Content_Manager::POST_TYPE ){
			$children = get_children( array( 'post_parent' => $GLOBALS['post']->ID, 'post_type' => Featured_Content_Manager::POST_TYPE, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ), ARRAY_A );
			if ( $children ) {
				$featured_children = '';
				foreach ( $children as $child ) {
					$featured_children .= sprintf(
						'<li><a href="%s">%s</a></li>',
						get_permalink( get_post_meta( $child['ID'], 'fcm_post_parent', true ) ),
						$child['post_title']
					);
				}
				$content = sprintf(
					'%s <div class="featured-content-children"><h2>%s</h2><ul>%s</ul></div>',
		            			$content,
		            			esc_html( 'Related posts', 'featured-content-manager' ),
		            			$featured_children
		            		);
			}
			return $content;
		}
		return $content;
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
		if ( $post->post_type === Featured_Content_Manager::POST_TYPE ) {

			// Get original post id
			$post_parent = get_post_meta( $post->ID, 'fcm_post_parent', TRUE );

			// If original post is a featured item this will loop infinitly, halt.
			if( get_post_type( $post_parent ) != 'featured_item' ){
				$url = get_permalink( $post_parent );
			}
		}
		return $url;
	}

	/**
	 * Adds the featured item style term as post class
	 *
	 * @param  array $classes  Predefined classes
	 * @return array           Predefined plus new classes
	 */
	public static function fcm_style_post_class( $classes ) {
		global $post;
		if( get_post_type($post->ID) === self::POST_TYPE && taxonomy_exists(self::STYLE_TAXONOMY) ) {
			$style_list = wp_get_post_terms($post->ID, self::STYLE_TAXONOMY);
			foreach($style_list as $style) {
				$classes[] = $style->slug;
			}
		}
		return $classes;
	}

	public static function fcm_register_styles( $styles = array() ) {
		global $fcm_registered_styles;
        		$fcm_registered_styles = array_merge( (array) $fcm_registered_styles, $styles );
	}

	public static function menu_page() {
		add_menu_page( __('Featured Content Manager', 'featured-content-manager' ), __('Featured Content', 'featured-content-manager' ), 'featured-content-manager', '/customize.php?autofocus%5Bpanel%5D=featured_areas&return=%2Fwp-admin%2Findex.php', '', 'dashicons-exerpt-view', 61 );
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

function register_featured_content_styles(  $styles = array() ){
	Featured_Content_Manager::fcm_register_styles( $styles );
}
