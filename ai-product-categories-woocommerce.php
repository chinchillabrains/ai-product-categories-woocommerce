<?php
/**
 * Plugin Name: AI Product Categories for Woocommerce
 * Description: Automatic product category suggestions for Woocommerce.
 * Version: 1.0.0
 * Author: codinghabits
 * Requires at least: 5.0
 * Author URI: https://coding-habits.com
 * Text Domain: ai-product-categories-woocommerce
 * Domain Path: /languages/
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'AI_Product_Categories' ) ) {
    define( 'AIPC_TEXTDOMAIN', 'ai-product-categories-woocommerce' );
    define( 'AIPC_PREFIX', 'aipc' );
    class AI_Product_Categories {

        // Instance of this class.
        protected static $instance = null;

        public function __construct() {

            // Load translation files
            // add_action( 'init', array( $this, 'add_translation_files' ) );

            // Admin page
            // add_action('admin_menu', array( $this, 'setup_menu' ));


            // Add settings link to plugins page
            // add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'add_settings_link' ) );

            // Register plugin settings fields
            // register_setting( AIPC_PREFIX . '_settings', AIPC_PREFIX . '_email_message', array('sanitize_callback' => array( 'AI_Product_Categories', 'sanitize_code' ) ) );

        }


        public static function sanitize_code( $input ) {        
            $sanitized = wp_kses_post( $input );
            if ( isset( $sanitized ) ) {
                return $sanitized;
            }
            
            return '';
        }

        public function add_translation_files () {
            load_plugin_textdomain( AIPC_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        public function setup_menu() {
            add_management_page(
                __( 'AI Product Categories', AIPC_TEXTDOMAIN ),
                __( 'AI Product Categories', AIPC_TEXTDOMAIN ),
                'manage_options',
                AIPC_PREFIX . '_settings_page',
                array( $this, 'admin_panel_page' )
            );
        }

        public function admin_panel_page(){
            require_once( __DIR__ . '/ai-product-categories-woocommerce.admin.php' );
        }

        public function add_settings_link( $links ) {
            $links[] = '<a href="' . admin_url( 'tools.php?page=' . AIPC_PREFIX . '_settings_page' ) . '">' . __('Settings') . '</a>';
            return $links;
        }

        // Return an instance of this class.
		public static function get_instance () {
			// If the single instance hasn't been set, set it now.
			if ( self::$instance == null ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

    }

    add_action( 'plugins_loaded', array( 'AI_Product_Categories', 'get_instance' ), 0 );

}
