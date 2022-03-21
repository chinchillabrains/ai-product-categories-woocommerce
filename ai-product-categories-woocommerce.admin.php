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
        $gather_data_btn_label = __( 'Gather Data', AIPC_TEXTDOMAIN );
        $disabled_suggestions_html = self::get_disabled_products_widget();
        $status = self::get_service_status();
        $gather_data_class = ( as_has_scheduled_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' ) ? 'aipc-settings__gatherdataButton--disabled' : '' );
        $extra_status_txt = '';
        if ( ! empty( $status['extra_txt'] ) ) {
            $extra_status_txt = '<small class="aipc-settings__systemstatusLabelExtra"><em> (' . esc_html( $status['extra_txt'] ) . ')</em></small>';
        }

        ?>
        <div class="aipc-settings__outer">
            <div class="aipc-settings">
                <h2 class="aipc-settings__title"><?= __( 'AI Product Categories Settings', AIPC_TEXTDOMAIN ) ?></h2>
                <div class="aipc-settings__gatherdata">
                    <h4 class="aipc-settings__gatherdataTitle"><?= __( 'Gather Data', AIPC_TEXTDOMAIN ) ?></h4>
                    <p>
                        <button class="aipc-settings__gatherdataButton <?= esc_html( $gather_data_class ) ?>"><?= $gather_data_btn_label ?></button>
                    </p>
                </div>
                <div class="aipc-settings__disabledsuggestions">
                    <h4 class="aipc-settings__disabledsuggestionsTitle"><?= __( 'Disabled Suggestions', AIPC_TEXTDOMAIN ) ?></h4>
                    <?= $disabled_suggestions_html ?>
                </div>
                <div class="aipc-settings__systemstatus">
                    <h4 class="aipc-settings__systemstatusTitle"><?= __( 'System Status', AIPC_TEXTDOMAIN ) ?></h4>
                    <p class="aipc-settings__systemstatusLabel aipc-<?= esc_attr( $status['color'] ) ?>"><?= esc_html( $status['label'] ) . $extra_status_txt ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_disabled_products_widget () {
        $product_ids = get_option( 'aipc_product_ids_to_skip' );
        if ( empty( $product_ids ) ) {
            return __( 'List is empty.', AIPC_TEXTDOMAIN );
        }
        $products = [];
        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( ! empty( $product ) ) {
                $products[] = [
                    'id'    => $product->get_id(),
                    'title' => $product->get_title(),
                ];
            }
        }
        if ( empty( $products ) ) {
            return __( 'List is empty.', AIPC_TEXTDOMAIN );
        }
        $ret_html = '<ul class="aipc-settings__disabledsuggestionsList">';
        foreach ( $products as $product ) {
            $ret_html .= "<li data-id=\"{$product['id']}\" class=\"aipc-settings__disabledsuggestionsItem\"><span>{$product['title']}</span> <span class=\"aipc-settings__disabledsuggestionsEnable\"><span></span><span></span></span></li>";
        }
        $ret_html .= '</ul>';
        return $ret_html;
    }

    private static function get_service_status () {
        $gathered_data = get_option( 'aipc_data_gathered' );
        $pending_categories = get_option( 'aipc_categories_to_gather' );
        $pending_categories_count = count( $pending_categories );
        $has_gathered_data = ( empty( $gathered_data ) ? false : true );
        $is_gathering_data = ( as_has_scheduled_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' ) ? true : false );

        // If options are initialized, service is Live / Gathering data - green
        // If options are not initialized, service is Starting / Gathering data - orange
        // If options are not initialized, service is Off - red

        if ( $has_gathered_data ) {
            if ( $is_gathering_data ) {
                // Set count = 1 if is gathering data & count == 0 (It means the last action is in progress)
                $pending_categories_count = ( $pending_categories_count == 0 ? 1 : $pending_categories_count );
                $service = [
                    'color'         => 'green',
                    'label'         => __( 'Live / Gathering Data', AIPC_TEXTDOMAIN ),
                    'extra_txt'     =>  sprintf( __( 'Categories remaining: %d' ), $pending_categories_count ),
                ];
            } else {
                $service = [
                    'color' => 'green',
                    'label' => __( 'Live', AIPC_TEXTDOMAIN ),
                ];
            }
        } else {
            if ( $is_gathering_data ) {
                $service = [
                    'color'     => 'orange',
                    'label'     => __( 'Gathering Data', AIPC_TEXTDOMAIN ),
                    'extra_txt' =>  sprintf( __( 'Categories remaining: %d' ), $pending_categories_count ),
                ];
            } else {
                $service = [
                    'color'     => 'red',
                    'label'     => __( 'Off', AIPC_TEXTDOMAIN ),
                    'extra_txt' => __( 'click', AIPC_TEXTDOMAIN ) . '"' . __( 'Gather Data', AIPC_TEXTDOMAIN ) . '"',
                ];
            }
        }
        return $service;
    }

    public static function init () {
        // If the single instance hasn't been set, set it now.
        if ( self::$instance == null ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

}
