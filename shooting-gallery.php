<?php if(!defined('ABSPATH')) { die(); } // Include in all php files, to prevent direct execution
/**
 * Plugin Name: Shooting Gallery
 * Description: A sweet little gallery plugin
 * Author:
 * Author URI:
 * Version: 0.1.0
 * Text Domain: shooting-gallery
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 **/

include( plugin_dir_path( __FILE__ ) . 'resources/bytes_image_uploader/bbytes_image_uploader.php');
if( !class_exists('ShootingGallery') ) {
	class ShootingGallery {
		private static $version = '0.1.0';
		private static $_this;
		private $settings;

		public static function Instance() {
			static $instance = null;
			if ($instance === null) {
				$instance = new self();
			}
			return $instance;
		}

		private function __construct() {
			register_activation_hook( __FILE__, array( $this, 'register_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'register_deactivation' ) );
			// Stuff that happens on every page load, once the plugin is active
			$this->initialize_settings();
			if( is_admin() && !( defined('DOING_AJAX') && DOING_AJAX ) ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
				add_action( 'save_post', array( $this, 'save_post' ) );
			} else {
				add_filter( 'the_content', array( $this, 'the_content') );
				add_shortcode( 'shooting-gallery', array( $this, 'sg_shortcode' ) );
			}
		}
		// PUBLIC STATIC FUNCTIONS
		public static function get_version() {
			return ShootingGallery::$version;
		}
		// PRIVATE STATIC FUNCTIONS

		// PUBLIC FUNCTIONS
		public function the_content($content){
			return $content;
		}
		public function register_activation() {
			// Stuff that only has to run once, on plugin activation
		}
		public function register_deactivation() {
			// Clean up on deactivation
		}
		public function admin_init() {
			// Register Settings Here
		}
		public function admin_menu() {
			add_options_page(
				__( 'Shooting Gallery Settings', 'shooting-gallery' ),
				__( 'Shooting Gallery', 'shooting-gallery' ),
				'manage_options',
				'shooting-gallery-admin',
				array( $this, 'options_page_callback' )
			);
		}
		public function options_page_callback() {
			// TODO: Implement options page
		}
		public function add_meta_boxes() {
			$post_types = $this->get_setting('post_types');
			foreach( $post_types as $type ) {
				add_meta_box(
					'shooting_gallery_metabox',
					__( 'Shooting Gallery', 'shooting-gallery' ),
					array( $this, 'shooting_gallery_metabox' ),
					$type
				);
			}
		}
		public function shooting_gallery_metabox( $post ) {
			wp_nonce_field( 'shooting_gallery_metabox', 'shooting_gallery_metabox_nonce' );
			echo '<p>These images will be included in the gallery. use the [shooting-gallery] shortcode to place it in the body, rather than before it.</p>';
			$images = get_post_meta($post->ID, 'gallery_images', true);
			echo bbytes_render_image_uploader('gallery_images',$images,3);
			// TODO: render the shooting gallery metabox

		}
		public function save_post( $post_id, $post ) {
			if (!isset($_POST["shooting_gallery_metabox_nonce"]) || !wp_verify_nonce($_POST["shooting_gallery_metabox_nonce"], basename("shooting_gallery_metabox")))
				return $post_id;
			if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
				        return;
 			if (defined('DOING_AJAX') && DOING_AJAX)
				        return;
			if (!current_user_can( 'edit_post' , $post_id ) )
				return $post_id;
			$gallery_images = '';
			if (isset($_POST['gallery_images']) && $_POST['gallery_images'])
				$gallery_images = $_POST['gallery_images'];

			$current_gallery_images = get_post_meta( $post_id, 'gallery_images', true);
			if ( $gallery_images && '' == $current_gallery_images)
				add_post_meta( $post_id, 'gallery_images', true);
			elseif ( $gallery_images && $gallery_images != $current_gallery_images)
				update_post_meta($post_id, "gallery_images", $gallery_images);
			elseif ( '' == $gallery_images && $current_gallery_images)
				delete_post_meta( $post_id, "gallery_images",  $current_gallery_images);
			// TODO: save the metabox data
		}
		public function sg_shortcode( $atts, $content ) {

			$gal_images = get_post_meta( get_the_ID(), 'gallery_images', true);// TODO: implement shortcode
			return print_r($gal_images, true);
		}
		// PRIVATE FUNCTIONS
		private function initialize_settings() {
			$default_settings = array(
				'post_types' => array( 'post', 'page' ),
			);
			$this->settings = get_option( 'ShootingGallery_options', $default_settings );
		}
		private function get_setting( $key ) {
			if( $key && isset( $this->settings[$key] ) ) {
				return $this->settings[$key];
			}
			return null;
		}
	}
	ShootingGallery::Instance();
}
