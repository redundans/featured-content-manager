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
				'post_status' => 'publish',
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
		foreach ( $posts_array as $post ) {
			if ( wp_get_post_parent_id( $post->ID ) === 0 ) :
				$child_markup = null;
				$post = get_post( $post->ID );
				$post_original_id = get_post_meta( $post->ID, 'fcm_post_parent', true );
				$post_thumbnail = '';

				if ( has_post_thumbnail( $post ) ) {
					$post_thumbnail = get_post( get_post_thumbnail_id( $post ) );
					$post_thumbnail->url = set_url_scheme( get_the_post_thumbnail_url( $post, 'thumbnail' ) );
				} elseif ( fcm_is_multisite_elasticsearch_enabled() && $site_id = get_post_meta( $post->ID, 'fcm_site_id', true ) ) {
					switch_to_blog( $site_id );
					if ( has_post_thumbnail( $post_original_id ) ) {
						$post_thumbnail = get_post( get_post_thumbnail_id( $post_original_id ) );
						$post_thumbnail->url = set_url_scheme( get_the_post_thumbnail_url( $post_original_id, 'thumbnail' ) );
					}
					restore_current_blog();
				}

				$output = $this->render_featured_item( $index, $post, $post_original_id, 'false', $post_thumbnail );

				$children = get_children( array( 'post_parent' => $post->ID, 'post_type' => Featured_Content_Manager::POST_TYPE, 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ), ARRAY_A );

				$index++;

				foreach ( $children as $child ) {
					$child_post = get_post( $child['ID'] );
					$post_thumbnail_id = get_post_thumbnail_id( $child['ID'] );
					$post_thumbnail = ( $post_thumbnail_id != '' ? get_post( $post_thumbnail_id ) : false );
					$post_original_id = get_post_meta( $child_post->ID, 'fcm_post_parent', true );

					if ( $post_thumbnail ) {
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
	public function render_featured_item( $index, $post, $post_original_id, $child, $post_thumbnail ) {
		$styles = get_terms( Featured_Content_Manager::STYLE_TAXONOMY, array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
		$post_style = wp_get_post_terms( $post->ID, Featured_Content_Manager::STYLE_TAXONOMY );

		if ( 0 !== count( $post_style ) ) {
			$post_style = $post_style[0];
		} else {
			$post_style = null;
		}

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
		if ( $post_thumbnail ) {
			$output .= '<input type="hidden" name="post_thumbnail[' . $index . ']" value="' . $post_thumbnail->ID . '">';
		} else {
			$output .= '<input type="hidden" name="post_thumbnail[' . $index . ']" value="">';
		}
		$output .= '	<input type="hidden" name="area[' . $index . ']" value="{{term}}">
				<input type="hidden" name="menu_order[' . $index . ']" value="' . $post->menu_order . '">
				<input type="hidden" name="site_id[' . $index . ']" value="' . get_post_meta( $post->ID, 'fcm_site_id', true ) . '">
				<input type="hidden" name="post_id[' . $index . ']" value="' . $post->ID . '">
				<input type="hidden" name="child[' . $index . ']" value="' . $child . '">
				<input type="hidden" name="post_original[' . $index . ']" value="' . $post_original_id . '">';
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			if ( $post_thumbnail ) {
				if ( fcm_is_multisite_elasticsearch_enabled() && get_current_blog_id() != get_post_meta( $post->ID, 'fcm_site_id', true ) ) {
					$output .= '<p><img src="' . $post_thumbnail->url . '"></p>';
					$output .= '<p>Det går inte att ändra bilder från undersajter.</p>';
				} else {
					$output .= '
						<div class="uploader">
							<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail"><img src="' . $post_thumbnail->url . '"></a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
							</p>
						</div>';
				}
			} else {
				if ( fcm_is_multisite_elasticsearch_enabled() && get_current_blog_id() != get_post_meta( $post->ID, 'fcm_site_id', true ) ) {
					$output .= '<p>Det går inte att ändra bilder från undersajter.</p>';
				} else {
					$output .= '
						<div class="uploader">
							<p>
								<a href="#" title="Select thumbnail" class="edit-thumbnail">' . __( 'Select thumbnail', $this->plugin_slug ) . '</a>
							</p>
							<p>
								<a href="#" title="Delete thumbnail" style="display: none;" class="remove-thumbnail">' . __( 'Delete thumbnail', $this->plugin_slug ) . '</a>
							</p>
						</div>';
				}
			}
		}
		if ( ! empty( $styles ) ) {
			$output .= '<p><label>' . esc_html( 'Style', 'featured-content-manager' ) . '<select class="widefat" name="style[' . $index . ']">';
			foreach ( $styles as $style ) {
				if ( null !== $post_style && intval( $post_style->term_id ) === intval( $style->term_id ) ) {
					$output .= '<option value="' . $style->term_id . '" selected>' . $style->name . '</option>';
				} else {
					$output .= '<option value="' . $style->term_id . '">' . $style->name . '</option>';
				}
			}
			$output .= '</select></label><p>';
		}
		$output .= '
			<p>
				<label>' . esc_html_x( 'Title', '', 'featured-content-manager' ) . '<input type="text" name="post_title[' . $index . ']" value="' . esc_js( $post->post_title ) . '"></label>
			</p>
			<p>
				<input type="hidden" name="post_date[' . $index . ']" value="' . $post->post_date . '">
			</p>
			<p>
				<label>' . esc_html_x( 'Excerpt', '', 'featured-content-manager' ) . '<textarea name="post_content[' . $index . ']">' . $post->post_content . '</textarea></label>
			</p>';
		if ( get_post_meta( $post->ID, 'fcm_blurb', true ) ) :
			$output .=
			'<p>
				<label>' . esc_html_x( 'URL', '', 'featured-content-manager' ) . '<input type="url" name="url[' . $index . ']" value="' . get_post_meta( $post->ID, 'fcm_blurb_url', true ) . '"></label>
			</p>';
		endif;
		$output .=
			'<p>
				<a href="#" class="remove">' . __( 'Delete', 'featured-content-manager' ) . '</a>
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
