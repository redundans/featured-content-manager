<?php
/**
 * Create Featured Area Control.
 *
 * @package Featured_Content_Manager
 * @author  Jesper Nilsson <jesper@klandestino.se>
 */
class Create_Featured_Area_Control extends WP_Customize_Control {
	/**
	 * Controller Section.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	public $section;

	/**
	 * Controller Area.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	public $area;

	/**
	 * Plugin slug.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	public $plugin_slug;

	/**
	 * Render the controller form.
	 *
	 * @since     0.1.0
	 */
	public function render_content() {
		$plugin = Featured_Content_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		?>
		<label for="<?php echo $this->section ?>">
			<span class="customize-control-title"><?php _e( 'Title', $this->plugin_slug ) ?></span>
			<input id="featured-area-title" type="text" style="width:100%;" class="customizer-setting" <?php $this->link(); ?> value="<?php echo esc_textarea( $this->value() ); ?>">
		</label>
		<p>
			<span id="create-fatured-area" class="button-secondary right" tabindex="0"><?php _e( 'Create', $this->plugin_slug ) ?></span>
		</p>
		<?php
	}
}