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
			// TODO: render the shooting gallery metabox

		}
		public function save_post( $post_id ) {
			// TODO: save the metabox data
		}
		public function sg_shortcode( $atts, $content ) {
			// TODO: implement shortcode
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
				return $this->settings['key'];
			}
			return null;
		}
	}
	ShootingGallery::Instance();
}