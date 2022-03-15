<?php

namespace Aipc;

if ( ! defined('ABSPATH') ) {
    die( 'ABSPATH is not defined! "Script didn\' run on Wordpress."' );
}

class Admin {

    protected static $instance = null;

    public function __construct () {
        // add_filter( 'woocommerce_get_sections_products', array( $this, 'aipc_add_section' ) );
        // add_filter( 'woocommerce_get_settings_products', array( $this, 'aipc_admin_settings' ), 10, 2 );
        $this->render_admin_page();
    }

    // public function aipc_admin_settings ( $settings, $current_section ) {
    //     var_dump( $settings );
    //     if ( $current_section !== 'aipcsettings' ) {
    //         return $settings;
    //     }
    //     // Title
    //     $settings[] = array( 
    //         'name' => __( 'AI Product Categories Settings', 'text-domain' ),
    //         'type' => 'title',
    //         // 'desc' => __( '', AIPC_TEXTDOMAIN ),
    //         'id' => 'aipc_settings',
    //     );
    //     // Enable/Disable Toggle
    //     $settings[] = array(
	// 		'name'     => __( 'Auto-insert into single product page', 'text-domain' ),
	// 		'desc_tip' => __( 'This will automatically insert your slider into the single product page', 'text-domain' ),
	// 		'id'       => 'wcslider_auto_insert',
	// 		'type'     => 'checkbox',
	// 		'css'      => 'min-width:300px;',
	// 		'desc'     => __( 'Enable Auto-Insert', 'text-domain' ),
	// 	);
    //     return $settings;
    // }

    // public function aipc_add_section ( $sections ) {
    //     $sections['aipcsettings'] = __( 'AI Categories Suggestions', AIPC_TEXTDOMAIN );
    //     return $sections;
    // }

    private function render_admin_page () {
        echo 'Working';
    }

    public static function init () {
        // If the single instance hasn't been set, set it now.
        if ( self::$instance == null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

}
