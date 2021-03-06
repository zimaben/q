<?php

namespace q\get;

// Q ##
use q\core;
use q\core\helper as h;
use q\ui;
use q\get;

// Q Theme ##
use q\theme;

class query extends \q\get {

	

	/**
     * Get Main Posts Loop
     *
     * @since       1.0.2
     */
    public static function posts( $args = null )
    {

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'Error in passed args' );

			return false;

		}

		// h::log( $args );

		// add hardcoded query args ##
		$wp_query_args['paged'] = \get_query_var( 'paged' ) ? \get_query_var( 'paged' ) : 1 ;
		
		// merge passed args ##
		if ( 
			isset( $args['wp_query_args'] )
			&& is_array( $args['wp_query_args'] )
		){

            // merge passed args ##
			$wp_query_args = array_merge( $args['wp_query_args'], $wp_query_args );
			
		}
		
        // merge in global $wp_query variables ( required for archive pages ) ##
        if ( 
			isset( $args['wp_query_args']['query_vars'] ) 
			// && true === $args['query_vars']	
		) {

            // grab all global wp_query args ##
            global $wp_query;

            // merge all args together ##
            $wp_query_args = array_merge( $wp_query->query_vars, $wp_query_args );

			// h::log('added query vars');

        }

		// h::log( $wp_query_args );

		// filter posts_args ##
		$wp_query_args = \apply_filters( 'q/get/wp/the_posts/wp_query_args', $wp_query_args );
		
		// set-up new array to hold returned post objects ##
		$array = [];

        // run query ##
		$q_query = new \WP_Query( $wp_query_args );
		
		// put in the array key 'query' ##
		$array['query'] = $q_query ;

		// h::log( $array );

		// filter and return array ##
		return ui\method::prepare_return( $args, $array );

    }




  	/**
     * Get Post object by post_meta query
     *
     * @since       1.0.4
     * @return      Object      $args
     */
    public static function posts_by_meta( $args = array() )
    {

        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = ( object ) \wp_parse_args( $args, \q_theme::$get_post_by_meta );

        // grab page - polylang will take care of language selection ##
        $post_args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value
                )
            ),
            'post_type'         => $args->post_type,
            'posts_per_page'    => $args->posts_per_page,
            'order'				=> $args->order,
            'orderby'			=> $args->orderby
        );

        #pr( $args );

        // run query ##
        $posts = \get_posts( $post_args );

        // check results ##
        if ( ! $posts || \is_wp_error( $posts ) ) return false;

        // test it ##
        #pr( $posts[0] );
        #pr( $args->posts_per_page );

        // if we've only got a single item - shuffle up the array ##
        if ( 1 === $args->posts_per_page && $posts[0] ) { return $posts[0]; }

        // kick back results ##
        return $posts;

	}



	
    /**
    * Get post with title %like% search term
    *
    * @param       $title          Post title to search for
    * @param       $method         wpdb method to use to retrieve results
    * @param       $columns        Array of column rows to retrieve
    *
    * @since       0.3
    * @return      Mixed           Array || False
    */
    public static function posts_with_title_like( $title = null, $method = 'get_col', $columns = array ( 'ID' ) )
    {

        // sanity check ##
        if ( ! $title ) { return false; }

        // global $wpdb ##
        global $wpdb;

        // First escape the $columns, since we don't use it with $wpdb->prepare() ##
        $columns = \esc_sql( $columns );

        // now implode the values, if it's an array ##
        if( is_array( $columns ) ){
            $columns = implode( ', ', $columns ); // e.g. "ID, post_title" ##
        }

        // run query ##
        $results = $wpdb->$method (
                $wpdb->prepare (
                "
                    SELECT $columns
                    FROM $wpdb->posts
                    WHERE {$wpdb->posts}.post_title LIKE %s
                "
                #,   esc_sql( '%'.like_escape( trim( $title ) ).'%' )
                ,   \esc_sql( '%'.$wpdb->esc_like( trim( $title )  ).'%' )
                )
            );

        #var_dump( $results );

        // return results or false ##
        return $results ? $results : false ;

	}
	
	


	/**
     * Check if a page has children
     *
     * @since       1.3.0
     * @param       integer         $post_id
     * @return      boolean
     */
    public static function has_children( $post_id = null )
    {

        // nothing to do here ##
        if ( is_null ( $post_id ) ) { return false; }

        // meta query to allow for inclusion and exclusion of certain posts / pages ##
        $meta_query =
                array(
                    array(
                        'key'       => 'program_sub_group',
                        'value'     => '',
                        'compare'   => '='
                    )
                );

        // query for child or sibling's post ##
        $wp_args = array(
            'post_type'         => 'page',
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'posts_per_page'    => -1,
            'meta_query'        => $meta_query,
        );

        #pr( $wp_args );

        $object = new \WP_Query( $wp_args );

        // nothing found - why? ##
        if ( 0 === $object->post_count ) { return false; }

        // get children ##
        $children = \get_pages(
            array(
                'child_of'      => $post_id,
                'meta_key'      => '',
                'meta_value'    => '',
            )
        );

        // count 'em ##
        if( count( $children ) == 0 ) {

            // No children ##
            return false;

        } else {

            // Has Children ##
            return true;

        }

    }


}	