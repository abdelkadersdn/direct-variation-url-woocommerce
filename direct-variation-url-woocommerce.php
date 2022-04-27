<?php
/*
Plugin Name: Direct Variation URL for WooCommerce
Plugin URI: https://github.com/abdelkadersdn/direct-variation-url-woocommerce
Description: This plugin allows you to load variations on your product page via get variables passed directly in the url (yourwebsite.com/your-product?color=black&size=large).
Version: 1.0.2
Author: Abdelkader Soudani
Author URI: https://abdelkader.com.tn
*/ 


if (!defined('ABSPATH')) {
    exit;
}



add_filter('woocommerce_product_get_default_attributes', 'wdvu_setup_swatches', 10, 2);

function wdvu_setup_swatches($selected_attributes) {

    global $product;

	if(!is_admin()) {
        if(is_a($product, 'WC_Product_Variable')) {
            $variation_names = wdvu_get_variation_values();
            $start_vals = wdvu_get_variation_start_values( $variation_names );
            
            if(!empty($start_vals)) {
                return $start_vals;
            } else {
                return $selected_attributes;
            }
        }
    }
	
}

/*
*	Returns an array of variations related to a product
*
*	@access 		public 
*	@subpackage  	Product
*	@return array	variation_names
*
*/		
function wdvu_get_variation_values() {
	global $product;
	
	// Create an array of possible variations
	$available_variations = $product->get_variation_attributes();
	$variation_names = array();
	
	foreach ( $available_variations as $key => $variations ) {
		array_push( $variation_names, $key );
	}
	
	return $variation_names;
}

/*
*	Returns an array of variations related to a product
*
*	@access 		public 
*	@subpackage  	Product
*	@param	array	variation_names
*	@return array	start_vals
*
*/	
function wdvu_get_variation_start_values( $variation_names ) {
	global $product;

	$all_variations = $product->get_variation_attributes();
	$_GET_lower = array_change_key_case($_GET, CASE_LOWER);

	// Check to see if any of the attributes are in $_GET vars
	$start_vals = array();

	foreach ( $variation_names as $name ) {
	
		// Get the lower case name and remove the pa_ if they have it
		$lower_name = strtolower( $name );
		$clean_name = str_replace( 'pa_', '', $lower_name );
		$flag = false;
		
		// Grab the right variation based on the full name
		if ( isset( $_GET_lower[ $lower_name ] ) ) {
		
			foreach( $all_variations[ $name ] as $val ) {		
				if ( strtolower( $val ) == strtolower( $_GET_lower[ $lower_name ] ) ) {
					$flag = true;
				}			
			}

			if ( $flag == true ) {
				$start_vals[ $lower_name ] = $_GET_lower[ $lower_name ];
			}
		
		// Grab the right variation if they attribute has a pa_ infronnt of it
		} elseif ( isset( $_GET_lower[ $clean_name ] ) ) {
		
			foreach( $all_variations[ $name ] as $val ) {		
				if ( strtolower( $val ) == strtolower( $_GET_lower[ $clean_name ] ) ) {
					$flag = true;
				}			
			}

			if ( $flag == true ) {
				$start_vals[ $lower_name ] = $_GET_lower[ $clean_name ];
			}
		}
	}
	
	return $start_vals;
}
