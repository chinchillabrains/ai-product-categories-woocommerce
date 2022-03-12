<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;


$options_to_remove = array(
	''
);
foreach ($options_to_remove as $option) {
	if ( get_option($option) ) {
        delete_option($option);
    }
}