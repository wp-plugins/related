<?php
/*
Plugin Name: Related (Doubled Up)
Plugin URI: http://products.zenoweb.nl/free-wordpress-plugins/related/
Description: Partnering plugin of Related, for building a second list of related posts. It requires the main Related plugin to be activated.
Version: 2.0.6
Author: Marcel Pol
Author URI: http://zenoweb.nl
Text Domain: related
Domain Path: /lang/


Copyright 2010-2012  Matthias Siegel  (email: matthias.siegel@gmail.com)
Copyright 2013-2015  Marcel Pol       (email: marcel@timelord.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if (!class_exists('Related_du')) :
	class Related_du {

		/*
		 * __construct
		 * Constructor
		 */
		public function __construct() {

			// Set some helpful constants
			$this->defineConstants();

			/* Test if the main Related plugin is activated. */
			$main_plugin = plugin_basename( dirname(__FILE__) . '/related.php' );
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( !is_plugin_active($main_plugin) ) {
				// Not active, deactivate this one again.
				deactivate_plugins( plugin_basename( dirname(__FILE__) . '/related_du.php' ) );
			}

			// Register hook to save the related posts when saving the post
			add_action('save_post', array(&$this, 'save'));

			// Start the plugin
			add_action('admin_menu', array(&$this, 'start'));

			// Add the related posts to the content, if set in options
			add_filter( 'the_content', array($this, 'related_content_filter') );
		}


		/*
		 * defineConstants
		 * Defines a few static helper values we might need
		 */
		protected function defineConstants() {
			define('RELATED_DU_FILE', plugin_basename(dirname(__FILE__)));
			define('RELATED_DU_ABSPATH', str_replace('\\', '/', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__))));
		}


		/*
		 * start
		 * Main function
		 */
		public function start() {

			// Load the scripts
			add_action('admin_enqueue_scripts', array(&$this, 'loadScripts'));

			// Adds a meta box for related posts to the edit screen of each post type in WordPress
			$related_show = get_option('related_du_show');
			$related_show = json_decode( $related_show );
			if ( empty( $related_show ) ) {
				$related_show = array();
				$related_show[] = 'any';
			}
			if ( in_array( 'any', $related_show ) ) {
				foreach (get_post_types() as $post_type) :
					add_meta_box($post_type . '-related_du-posts-box', __('Related posts (Doubled Up)', 'related' ), array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
				endforeach;
			} else {
				foreach ($related_show as $post_type) :
					add_meta_box($post_type . '-related_du-posts-box', __('Related posts (Doubled Up)', 'related' ), array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
				endforeach;
			}

		}


		/*
		 * loadScripts
		 * Load Javascript
		 */
		public function loadScripts() {
			wp_enqueue_script('related_du-scripts', RELATED_URLPATH .'/scripts_du.js', false, RELATED_VERSION, true);
		}


		/*
		 * save
		 * Save related posts when saving the post
		 */
		public function save($id) {
			global $pagenow;

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if ( isset($_POST['related_du-posts']) ) {
				update_post_meta($id, 'related_du_posts', $_POST['related_du-posts']);
			}
			/* Only delete on post.php page, not on Quick Edit. */
			if ( empty($_POST['related_du-posts']) ) {
				if ( $pagenow == 'post.php' ) {
					delete_post_meta($id, 'related_du_posts');
				}
			}
		}


		/*
		 * displayMetaBox
		 * Creates the output on the post screen
		 */
		public function displayMetaBox() {
			global $post;

			$post_id = $post->ID;

			echo '<p>' . __('Choose related posts. You can drag-and-drop them into the desired order:', 'related' ) . '</p><div id="related_du-posts">';

			// Get related posts if existing
			$related = get_post_meta($post_id, 'related_du_posts', true);

			if (!empty($related)) :
				foreach($related as $r) :
					$p = get_post($r);


					echo '
						<div class="related_du-post" id="related_du-post-' . $r . '">
							<input type="hidden" name="related_du-posts[]" value="' . $r . '">
							<span class="related_du-post-title">' . $p->post_title . ' (' . ucfirst(get_post_type($p->ID)) . ')</span>
							<a href="#">' . __('Delete', 'related' ) . '</a>
						</div>';
				endforeach;
			endif;

			/* First option should be empty with a data placeholder for text.
			 * The jQuery call allow_single_deselect makes it possible to empty the selection
			 */
			echo '
				</div>
				<p>
					<select class="related_du-posts-select chosen-select" name="related_du-posts-select" data-placeholder="' . __('Choose a related post... ', 'related' ) . '">';

			echo '<option value="0"></option>';


			$related_list = get_option('related_du_list');
			$related_list = json_decode( $related_list );

			if ( empty( $related_list ) || in_array( 'any', $related_list ) ) {
				// list all the post_types
				$related_list = array();

				$post_types = get_post_types( '', 'names' );
				foreach ( $post_types as $post_type ) {
					if ( $post_type == "revision" || $post_type == "nav_menu_item" ) {
						continue;
					}
					$related_list[] = $post_type;
				}
			}

			foreach ( $related_list as $post_type ) {

				echo '<optgroup label="'. $post_type .'">';

				/* Use suppress_filters to support WPML, only show posts in the right language. */
				$r = array(
					'nopaging' => true,
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_type' => $post_type,
					'suppress_filters' => 0,
					'post_status' => 'publish, inherit',
				);

				$posts = get_posts( $r );

				if ( ! empty( $posts ) ) {
					$args = array($posts, 0, $r);

					$walker = new Walker_RelatedDropdown;
					echo call_user_func_array( array( $walker, 'walk' ), $args );
				}

				echo '</optgroup>';

			} // endforeach

			wp_reset_query();
			wp_reset_postdata();

			echo '
					</select>
				</p>';

		}


		/*
		 * show
		 * The frontend function that is used to display the related post list
		 */
		public function show( $id, $return = false ) {

			global $wpdb;

			/* Compatibility for Qtranslate, Qtranslate-X and MQtranslate, and the get_permalink function */
			$plugin = "qtranslate/qtranslate.php";
			$q_plugin = "qtranslate-x/qtranslate.php";
			$m_plugin = "mqtranslate/mqtranslate.php";
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active($plugin) || is_plugin_active($q_plugin) || is_plugin_active($m_plugin) ) {
				add_filter('post_type_link', 'qtrans_convertURL');
			}

			if (!empty($id) && is_numeric($id)) :
				$related = get_post_meta($id, 'related_du_posts', true);

				if (!empty($related)) :
					$rel = array();
					foreach ($related as $r) :
						$p = get_post($r);
						$rel[] = $p;
					endforeach;

					// If value should be returned as array, return it
					if ($return) :
						return $rel;

					// Otherwise return a formatted list
					else :
						if ( is_array( $rel ) && count( $rel ) > 0 ) {
							$list = '<ul class="related_du-posts">';
							foreach ($rel as $r) :
								if ( is_object( $r ) ) {
									if ($r->post_status != 'trash') {
										$list .= '<li><a href="' . get_permalink($r->ID) . '">' . get_the_title($r->ID) . '</a></li>';
									}
								}
							endforeach;
							$list .= '</ul>';

							return $list;
						}
					endif;
				else :
					return false;
				endif;
			else :
				return __('Invalid post ID specified', 'related' );
			endif;
		}


		/*
		 * Add the plugin data to the content, if it is set in the options.
		 */
		public function related_content_filter( $content ) {
			if ( (get_option( 'related_du_content', 0 ) == 1 && is_singular()) || get_option( 'related_du_content_all', 0 ) == 1 ) {
				global $related_du;
				$related_posts = $related_du->show( get_the_ID() );
				if ( $related_posts ) {
					$content .= '<div class="related_du_content" style="clear:both;">';
					$content .= '<h3 class="widget-title">';
					$content .= stripslashes(get_option('related_du_content_title', __('Related Posts', 'related')));
					$content .= '</h3>';
					$content .= $related_posts;
					$content .= "</div>";
				}
			}
			// otherwise returns the old content
			return $content;
		}

	}

endif;


/*
 * related_links
 * Add Settings link to the main plugin page
 *
 */

function related_du_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/related_du.php' ) ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=related_du.php' ) . '">'.__( 'Settings', 'related' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'related_du_links', 10, 2 );


/* Include Settings page */
include( 'page-related_du.php' );

/* Include widget */
include( 'related_du-widget.php' );


/*
 * related_init
 * Function called at initialisation.
 * - Loads language files
 * - Make an instance of Related()
 */

function related_du_init() {
	// Start the plugin
	global $related_du;
	$related_du = new Related_du();
}
add_action('plugins_loaded', 'related_du_init');


