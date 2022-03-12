<?php
/**
 * Plugin Name: Plugin name here...
 * Description: Plugin description here...
 * Version: 1.0.0
 * Author: chinchillabrains
 * Requires at least: 5.0
 * Author URI: https://chinchillabrains.com
 * Text Domain: chilla-plugin-template
 * Domain Path: /languages/
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Chilla_Plugin_Template' ) ) {
    define( 'CHPT_TEXTDOMAIN', 'chilla-plugin-template' );
    define( 'CHPT_PREFIX', 'chpt' );
    class Chilla_Plugin_Template {

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
            // register_setting( CHPT_PREFIX . '_settings', CHPT_PREFIX . '_email_message', array('sanitize_callback' => array( 'Chilla_Plugin_Template', 'sanitize_code' ) ) );

        }


        public static function sanitize_code( $input ) {        
            $sanitized = wp_kses_post( $input );
            if ( isset( $sanitized ) ) {
                return $sanitized;
            }
            
            return '';
        }

        public function add_translation_files () {
            load_plugin_textdomain( CHPT_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        public function setup_menu() {
            add_management_page(
                __( 'Plugin Settings Title here...', CHPT_TEXTDOMAIN ),
                __( 'Plugin Settings Title here...', CHPT_TEXTDOMAIN ),
                'manage_options',
                CHPT_PREFIX . '_settings_page',
                array( $this, 'admin_panel_page' )
            );
        }

        public function admin_panel_page(){
            require_once( __DIR__ . '/chilla-plugin-template.admin.php' );
        }

        public function add_settings_link( $links ) {
            $links[] = '<a href="' . admin_url( 'tools.php?page=' . CHPT_PREFIX . '_settings_page' ) . '">' . __('Settings') . '</a>';
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

    add_action( 'plugins_loaded', array( 'Chilla_Plugin_Template', 'get_instance' ), 0 );

}
