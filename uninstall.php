<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;


$options_to_remove = array(
	'aipc_categories_to_gather',
    'aipc_data_gathering',
    'aipc_data_gathered',
    'aipc_product_ids_to_skip',
);
foreach ($options_to_remove as $option) {
	if ( get_option( $option ) ) {
        delete_option( $option );
    }
}