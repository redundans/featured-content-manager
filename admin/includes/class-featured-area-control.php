<?php
/**
 * Featured Area Control.
 *
 * @package Featured_Content_Manager
 * @author  Jesper Nilsson <jesper@klandestino.se>
 */
class Featured_Area_Control extends WP_Customize_Control {
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
	 * @TODO: Clean up this mess.
	 *
	 * @since     0.1.0
	 */
	public function render_content() {
		$plugin = Featured_Content_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		?>
		<input id="<?php echo $this->section ?>" type="hidden" style="width:100%;" class="customizer-setting" <?php $this->link(); ?> value="<?php echo esc_textarea( $this->value() ); ?>">
		<p><?php _e('Drag and drop to reorder the featured items.', $this->plugin_slug ); ?></p>
		<?php
		$featured_items = Featured_Content_Manager::get_featured_content( (int)$this->area, array('publish','future') );
		$index = 1;
		echo '<ul class="featured-area sortable connectable" data-area="'. $this->area. '">';
		echo '<input type="hidden" name="action" value="fcm_save_posts">';
		echo '<input type="hidden" name="_wpnonce" value="'.wp_create_nonce('fcm_save_posts').'" />';
		echo '<input type="hidden" name="featured_area" value="'. $this->area. '" />';
		if ( $featured_items != null && $featured_items->have_posts() ) {
			while ( $featured_items->have_posts() ) {
				$featured_items->the_post();
														
				$child_markup = null;
				$post_thumbnail_id = get_post_thumbnail_id( get_the_id() );
				$post = get_post( get_the_id() );
				$post_thumbnail = ($post_thumbnail_id != '' ? get_post( $post_thumbnail_id ) : false);
						
				if($post_thumbnail) {
					$post_thumbnail->url = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
					$post_thumbnail->url = $post_thumbnail->url[0];
				}
						
				$post_original_id = get_post_meta( get_the_id(), 'cfm_post_parent', TRUE );

				$output = $this->render_featured_item( $index, $post, $post_original_id, 'false', $post_thumbnail );

				$children = get_children( array( 'post_parent' => get_the_id(), 'post_type' => Featured_Content_Manager::POST_TYPE, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ), ARRAY_A );

				$index++;
						
				foreach ( $children as $child ) {
					$child_post = get_post( $child["ID"] );
					$post_thumbnail_id = get_post_thumbnail_id( $child["ID"] );
					$post_thumbnail = ( $post_thumbnail_id != '' ? get_post( $post_thumbnail_id ) : false );
				
					if( $post_thumbnail ) {
						$post_thumbnail->url = wp_get_attachment_thumb_url( $post_thumbnail_id );
					}

					$child_markup[] = $this->render_featured_item( $index, $child_post, $post_original_id, 'true', $post_thumbnail );

					$index++;
				}
					
				if( $child_markup ){
					$output = str_replace('closed', 'closed parent', $output );
					$output = str_replace('<ul class="sortable connectable"></ul>', '<ul class="sortable connectable">'.implode( '', $child_markup ).'</ul>', $output);
				}
				print $output;

			}
		}
		echo '</ul>';
		echo '<p>';
		echo '<a class="add-featured-items button">' . __('Add item', $this->plugin_slug ) . '</a>';
		echo '<a class="remove-area left">' . __('Remove featured area', $this->plugin_slug ) . '</a>';
		echo '</p>';
	}

	/**
	 * Render each featured item.
	 *
	 * @TODO: Clean up this mess.
	 *
	 * @since     0.1.0
	 */
	public function render_featured_item( $index, $post, $post_original_id, $child, $post_thumbnail ){
		$output = '
			<li class="closed">
				<div class="fcm-title">
					<div class="sidebar-name-arrow"></div>
					<div class="sidebar-parent-arrow"></div>
					<h4>' . $post->post_title . '</h4>
				</div>
				<div class="fcm-inside">
					<fieldset name="post-' . $post->ID . '">';
		if( $post_thumbnail ) { 
			$output .= '<input type="hidden" name="post_thumbnail[' . $index . ']" value="' . $post_thumbnail->ID . '">';
		} else {
			$output .= '<input type="hidden" name="post_thumbnail[' . $index . ']" value="">';
		}
		$output .= '	<input type="hidden" name="area[' . $index . ']" value="{{term}}">
						<input type="hidden" name="menu_order[' . $index . ']" value="' . $post->menu_order . '">
						<input type="hidden" name="post_id[' . $index . ']" value="' .$post->ID .'">
						<input type="hidden" name="child[' . $index . ']" value="' . $child . '">
						<input type="hidden" name="post_original[' . $index . ']" value="' . $post_original_id .'">
						<div class="uploader">';
		if( $post_thumbnail ) {
			$output .= '	<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail"><img src="' . $post_thumbnail->url . '"></a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
							</p>';

		} else {
			$output .= '	<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail">' . __( 'Select thumbnail', $this->plugin_slug ) . '</a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" style="display: none;" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
							</p>';
		} 
		$output .= '	</div>
						<p>
							<input type="text" name="post_title[' . $index . ']" value="' . $post->post_title . '">
						</p>
						<p>
							<input type="text" name="post_date[' . $index . ']" value="' . $post->post_date . '">
						</p>
						<p>
							<textarea name="post_content[' . $index . ']">' . $post->post_content . '</textarea>
						</p>
						<p>
							<a href="#" class="remove">' . __( 'Delete', $this->plugin_slug ) . '</a>
						</p>
					</fieldset>
				</div>
				<div class="fcm-children">
					<ul class="sortable connectable"></ul>
				</div>
			</li>';
		return $output; 
	}
}