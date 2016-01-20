<?php
/**
 * Featured Area Control.
 *
 * @package Featured_Content
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

		$posts_array = get_posts(
			array(
				'post_type' => 'featured_item',
				'tax_query' => array(
					array(
						'taxonomy' => 'featured_area',
						'field' => 'id',
						'terms' => $this->area
					)
				),
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			)
		);
		foreach ($posts_array as $post) {
			if( wp_get_post_parent_id($post->ID) === 0 ):
				$child_markup = null;
				$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
				$post = get_post( $post->ID );
				$post_thumbnail = ($post_thumbnail_id != '' ? get_post( $post_thumbnail_id ) : false);

				if($post_thumbnail) {
					$post_thumbnail->url = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
					$post_thumbnail->url = $post_thumbnail->url[0];
				}

				$post_original_id = get_post_meta( $post->ID, 'fcm_post_parent', TRUE );

				$output = $this->render_featured_item( $index, $post, $post_original_id, 'false', $post_thumbnail );

				$children = get_children( array( 'post_parent' => $post->ID, 'post_type' => Featured_Content_Manager::POST_TYPE, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ), ARRAY_A );

				$index++;

				foreach ( $children as $child ) {
					$child_post = get_post( $child["ID"] );
					$post_thumbnail_id = get_post_thumbnail_id( $child["ID"] );
					$post_thumbnail = ( $post_thumbnail_id != '' ? get_post( $post_thumbnail_id ) : false );
					$post_original_id = get_post_meta( $child_post->ID, 'fcm_post_parent', TRUE );

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
			endif;
		}
		echo '</ul>';
		echo '<p>';
		echo '<a class="add-featured-items button">' . __('Add item', $this->plugin_slug ) . '</a>';
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
		$styles = get_terms( Featured_Content_Manager::STYLE_TAXONOMY, array('hide_empty' => 0) );
		$post_style = wp_get_post_terms($post->ID, Featured_Content_Manager::STYLE_TAXONOMY);

		if ( count($post_style)!=0 )
			$post_style = $post_style[0];
		else
			$post_style = null;

		$output = '
			<li class="closed">
				<div class="fcm-title">
					<div class="sidebar-name-arrow"><span class="toggle-indicator" aria-hidden="true"></span></div>
					<div class="sidebar-parent-arrow"></div>
					<div class="sidebar-delete-icon"></div>
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
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			if( $post_thumbnail ) {
				$output .= '	<p>
									<a href="#" title="Select thumbnail" class="edit-thumbnail"><img src="' . $post_thumbnail->url . '"></a>
								</p>
								<p>
									<a href="#" title="Delete thumbnail" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
								</p>
							</div>';

			} else {
				$output .= '	<p>
									<a href="#" title="Select thumbnail" class="edit-thumbnail">' . __( 'Select thumbnail', $this->plugin_slug ) . '</a>
								</p>
								<p>
									<a href="#" title="Delete thumbnail" style="display: none;" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
								</p>
							</div>';
			}
		}
		if ( ! empty( $styles ) ) {
			$output .= '<p><select class="widefat" name="style[' . $index . ']">';
			foreach ( $styles as $style ) {
				if ( $post_style != null && intval($post_style->term_id) === intval($style->term_id) )
					$output .= '<option value="' . $style->term_id . '" selected>' . $style->name . '</option>';
				else
					$output .= '<option value="' . $style->term_id . '">' . $style->name . '</option>';
			}
			$output .= '</select><p>';
		}
		$output .= '	<p>
							<input type="text" name="post_title[' . $index . ']" value="' . esc_js( $post->post_title ) . '">
						</p>
						<p>
							<input type="hidden" name="post_date[' . $index . ']" value="' . $post->post_date . '">
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