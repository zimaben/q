<?php

namespace q\render\type;

use q\core;
use q\core\helper as h;
use q\ui;
use q\get;
use q\render;

class category extends render\type {

	/**
     * Category handler
     *  
     * 
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// start with default passed value ##
		$string = null;

		// get category ##
		$category = \get_the_category( $wp_post->ID );
		// h::log( $category );

		// get category ##
		if ( 
			! $category
			|| ! is_array( $category )
		) {

			h::log( 'No category or corrupt data returned' );

			// log ##
			log::add([
				'key' => 'notice', 
				'field'	=> __FUNCTION__,
				'value' => 'No category data returned'
			]);

			return $string;

		}

		// h::log( 'Working: '.$field );

		switch( $type_field ) {

			case 'category_name' :

				$string = isset( $category[0] ) ? $category[0]->name : null ; // category missing ##

			break ;

			case 'category_permalink' :

				$string = isset( $category[0] ) ? \get_category_link( $category[0] ) : null ; // category missing ##

			break ;

		}

		// check ##
		if ( is_null( $string ) ) {

			h::log( 'String is empty.. so return null' );

			$string = null;

		}

		// check ##
		// h::log( '$string: '.$string );

        // kick back ##
        return $string;

    }



}