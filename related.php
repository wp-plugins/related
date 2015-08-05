<?php
/*
Plugin Name: Related
Plugin URI: http://products.zenoweb.nl/free-wordpress-plugins/related/
Description: A simple 'related posts' plugin that lets you select related posts manually.
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



if (!class_exists('Related')) :
	class Related {

		/*
		 * __construct
		 * Constructor
		 */
		public function __construct() {

			// Set some helpful constants
			$this->defineConstants();

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
			define('RELATED_VERSION', '2.0.6');
			define('RELATED_HOME', 'http://zenoweb.nl');
			define('RELATED_FILE', plugin_basename(dirname(__FILE__)));
			define('RELATED_ABSPATH', str_replace('\\', '/', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__))));
			define('RELATED_URLPATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
		}


		/*
		 * start
		 * Main function
		 */
		public function start() {

			// Load the scripts
			add_action('admin_enqueue_scripts', array(&$this, 'loadScripts'));

			// Load the CSS
			add_action('admin_enqueue_scripts', array(&$this, 'loadCSS'));

			// Adds a meta box for related posts to the edit screen of each post type in WordPress
			$related_show = get_option('related_show');
			$related_show = json_decode( $related_show );
			if ( empty( $related_show ) ) {
				$related_show = array();
				$related_show[] = 'any';
			}
			if ( in_array( 'any', $related_show ) ) {
				foreach (get_post_types() as $post_type) :
					add_meta_box($post_type . '-related-posts-box', __('Related posts', 'related' ), array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
				endforeach;
			} else {
				foreach ($related_show as $post_type) :
					add_meta_box($post_type . '-related-posts-box', __('Related posts', 'related' ), array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
				endforeach;
			}

		}


		/*
		 * loadScripts
		 * Load Javascript
		 */
		public function loadScripts() {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('related-scripts', RELATED_URLPATH .'/scripts.js', false, RELATED_VERSION, true);
			wp_enqueue_script('related-chosen', RELATED_URLPATH .'/chosen/chosen.jquery.min.js', false, RELATED_VERSION, true);
		}


		/*
		 * loadCSS
		 * Load CSS
		 */
		public function loadCSS() {
			wp_enqueue_style('related-css', RELATED_URLPATH .'/styles.css', false, RELATED_VERSION, 'all');
			wp_enqueue_style('related-css-chosen', RELATED_URLPATH .'/chosen/chosen.min.css', false, RELATED_VERSION, 'all');
		}


		/*
		 * save
		 * Save related posts when saving the post
		 */
		public function save($id) {
			global $pagenow;

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if ( isset($_POST['related-posts']) ) {
				update_post_meta($id, 'related_posts', $_POST['related-posts']);
			}
			/* Only delete on post.php page, not on Quick Edit. */
			if ( empty($_POST['related-posts']) ) {
				if ( $pagenow == 'post.php' ) {
					delete_post_meta($id, 'related_posts');
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

			echo '<p>' . __('Choose related posts. You can drag-and-drop them into the desired order:', 'related' ) . '</p><div id="related-posts">';

			// Get related posts if existing
			$related = get_post_meta($post_id, 'related_posts', true);

			if (!empty($related)) :
				foreach($related as $r) :
					$p = get_post($r);


					echo '
						<div class="related-post" id="related-post-' . $r . '">
							<input type="hidden" name="related-posts[]" value="' . $r . '">
							<span class="related-post-title">' . $p->post_title . ' (' . ucfirst(get_post_type($p->ID)) . ')</span>
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
					<select class="related-posts-select chosen-select" name="related-posts-select" data-placeholder="' . __('Choose a related post... ', 'related' ) . '">';

			echo '<option value="0"></option>';


			$related_list = get_option('related_list');
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
				$related = get_post_meta($id, 'related_posts', true);

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
							$list = '<ul class="related-posts">';
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
			if ( (get_option( 'related_content', 0 ) == 1 && is_singular()) || get_option( 'related_content_all', 0 ) == 1 ) {
				global $related;
				$related_posts = $related->show( get_the_ID() );
				if ( $related_posts ) {
					$content .= '<div class="related_content" style="clear:both;">';
					$content .= '<h3 class="widget-title">';
					$content .= stripslashes(get_option('related_content_title', __('Related Posts', 'related')));
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


/**
 * Create HTML dropdown list of hierarchical post_types.
 * Returns the list of <option>'s for the select dropdown.
 */
class Walker_RelatedDropdown extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page   Page data object.
	 * @param int    $depth  Depth of page in reference to parent pages. Used for padding.
	 * @param int $id
	 */
	public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $page->ID ) . "\">";

		$title = $page->post_title;
		if ( '' === $title ) {
			$title = sprintf( __( '#%d (no title)', 'related' ), $page->ID );
		}

		/**
		 * Filter the page title when creating an HTML drop-down list of pages.
		 *
		 * @since 3.1.0
		 *
		 * @param string $title Page title.
		 * @param object $page  Page data object.
		 */
		$title = apply_filters( 'list_pages', $title, $page );
		$output .= $pad . esc_html( $title );
		$output .= "</option>\n";
	}
}


/*
 * related_links
 * Add Settings link to the main plugin page
 *
 */

function related_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/related.php' ) ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=related.php' ) . '">'.__( 'Settings', 'related' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'related_links', 10, 2 );


/* Include Settings page */
include( 'page-related.php' );

/* Include widget */
include( 'related-widget.php' );


/*
 * related_init
 * Function called at initialisation.
 * - Loads language files
 * - Make an instance of Related()
 */

function related_init() {
	load_plugin_textdomain('related', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');

	// Start the plugin
	global $related;
	$related = new Related();
}
add_action('plugins_loaded', 'related_init');

