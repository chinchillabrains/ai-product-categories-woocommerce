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

use Aipc\Tools as Tools;
use Aipc\Admin as Admin;

if ( ! class_exists( 'AI_Product_Categories' ) ) {

    require_once( __DIR__ . '/ai-product-categories-woocommerce.admin.php' );

    require_once( __DIR__ . '/Tools.class.php' );

    define( 'AIPC_TEXTDOMAIN', 'ai-product-categories-woocommerce' );
    define( 'AIPC_PREFIX', 'aipc' );

    class AI_Product_Categories {

        // Instance of this class.
        protected static $instance = null;

        public function __construct() {

            if ( ! class_exists( 'woocommerce' ) ) {
                exit;
            }

            // Load translation files
            // add_action( 'init', array( $this, 'add_translation_files' ) );

            // Admin page
            add_action('admin_menu', array( $this, 'setup_menu' ));


            // Add settings link to plugins page
            // add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'add_settings_link' ) );

            // Register plugin settings fields
            // register_setting( AIPC_PREFIX . '_settings', AIPC_PREFIX . '_email_message', array('sanitize_callback' => array( 'AI_Product_Categories', 'sanitize_code' ) ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

            add_action( 'aipc_gather_categories_data', array( $this, 'gather_data_init' ) );

            add_action( 'aipc_process_gathering', array( $this, 'gather_batch_data' ) );

            add_action( 'aipc_data_gathering_finished', array( $this, 'gather_data_finish' ) );

            add_filter( 'aipc_skip_product', array( '\Aipc\Tools', 'skip_suggestions_for_product' ), 10, 2 );

            add_action( 'admin_head', array( $this, 'add_product_to_skip_list' ) );
        }

        public function add_product_to_skip_list () {
            if ( ! isset( $_GET['aipc-product-add-skip-list'] ) ) {
                return;
            }
            $product_id = intval( $_GET['aipc-product-add-skip-list'] );
            $ids_to_skip = get_option( 'aipc_product_ids_to_skip' );
            $ids_to_skip[] = $product_id;
            update_option( 'aipc_product_ids_to_skip', $ids_to_skip );
        }

        public function enqueue_admin_scripts( $screen ) {
            global $post;
            if ( empty( $post ) ) {
                return;
            }
            $screen = get_current_screen();
            if ( 'post' === $screen->base && 'product' === $post->post_type ) {
                // Add parent names to $suggestions
                $suggestions = Tools::get_category_suggestions( $post->ID );
                $skip_suggestions = apply_filters( 'aipc_skip_product', $post->ID, $suggestions );
                if ( $skip_suggestions ) {
                    return;
                }
                $plugin_data = [
                    'categories'            => $suggestions,
                    'listTitle'             => esc_html__( 'Suggested Categories', AIPC_TEXTDOMAIN ),
                    'skipSuggestionsLabel'  => esc_html__( 'Disable suggestions for this product', AIPC_TEXTDOMAIN ),
                    'productId'             => $post->ID,
                    'adminUrl'              => admin_url(),
                ];
                wp_enqueue_style( AIPC_TEXTDOMAIN . '-admin-stylesheet', plugins_url( 'assets/css/style.css', __FILE__ ) );
                wp_enqueue_script( AIPC_TEXTDOMAIN . '-admin-script', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ) );
                wp_localize_script( AIPC_TEXTDOMAIN . '-admin-script', 'aipcplugin', $plugin_data );
            }
        }



        public function gather_batch_data () {
            $pending_categories = get_option( 'aipc_categories_to_gather' );
            if ( empty( $pending_categories ) ) {
                do_action( 'aipc_data_gathering_finished' );
                return;
            }
            // Get products from next category & add them to pending_products
            $category_id = $pending_categories[0];
            $product_titles = Tools::get_product_titles_of_category( $category_id );
            $product_count = count( $product_titles );
            $bag_of_words = Tools::generate_bag_of_words( $product_titles );
            unset( $product_titles );

            $current_categories_data = get_option( 'aipc_data_gathering' );
            $current_categories_data[] = [
                'category_id'   => $category_id,
                'bag_of_words'  => $bag_of_words,
                'product_count' => $product_count,
            ];
            $option_updated = update_option( 'aipc_data_gathering', $current_categories_data );
            if ( $option_updated === false ) {
                error_log( 'update_option aipc_data_gathering FAILED!' );
            }
            unset( $pending_categories[0] );
            $pending_categories = array_values( $pending_categories );
            update_option( 'aipc_categories_to_gather', $pending_categories );
            
            as_enqueue_async_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' );

        }

        public function gather_data_init () {
            if ( ! as_has_scheduled_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' ) ) {
                //
                $terms = get_terms(
                    array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                    )
                );
                $categories = [];
                foreach ( $terms as $term ) {
                    if ( 'uncategorized' === $term->slug || 15 === $term->term_id ) {
                        continue;
                    }
                    $categories[] = $term->term_id;
                }
                update_option( 'aipc_categories_to_gather', $categories );
                as_enqueue_async_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' );
            }
        }

        public function gather_data_finish () {
            // Update data on aipc_data_gathered option & clear data gathering option 
            $current_categories_data = get_option( 'aipc_data_gathering' );
            update_option( 'aipc_data_gathered', $current_categories_data );
            update_option( 'aipc_data_gathering', [] );
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
                __( 'AI Categories', AIPC_TEXTDOMAIN ),
                __( 'AI Categories', AIPC_TEXTDOMAIN ),
                'manage_options',
                AIPC_PREFIX . '_settings_page',
                array( '\Aipc\Admin', 'init' )
            );
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

        public static function plugin_activated () {
            $hours_to_repeat = 24;
            as_schedule_recurring_action( time(), $hours_to_repeat * 60 * 60, 'aipc_gather_categories_data', [], 'ai-product-categories-woocommerce' );
        }

        public static function plugin_deactivated () {
            as_unschedule_all_actions( 'aipc_gather_categories_data', [], 'ai-product-categories-woocommerce' );
            as_unschedule_all_actions( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' );
        }

    }

    add_action( 'plugins_loaded', array( 'AI_Product_Categories', 'get_instance' ), 0 );

    register_activation_hook( __FILE__, ['AI_Product_Categories', 'plugin_activated'] );
    register_deactivation_hook( __FILE__, ['AI_Product_Categories', 'plugin_deactivated'] );

}
