<?php

namespace Aipc_Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tools {
    public static function generate_bag_of_words ( $titles_list ) {
        $bag = [];
        foreach ( $titles_list as $title ) {
            // Turn multiple spaces to only 1
            $title = preg_replace( '/\s+/', ' ', $title );
            $title_arr = explode( ' ', $title );
            foreach ( $title_arr as $word ) {
                if ( ! isset( $bag[ $word ] ) ) {
                    $bag[ $word ] = 0;
                }
                $bag[ $word ]++;
            }
        }
        return $bag;
    }

    public static function get_product_titles_of_category ( $category_id ) {
		$product_titles = [];
        $limit = 300;
        $page = 1;
        // Get batches of 300 to keep memory usage low
        do {
            $args = array(
                'status'            => 'publish',
                'stock_status'      => 'instock',
                'limit'             => $limit,
                'page'              => $page,
                'aipc_find_cat'     => $category_id,
            );
    
            add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( '\Aipc_Tools\Tools', 'find_category' ), 100, 2 );
    
            $products = wc_get_products( $args );
    
            remove_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( '\Aipc_Tools\Tools', 'find_category' ), 100, 2 );
            if ( ! empty( $products ) ) {
                foreach ( $products as $product ) {
                    $product_titles[] = $product->get_title();
                }
            }

            $page++;
            
        } while ( ! empty( $products ) );

		return $product_titles;
    }

    public static function find_category( $wp_query_args, $query_vars ) {
		if ( isset( $query_vars['aipc_find_cat'] ) && ! empty( $query_vars['aipc_find_cat'] ) ) {
			$category_id = (int) $query_vars['aipc_find_cat'];
			$wp_query_args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_id,
				'operator' => 'IN',
			);
		}

		return $wp_query_args;

	}
}