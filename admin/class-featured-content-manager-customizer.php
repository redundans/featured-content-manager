<?php
/**
 * Featured Content Manager Customizer.
 *
 * @package Featured_Content_Manager_Customizer
 * @author  Jesper Nilsson <jesper@klandestino.se>
 */
class Featured_Content_Manager_Customizer {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles, adding a
	 * cusomizer settings and setting up actions & filters.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		// Call $plugin_slug from public plugin class.
		$plugin = Featured_Content_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Register customizer panels, settings and option
		add_action( 'customize_register', array( $this, 'featured_area_customize_register' ) );

		// Load admin style sheet.
		add_action( 'customize_controls_print_styles', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load admin JavaScript.
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'available_featured_items_panel' ) );
		add_action( 'customize_controls_print_footer_scripts',  array( $this, 'search_result_templates' ) );
		add_action( 'customize_controls_print_footer_scripts',  array( $this, 'featured_item_templates' ) );

		// Save posts when "Save & Publish" is pressed from Customizer
		add_action( 'customize_save_after',  array( $this, 'featured_area_customize_save' ) );

		// Save, find and get single post from AJAX request
		add_action( 'wp_ajax_fcm_save_posts', array( $this, 'save_draft_order' ) );
		add_action( 'wp_ajax_search_content', array( $this, 'featured_content_search' ) );
		add_action( 'wp_ajax_get_post', array( $this, 'get_post' ) );
		add_action( 'wp_ajax_add_area', array( $this, 'add_area' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Register and enqueue customizer-specific style sheet.
	 *
	 * @since     0.1.0
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( $this->plugin_slug .'-customizer-styles', plugins_url( 'assets/css/customizer.min.css', __FILE__ ), array(), Featured_Content_Manager::VERSION );
	}

	/**
	 * Register and enqueue customizer-specific JavaScript.
	 *
	 * @since     0.1.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-customizer-script', plugins_url( 'assets/js/customizer.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), Featured_Content_Manager::VERSION );
	}

	/**
	 * Call for Featured Content Manager class' function to save drafts
	 *
	 * @since    0.1.0
	 */
	public function save_draft_order(){
		Featured_Content_Manager::save_order( 'draft', $_REQUEST );
		echo json_encode( array( 'error' => false ) );
		die();
	}

	/**
	 * Call for Featured Content Manager class' function to publish
	 *
	 * @since    0.1.0
	 */
	public function featured_area_customize_save($wp_customize){

		// Get a insance of the featured content class
		Featured_Content_Manager::get_instance();

		// Save each featured area setting
		foreach ( $wp_customize->settings() as $setting ) {
			if( strpos($setting->id,'featured_area_') !== false ){
				$form = $wp_customize->get_setting( $setting->id )->value();

				$values = array();
				parse_str($form, $values);

				// Send form values to private save_order function
				Featured_Content_Manager::save_order('publish', $values);
			}
		}
	}

	/**
	 * Call for Featured Content Manager class' function to get a post
	 *
	 * @since    0.1.0
	 */
	public function get_post(){
		Featured_Content_Manager::get_featured_content_post();
	}

	/**
	 * Add panel, sections, settings and controls form evere Featured Area.
	 *
	 * @since    0.1.0
	 */
	public function featured_area_customize_register( $wp_customize ){

		// Include Featured_Area_Control class
		require( plugin_dir_path( __FILE__ ) . 'includes/class-featured-area-control.php' );

		// Add customizer panel for featured areas
		$wp_customize->add_panel( 'featured_areas', array(
			'title'       => __('Feature Content Manager', $this->plugin_slug ),
			'description' => '<p>' . __( 'This panel is used for managing featured areas. You can add pages and posts.', $this->plugin_slug ) . '</p>',
			'priority'    => 20
		) );

		// Get all featured areas created by the template or the plugin
		$featured_areas = get_terms( Featured_Content_Manager::TAXONOMY, array( 'hide_empty' => false, 'orderby' => 'id', 'order' => 'DESC' ) );

		// For each featured area term registred
		foreach ($featured_areas as $featured_area) :
			$section_id = 'featured_area_' . $featured_area->term_id;
			$area_name_setting_id = $section_id . '[name]';

			// Add customizer section
			$wp_customize->add_section( $section_id,
				array(
					'title' => $featured_area->name,
					'priority' => 10,
					'panel'     => 'featured_areas',
				)
			);

			// Add customizer setting
			$wp_customize->add_setting( $area_name_setting_id,
				array(
					'default' => '',
					'transport' => 'refresh',
				)
			);

			// And last add a controller
			$wp_customize->add_control(
				new Featured_Area_Control( $wp_customize, 'featured-area-'.$featured_area->term_id,
					array(
						'label' => __( 'Featured Area', $this->plugin_slug ),
						'section' => $section_id,
						'settings' => $area_name_setting_id,
						'area' => $featured_area->term_id,
					)
				)
			);

		endforeach;
	}

	/**
	 * Handle AJAX search request for available features content.
	 *
	 * @since    0.1.0
	 */
	function featured_content_search(){

		// Return error if no search term was found
		if( isset($_REQUEST['search_term']) ){

			// Create query for the search
			$search_query = new WP_Query( array(
					's' => $_REQUEST['search_term'],
					'post_type' => array('post','page'),
					'post_per_page' => '10'
				)
			);

			// Populate the output and return as JSON
			if ( $search_query->have_posts() ) {
				$output = array();
				$i = 0;
				while ( $search_query->have_posts() ) {
					$search_query->the_post();
					$output[$i]['ID'] = get_the_id();
					$output[$i]['post_title'] = html_entity_decode(get_the_title());
					$output[$i]['post_type'] = get_post_type();
					$output[$i]['post_content'] = wp_trim_words( wp_strip_all_tags( strip_shortcodes( get_the_content() ) ), 12, '...' );
					$i++;
				}
				echo json_encode( array( 'error' => FALSE, 'result' => $output ) );
				die();
			}
		}
		echo json_encode( array( 'error' => TRUE, 'message' => __( 'Nothing found.' , $this->plugin_slug ) ) );
		die();
	}

	/**
	 * Print out panel for available featured contents and the search form.
	 *
	 * @since    0.1.0
	 */
	public function available_featured_items_panel() {
	?>
		<div id="available-featured-items" class="accordion-container">
			<div id="available-featured-items-filter">
				<label class="screen-reader-text" for="featured-items-search"><?php esc_html_e( 'Search Content' , $this->plugin_slug ); ?></label>
				<input type="search" id="featured-items-search" placeholder="<?php esc_attr_e( 'Search content', $this->plugin_slug ); ?>">
			</div>
			<div id="featured-items-filter-result">
				<ul>
				</ul>
			</div>
		</div>
	<?php
	}

	/**
	 * Print out featured item templates i wp-template format.
	 *
	 * @since    0.1.0
	 */
	public function featured_item_templates() {
		$styles = get_terms( Featured_Content_Manager::STYLE_TAXONOMY, array('hide_empty' => 0) );
	?>
	<script type="text/html" id="tmpl-featured-item">
		<li class="closed">
			<div class="fcm-title">
				<div class="sidebar-name-arrow"><span class="toggle-indicator" aria-hidden="true"></span></div>
				<div class="sidebar-parent-arrow"></div>
				<div class="sidebar-delete-icon"></div>
				<h4>{{{data.post.post_title}}}</h4>
			</div>
			<div class="fcm-inside">
				<fieldset name="post-{{data.post.ID}}">
					<input type="hidden" name="post_thumbnail[{{data.index}}]" value="{{data.post_thumbnail.ID}}">
					<input type="hidden" name="area[{{data.index}}]" value="{{data.term}}">
					<input type="hidden" name="menu_order[{{data.index}}]" value="{{data.post.menu_order}}">
					<input type="hidden" name="post_id[{{data.index}}]" value="{{data.post.ID}}">
					<input type="hidden" name="child[{{data.index}}]" value="{{data.child}}">
					<input type="hidden" name="post_original[{{data.index}}]" value="{{data.post_original.ID}}">
					<?php if ( current_theme_supports( 'post-thumbnails' ) ) { ?>
						<div class="uploader">
							<# if( data.post_thumbnail.guid) { #>
							<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail"><img src="{{data.post_thumbnail.guid}}"></a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" class="remove-thumbnail"><?php esc_html_e( 'Delete thumbnail', $this->plugin_slug ); ?></a>
							</p>
							<# } else { #>
							<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail"><?php esc_html_e( 'Select thumbnail', $this->plugin_slug ); ?></a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" style="display: none;" class="remove-thumbnail"><?php esc_html_e( 'Delete thumbnail', $this->plugin_slug ); ?></a>
							</p>
							<# } #>
					<?php } ?>
					<?php
						if ( ! empty( $styles ) ) {
							$i = 0;
							echo '<select class="widefat" name="style[{{data.index}}]">';
							foreach ($styles as $style) {
								if($i==0)
									echo '<option value="' . $style->term_id . '" selected>' . $style->name . '</option>';
								else
									echo '<option value="' . $style->term_id . '">' . $style->name . '</option>';
								$i++;
							}
							echo '</select>';
						}
					?>
					<p>
						<input type="text" name="post_title[{{data.index}}]" value="{{data.post.post_title}}">
					</p>
					<p>
						<input type="hidden" name="post_date[{{data.index}}]" value="{{data.post.post_date}}">
					</p>
					<p>
						<textarea name="post_content[{{data.index}}]">{{data.post.post_content}}</textarea>
					</p>
					<p>
						<a href="#" class="remove"><?php esc_html_e( 'Delete', $this->plugin_slug ); ?></a>
					</p>
				</fieldset>
			</div>
			<div class="fcm-children">
				<ul class="sortable connectable"></ul>
			</div>
		</li>
	</script>
	<?php
	}

	/**
	 * Print out search result templates i wp-template format for the available feature content panel.
	 *
	 * @since    0.1.0
	 */
	public function search_result_templates(){
	?>
		<script type="text/html" id="tmpl-featured-area-search-result-item">
			<li class="featured-area-search-result-item {{data.post_type}}" data-id="{{data.ID}}">
				<div class="featured-area-search-item-title">
					<h4>{{data.post_title}}</h4>
				</div>
				<div class="featured-area-search-item-content">
					{{data.post_content}}
				</div>
			</li>
		</script>
	<?php
	}
}
