<?php

namespace Aipc;

if ( ! defined('ABSPATH') ) {
    die( 'ABSPATH is not defined! "Script didn\' run on Wordpress."' );
}

class Admin {

    protected static $instance = null;

    public function __construct () {
        $this->render_admin_page();
    }

    private function render_admin_page () {
        $disabled_suggestions = self::get_disabled_products();
        $status = self::get_service_status();
        $gather_data_class = ( as_has_scheduled_action( 'aipc_process_gathering', [], 'ai-product-categories-woocommerce' ) ? 'aipc-settings__gatherdataButton--disabled' : '' );
        $extra_status_txt = '';
        if ( ! empty( $status['extra_txt'] ) ) {
            $extra_status_txt = '<small class="aipc-settings__systemstatusLabelExtra"><em> (' . esc_html( $status['extra_txt'] ) . ')</em></small>';
        }

        ?>
        <div class="aipc-settings__outer">
            <div class="aipc-settings">
                <h2 class="aipc-settings__title"><?php _e( 'AI Product Categories Settings', AIPC_TEXTDOMAIN ) ?></h2>
                <div class="aipc-settings__gatherdata">
                    <h4 class="aipc-settings__gatherdataTitle"><?php _e( 'Gather Data', AIPC_TEXTDOMAIN ) ?></h4>
                    <p>
                        <button class="aipc-settings__gatherdataButton button-primary <?php echo esc_html( $gather_data_class ) ?>"><?php _e( 'Gather Data', AIPC_TEXTDOMAIN ); ?></button>
                    </p>
                </div>
                <div class="aipc-settings__disabledsuggestions">
                    <h4 class="aipc-settings__disabledsuggestionsTitle"><?php _e( 'Disabled Suggestions', AIPC_TEXTDOMAIN ) ?></h4>
                    <ul class="aipc-settings__disabledsuggestionsList">
                    <?php
                        if ( empty( $disabled_suggestions ) ) {
                            _e( 'List is empty.', AIPC_TEXTDOMAIN );
                        } else {
                            foreach ( $disabled_suggestions as $product ) {
                                ?>
                                <li data-id="<?php echo esc_attr( $product['id'] ) ?>" class="aipc-settings__disabledsuggestionsItem">
                                    <span><?php echo esc_html( $product['title'] ) ?></span> <span class="aipc-settings__disabledsuggestionsEnable"><span></span><span></span></span>
                                </li>
                                <?php
                            }
                        }
                    ?>
                    </ul>
                </div>
                <div class="aipc-settings__systemstatus">
                    <h4 class="aipc-settings__systemstatusTitle"><?php _e( 'System Status', AIPC_TEXTDOMAIN ) ?></h4>
                    <p class="aipc-settings__systemstatusLabel aipc-<?php echo esc_attr( $status['color'] ) ?>"><?php echo esc_html( $status['label'] ) . $extra_status_txt ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_disabled_products () {
        $product_ids = get_option( 'aipc_product_ids_to_skip' );
        if ( empty( $product_ids ) ) {
            return [];
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
            return [];
        }
        return $products;
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
