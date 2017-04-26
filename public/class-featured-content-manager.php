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
	const VERSION = '0.7';

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

		//Maybe add terms
		add_action( 'customize_register', array( $this, 'maybe_add_terms' ) );

		// Add featured content to post object
		add_action( 'the_post', array( $this, 'populate_the_post'), 10, 2 );
		add_action( 'get_post_metadata', array( $this, 'populate_the_thumbnail'), 10, 4 );

		// Add action for altering main query.
		add_action( 'pre_get_posts', array( $this, 'fcm_alter_main_query' ) );

		// Add action for population children.
		add_filter( 'the_excerpt', array( $this, 'fcm_populate_children' ) );
		add_filter( 'the_content', array( $this, 'fcm_populate_children' ) );

	}

	/**
	 * If the theme supports it, register post_type and taxonomy
	 *
	 * @since     0.1.0
	 */
	function init() {

		// Register the featured item post type.
		register_post_type( self::POST_TYPE, array(
			'public'     => false,
			'show_ui'    => true,
			'show_in_menu'         => false,
			'taxonomies' => array( self::TAXONOMY ),
			'supports'   => array( 'title', 'thumbnail', 'excerpt' ),
			'labels'     => array(
				'name'     => _x( 'Featured Items', 'post type general name', $this->plugin_slug ),
				'singular_name'      => _x( 'Featured Item', 'post type singular name', $this->plugin_slug ),
				'add_new'  => _x( 'Add New', 'Featured Item' ),
				'add_new_item'       => __( 'Add New Featured Item', $this->plugin_slug ),
				'edit_item'=> __( 'Edit Featured Item', $this->plugin_slug ),
				'new_item' => __( 'New Featured Item', $this->plugin_slug ),
				'view_item'=> __( 'View Featured Item', $this->plugin_slug ),
				'search_items'       => __( 'Search Featured Items', $this->plugin_slug ),
				'not_found'=> __( 'No Featured Items found', $this->plugin_slug ),
				'not_found_in_trash' => __( 'No Featured Items found in Trash', $this->plugin_slug ),
			),
		) );

		// Register the featured area taxonomy.
		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE, 'post' ), array(
			'public'  => false,
			'hierarchical'      => true,
			'show_ui' => false,
			'show_admin_column' => false,
			'show_in_menu'      => false,
			'description'       => __( 'This is some text explaining that the theme has this featured location.' ),
			'labels'  => array(
				'name'    => _x( 'Featured Area', 'taxonomy general name', $this->plugin_slug ),
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
			'public'  => false,
			'hierarchical'      => true,
			'show_ui' => false,
			'show_admin_column' => false,
			'show_in_menu'      => false,
			'description'       => __( 'This is some text explaining that the theme has this featured location.' ),
			'labels'  => array(
				'name'    => _x( 'Featured Content Style', 'taxonomy general name', $this->plugin_slug ),
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
	}

	/**
	 * Maybe insert terms for featured_areas and featured_styles
	 * @since 0.5.0
	 */
	public function maybe_add_terms() {
		global $fcm_registered_styles;

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
		if ( $post_status === '' ) {
			$post_status = 'publish';
		}

		if ( isset( $_REQUEST['wp_customize'] ) ) {
			$post_status = 'draft';
		}

		if ( ! is_int( $area ) ) {
			$area = get_term_by( 'name', $area, self::TAXONOMY );
		} else {
			$area = get_term_by( 'id', $area, self::TAXONOMY );
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_parent' => 0,
			'post_status' => $post_status,
			'orderby'   => 'menu_order',
			'order'		=> 'ASC',
		);

		if ( ! empty( $area ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => self::TAXONOMY,
					'field'    => 'id',
					'terms'    => $area->term_id,
				),
			);
		}

		$taxquery = array(
			array(
				'taxonomy' => self::TAXONOMY,
				'field'    => 'id',
				'terms'    => $area->term_id,
				'operator' => 'IN',
			),
		);

		$posts = get_posts( array(
			'post_type' => self::POST_TYPE,
			'post_status' => $post_status,
			'tax_query' => $taxquery,
			'post_parent' => 0,
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		) );

		$post_ids = array();

		foreach ( wp_list_pluck( $posts, 'ID' ) as $id ) {
			$post_ids[] = get_post_meta( $id, 'fcm_post_parent', true );
		}
		$args2 = apply_filters( 'fcm_query_args', array(
			'post__in' => $post_ids,
			'orderby' => 'post__in',
			'fcm' => true,
			'fcm_area'   => $area->slug,
		) );

		$query = new WP_Query( $args2 );
		return $query;
	}

	/**
	 * Return featured content children for a specified featured item.
	 *
	 * @uses $wp_query
	 * @param string/int $post_id
	 * @return object
	 *
	 * @since     0.5.0
	 */
	public static function get_children( $post_id ) {
		if( isset($_REQUEST['wp_customize']) ) {
			$post_status = 'draft';
		} else {
			$post_status = 'publish';
		}

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_parent' => $post_id,
			'post_status' => $post_status,
			'orderby'   => 'menu_order',
			'order'		=> 'ASC',
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
	public static function get_featured_content_post() {
		$post_id = $_POST['post_id'];
		$target = $_POST['target'];
		$fcm_has_switched = false;

		if ( fcm_is_multisite_elasticsearch_enabled() && isset( $_POST['site_id'] ) && ! empty( $_POST['site_id'] ) ) {
			$site_id = $_POST['site_id'];
			if ( absint( $site_id) !== get_current_blog_id() ) {
				$fcm_has_switched = true;
			}
			switch_to_blog( absint( $_POST['site_id'] ) );
		} else {
			$site_id = '';
		}

		$post = get_post( $post_id );
		if( isset( $post->post_title ) ) {
			$post->post_title = html_entity_decode( $post->post_title );
		}
		if( has_excerpt( $post_id ) ) {
			$post->post_content = $post->post_excerpt;
		} else {
			$post->post_content = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
		}
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );
		$post_thumbnail = get_post( $post_thumbnail_id );
		if(!$post_thumbnail){
			$post_thumbnail = new stdClass();
			$post_thumbnail->ID = '';
		}

		//If post thumbnail is from another site, do not return the id, only the url
		if ( fcm_is_multisite_elasticsearch_enabled() && $fcm_has_switched ) {
			$post_thumbnail->ID = '';
		}

		$output = array(
			'error' => 0,
			'post' => $post,
			'post_original' => array( 'ID' => $post_id ),
			'post_thumbnail' => $post_thumbnail,
			'term' => $target,
			'site_id' => $site_id,
		);

		if ( fcm_is_multisite_elasticsearch_enabled() && isset( $_POST['site_id'] ) && ! empty( $_POST['site_id'] ) ) {
			restore_current_blog();
		}

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
	public static function save_order( $post_status = 'publish', $values ) {
		global $wpdb;

		if ( isset( $values['_wpnonce'] ) ) {
			if ( wp_verify_nonce( $values['_wpnonce'], 'fcm_save_posts' ) ) {
				$featured_area = $values['featured_area'];

				$wpdb->query( "DELETE a,b,c FROM $wpdb->posts a LEFT JOIN $wpdb->term_relationships b ON (a.ID = b.object_id) LEFT JOIN $wpdb->postmeta c ON (a.ID = c.post_id) LEFT JOIN $wpdb->term_taxonomy d ON (b.term_taxonomy_id = d.term_taxonomy_id) WHERE d.term_id = '" . $featured_area . "' AND a.post_type = '" . self::POST_TYPE . "' AND a.post_status = '" . $post_status . "'" );

				if( isset($values['post_id']) ) {
					$post_ids = $values['post_id'];
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
							if ( 0 === $parent ) {
								$parent_id = $featured_content_id;
							}
							if ( isset( $values['url'][ $index ] ) ) {
								update_post_meta( $featured_content_id, 'fcm_blurb', true );
								if ( ! empty( $values['url'][ $index ] ) ) {
									update_post_meta( $featured_content_id, 'fcm_blurb_url', esc_url( $values['url'][ $index ] ) );
								}
							}
							update_post_meta( $featured_content_id, 'fcm_post_parent', $values['post_original'][$index] );
							wp_set_post_terms( $featured_content_id, array( $featured_area ), self::TAXONOMY, TRUE );
							if ( $values['post_thumbnail'][$index] != '' && get_current_blog_id() == $values['site_id'][$index]) {
								set_post_thumbnail( $featured_content_id, $values['post_thumbnail'][$index] );
							}
							if ( $values['site_id'][$index] != '' ) {
								update_post_meta( $featured_content_id, 'fcm_site_id', $values['site_id'][$index] );
							}

							if ( isset( $values['style'][$index] ) && $values['style'][$index] != '' ){
								wp_set_post_terms( $featured_content_id, array( $values['style'][$index] ), self::STYLE_TAXONOMY, TRUE );
							}
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
			$post_status = (isset( $wp_customize )) ? 'draft' : 'publish';

			$taxquery = array(
				array(
					'taxonomy' => Featured_Content_Manager::TAXONOMY,
					'field' => 'slug',
					'terms' => 'fcm-main-area',
					'operator'=> 'IN'
				)
			);

			$posts = get_posts( array(
				'post_type' => Featured_Content_Manager::POST_TYPE,
				'post_status' => $post_status,
				$taxquery,
				'post_parent' => 0,
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			) );

			$post_ids = array();
			foreach ( wp_list_pluck( $posts, 'ID' ) as $id ) {
				$post_ids[] = get_post_meta( $id, 'fcm_post_parent', TRUE );
			}
			$query->set( 'post__in', $post_ids );
			$query->set( 'orderby', 'post__in' );
			$query->set( 'fcm', true );
			$query->set( 'fcm_area', 'fcm-main-area' );
		}
	}

	/**
	 * A functions to populate the post object with the featured item title, content and excerpt
	 * @param  object $post
	 * @param  object $query
	 */
	function populate_the_post( $post, $query ) {

		// Stop if query_vars does not includes a fcm parameter
		if ( isset( $query->query_vars['fcm'] ) ) :
			global $wp_customize;

			// Set post status to query
			$post_status = (isset( $wp_customize )) ? 'draft' : 'publish';

			// Build our query
			$org = get_posts( array(
				'post_type' => 'featured_item',
				'post_parent' => 0,
				'post_status' => $post_status,
				'tax_query' => array(
					array(
						'taxonomy' => Featured_Content_Manager::TAXONOMY,
						'field' => 'slug',
						'terms' => $query->query_vars['fcm_area'],
						'operator'=> 'IN'
					)
				),
				'menu_order' => $query->current_post,

			) );

			// If query return posts, populate the post object with the featured item title, content and excerpt
			if( isset($org[0]) ){
				$post->post_excerpt = $org[0]->post_excerpt;
				$post->post_title = $org[0]->post_title;
				$post->post_content = $org[0]->post_content;
				$post->fcm_org_ID = $org[0]->ID;
			}
		endif;
	}

	/**
	 * A function that returns the featurd item thumbnail
	 * @param  [type] $value
	 * @param  integer $object_id
	 * @param  string $meta_key
	 * @param  bool $single
	 *
	 * @since  0.5
	 */
	function populate_the_thumbnail( $value, $object_id, $meta_key, $single ) {
		global $post;

		// Stop if the meta key is not that of a thumbnail
		if ( $meta_key !== '_thumbnail_id' ) {
			return;
		}

		// If the global post_object exist and has a fcm_org_ID parameter return the featured item thumbnail
		if ( isset( $post->ID ) ) {
			if ( $post->ID == $object_id && isset( $post->fcm_org_ID ) ) {
				return get_post_thumbnail_id( $post->fcm_org_ID );
			}
		}
	}


	/**
	 * Altering the main query for home to include featured items.
	 *
	 * @param string $content
	 *
	 * @since 0.5
	 */
	function fcm_populate_children( $content ){

		// Stop if current theme manualy configure Featured Items
		if ( current_theme_supports( $this->plugin_slug ) )
			return $content;

		// Stop if the post_type is not Featured Item
		if( $GLOBALS['post']->post_type == Featured_Content_Manager::POST_TYPE ){

			// Get all the children post objects
			$children = get_children( array( 'post_parent' => $GLOBALS['post']->ID, 'post_type' => Featured_Content_Manager::POST_TYPE, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ), ARRAY_A );

			// If there is children add Markup to the parent post_content
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
	 * Adds the featured item style term as post class
	 *
	 * @param  array $classes  Predefined classes
	 * @return array Predefined plus new classes
	 *
	 */
	public static function fcm_style_post_class( $classes, $class, $post_id ) {
		global $post, $query;

		// If the global post object has a fcm_org_ID parameter populate the class array with each of the featured item styles term slug
		if ( isset( $post->fcm_org_ID ) ) {
			$style_list = wp_get_post_terms($post->fcm_org_ID, self::STYLE_TAXONOMY);
			foreach($style_list as $style) {
				$classes[] = $style->slug;
			}
		}
		return $classes;
	}

	/**
	 * A function that merges an array to the global fcm_registered_styles
	 *
	 * @param  array  $styles
	 *
	 */
	public static function fcm_register_styles( $styles = array() ) {
		global $fcm_registered_styles;
		$fcm_registered_styles = array_merge( (array) $fcm_registered_styles, $styles );
	}

	/**
	 * A function that adds the a menu objevt for the featured content panel in the customizer
	 *
	 */
	public static function menu_page() {
		add_menu_page( __('Featured Content Manager', 'featured-content-manager' ), __('Featured Content', 'featured-content-manager' ), 'manage_options', '/customize.php?autofocus%5Bpanel%5D=featured_areas&return=%2Fwp-admin%2Findex.php', '', 'dashicons-exerpt-view', 61 );
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
function fcm_get_content( $area, $post_status = array( 'publish' ) ){
	return Featured_Content_Manager::get_featured_content( $area, $post_status );
}

/**
 * A public function for the register style function
 *
 * @param  array  $styles
 *
 * @since    0.5
 */
function fcm_register_styles( $styles = array() ){
	Featured_Content_Manager::fcm_register_styles( $styles );
}

/**
 * A public function that returns a WP_Query object from public
 *
 * @param  string $post_id
 * @return object WP_Query
 *
 * @since    0.5
 */
function fcm_get_children( $post_id = '' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	return Featured_Content_Manager::get_children( $post_id );
}

/**
 * If your site uses elasticpress on multisite you can return true in this filter
 * to be able to search and get content from all sites.
 * Do this at your own risk and roll your own frontend output using switch_to_blog etc.
 */
function fcm_is_multisite_elasticsearch_enabled() {
	if ( ! is_multisite() ) {
		return false;
	}
	return apply_filters( 'fcm_is_multisite_elasticsearch_enabled', false );
}

/**
 * Add support for custom blurb functionality.
 * Do this at your own risk and roll your own frontend output.
 */
function fcm_enable_blurbs() {
	return apply_filters( 'fcm_is_blurbs_enabled', false );
}
