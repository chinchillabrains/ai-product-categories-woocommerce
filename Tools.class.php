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
    public static function get_category_suggestions ( $product_id ) {
        // Load gathered data 
        $taxonomy_terms = get_option( 'aipc_data_gathered' );
        if ( empty( $taxonomy_terms ) ) {
            return [];
        }
        $product = wc_get_product( $product_id );
        $item_name = $product->get_title();
        $matched_terms = self::match_item_to_terms( $item_name, $taxonomy_terms );
        $matched_terms = self::format_suggestions_list( $matched_terms );
        return $matched_terms;
    }

    private static function match_item_to_terms ( $item_name, $taxonomy_terms ) {
        $matches = [];
        foreach ( $taxonomy_terms as $term_name => $term_data ) {
            $item_term_match = self::get_item_term_match_score( $item_name, $term_data );
            $matches[] = [
                'category_id'       => $term_data['category_id'],
                'match_score'       => $item_term_match,
            ];
        }
        usort( $matches, '\Aipc_Tools\Tools::sort_by_match_score' );
        $ret_arr = [];
        $return_matches = 5;
        foreach ( $matches as $match ) {
            $ret_arr[] =  [
                'term_id'           => $match['category_id'],
            ];
            $return_matches--;
            if ( $return_matches == 0 ) {
                break;
            }
        }
        return $ret_arr;
    }
    
    private static function get_item_term_match_score ( $item_name, $taxonomy_arr ) {
        $item_name_arr = array_unique( explode( ' ', $item_name ) );
        $score = 0;
        $word_index = count( $item_name_arr );
        foreach ( $item_name_arr as $word ) {
            if ( isset( $taxonomy_arr['bag_of_words'][ $word ] ) ) {
                $score += $word_index / count( $item_name_arr ) + ( $taxonomy_arr['bag_of_words'][ $word ] / $taxonomy_arr['product_count'] );
                // $score +=  ( $taxonomy_arr['bag_of_words'][ $word ] / $taxonomy_arr['product_count'] );
            }
            $word_index--;
        }
        return $score;
    }
    
    
    public static function sort_by_match_score ( $first, $second ) {
        $result = $second['match_score'] - $first['match_score'];
        return $result > 0 ? 1 : -1;
    }

    private static function format_suggestions_list ( $term_list ) {
        $suggestions_list = [];
        foreach ( $term_list as $term ) {
            $category_id = $term['term_id'];
            $term_obj = get_term( $category_id, 'product_cat' );
            $category_name = $term_obj->name;
            $hierarchical_cat_name = $category_name;
            while ( $term_obj->parent != 0 ) {
                $term_obj = get_term( $term_obj->parent, 'product_cat' );
                $hierarchical_cat_name = $term_obj->name . ' > ' . $hierarchical_cat_name;
            }
            $suggestions_list[] = [
                'term_id'               => esc_html( $category_id ),
                'category_name'         => esc_html( $category_name ),
                'hierarchical_cat_name' => esc_html( $hierarchical_cat_name ),
            ];
        }
        return $suggestions_list;
    }
}