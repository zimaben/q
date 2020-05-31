<?php

namespace q\theme;

// use q\core\config as config;
use q\core\core as core;
use q\core\helper as helper;
use q\theme\template as template;
use q\theme\markup as markup;
use q\wordpress\post as wp_post;
use q\wordpress\core as wp_core;
use q\wordpress\media as wp_media;
use q\theme\core as ui_core;

class ui extends \Q {


	/**
     * Open .content HTML
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function the_content_open( $args = array() )
    {

		// global arg validator ##
		if ( ! $args = ui_core::prepare_args( $args ) ){ return false; }

        // set-up new array ##
		$array = [];

        // grab classes ##
        $array['classes'] = wp_core::get_body_class( $args );

        // return ##
		return ui_core::prepare_return( $args, $array );

	}

	

	/**
     * Open .content HTML
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function the_content_close( $args = array() )
    {

		// global arg validator ##
		if ( ! $args = ui_core::prepare_args( $args ) ){ return false; }

        // set-up new array -- nothing really to do ##
		$array = [];

        // return ##
		return ui_core::prepare_return( $args, $array );

	}




	
    /**
    * Get Main Posts Loop
    *
    * @since       1.0.2
    */
    public static function the_posts( $args = array() )
    {

        // pass to get_posts ##
        return wp_post::get_posts( $args );

    }



	
    /**
    * Get Post Loop
    *
    * @since       1.0.2
    */
    public static function the_post_loop( $args = array() )
    {

        // helper::log( $args );

        // grab object with post_loop data ##
        if ( ! $object = wp_post::get_post_loop( $args ) ) { 
		
			helper::log( 'Error in $object returned from get_post_loop' );

			return false; 
		
		}

        // auto ##
        $class = ( isset( $object->auto ) && $object->auto ) ? ' auto' : '' ;

        // class ##
        $class .= true === $object->sticky ? ' is_sticky' : ' not_sticky' ;

?>
        <li class="the-post-loop">

            <div class="blog-wrapper equal-item use_wrap posts<?php echo $class; if ( 'handheld' != helper::get_device() ) echo ' whole'; ?>">
<?php

                // auto ##
                if ( isset( $object->auto ) && $object->auto ) {

?>                  <span class="auto"></span><?php

                }

?>
                <a class="blog-image lazy rounded" href="<?php echo $object->permalink; ?>" data-src="<?php echo $object->src; ?>">
                    <span></span>
                </a>
                <a href="<?php echo $object->permalink; ?>" class="use"><h3><?php echo $object->title; ?></h3></a>
                <p><?php echo $object->excerpt; ?></p>
<?php
                // test ID ##
                #pr( $object->ID );

                // post meta ##
                self::the_meta( array( 'post' => $object->ID ) );

?>
            </div>
        </li>
<?php

    }



	
    /**
     * Generic H1 title tag
     *
     * @param       Array       $args
     * @since       1.3.0
     * @return      String
     */
    public static function the_title( Array $args = null ) {

		// bounce on to getter and return value || echo ##
		return wp_post::get_the_title( $args );

    }




	/**
     * link to parent, works for single WP Post or page objects
     *
     * @since       1.0.1
     * @return      string   HTML
     */
    public static function the_parent( Array $args = null ) {

		// global arg validator ##
		if ( ! $args = ui_core::prepare_args( $args ) ){ return false; }

        // set-up new array ##
		$array = [];
		
		// pages might have a parent
		if ( 
			'page' === \get_post_type( $args['post'] ) 
			&& $args['post']->post_parent
		) {

			// $array['text'] = __( "View More", 'q-textdomain' );
            $array['permalink'] = \get_permalink( $object->ID );
            $array['slug'] = $object->post_name;
            $array['title'] = $object->post_title;

		// is singular post ##
		} elseif ( \is_single( $args['post'] ) ) {

			// helper::log( 'Get category title..' );

			// $args->ID = $the_post->post_parent;
			if ( 
				! $array = self::get_the_category([ 'post' => $args['post'] ])
			){

				return false;

			}


		}

        // return ##
		return ui_core::prepare_return( $args, $array );

	}



    /**
	 * Helper Method to get parent
	 */
	public static function get_the_parent( Array $args = null ){

		// we want to return ##
		$args['return'] = 'return';

		// bounce on, and return array ##
		return self::the_parent( $args );

	}




	/**
	 * Helper Method to get excerpt
	 */
	public static function the_excerpt( Array $args = null ){

		// bounce on, and return array ##
		return wp_post::get_the_excerpt( $args );

	}


	

	public static function the_category( Array $args = null ) {

		if ( 
			is_null( $args )
		) {

			helper::log( 'Error in passed $args' );

			return false;

		}

		// get post ##
		if ( 
			isset( $args['post'] ) 
			&& $args['post'] instanceof \WP_Post
		) {

			$the_post = $args['post'];

		} else {

			$the_post = self::the_post();

		}

		// last check ##
		if ( ! $the_post ) {

			helper::log( 'Error with post object, validate.' );

			return false;

		}

		// try and get_post_categories ##
		if ( 
			! $get_the_category = \get_the_category( $the_post->ID )
		){

			helper::log( 'No categories found for Post: '.$the_post->post_title );

			return false;

		}

		// we only want the first array item ##
		$category = $get_the_category[0];

		// test ##
		// helper::log( $category );

		// categories ##
		if (
			! is_object( $category )
			|| ! $category instanceof \WP_Term
		) {

			helper::log( 'Error in returned category' );

			return false;

		}

		$array['permalink'] = \get_category_link( $category );
		$array['slug'] = $category->slug;
		$array['title'] = $category->cat_name;

		// helper::log( $array );

		if ( isset( $args['return'] ) && 'return' == $args['return'] ) {
			
			return $array ;

		}

		if ( ! isset( $args['markup'] ) ) {

			helper::log( 'Missing "markup", returning false.' );

			return false;

		}

		$string = markup::apply( $args['markup'], $array );

		// helper::log( $string );

		// echo ##
		echo $string ;

		// stop ##
		return true;

	}



	/**
	 * Helper Method to get category
	 */
	public static function get_the_category( Array $args = null ){

		// we want to return ##
		$args['return'] = 'return';

		// bounce on, and return array ##
		return \apply_filters( 'q/wordpress/get_the_category', self::the_category( $args ) );

	}




	public static function the_author( Array $args = null ) {

		if ( 
			is_null( $args )
		) {

			helper::log( 'Error in passed $args' );

			return false;

		}

		// get post ##
		if ( 
			isset( $args['post'] ) 
			&& $args['post'] instanceof \WP_Post
		) {

			$the_post = $args['post'];

		} else {

			$the_post = self::the_post();

		}

		// last check ##
		if ( ! $the_post ) {

			helper::log( 'Error with post object, validate.' );

			return false;

		}

		// get author ##
		$author = $the_post->post_author;
		$authordata = \get_userdata( $author );

		// validate ##
		if (
			! $authordata
		) {

			helper::log( 'Error in returned author data' );

			return false;

		}

		// get author name ##
		$author_name = $authordata && isset( $authordata->display_name ) ? $authordata->display_name : 'Author' ;

		// assign values ##
		$array['permalink'] = \esc_url( \get_author_posts_url( $author ) );
		$array['slug'] = $authordata->user_login;
		$array['title'] = $author_name;

		// helper::log( $array );

		if ( isset( $args['return'] ) && 'return' == $args['return'] ) {
			
			return $array ;

		}

		if ( ! isset( $args['markup'] ) ) {

			helper::log( 'Missing "markup", returning false.' );

			return false;

		}

		$string = markup::apply( $args['markup'], $array );

		// helper::log( $string );

		// echo ##
		echo $string ;

		// stop ##
		return true;

	}



	/**
	 * Helper Method to get the author
	 */
	public static function get_the_author( Array $args = null ){

		// we want to return ##
		$args['return'] = 'return';

		// bounce on, and return array ##
		return \apply_filters( 'q/wordpress/get_the_author', self::the_author( $args ) );

	}




	public static function the_date( Array $args = null ) {

		if ( 
			is_null( $args )
		) {

			helper::log( 'Error in passed $args' );

			return false;

		}

		// get post ##
		if ( 
			isset( $args['post'] ) 
			&& $args['post'] instanceof \WP_Post
		) {

			$the_post = $args['post'];

		} else {

			$the_post = self::the_post();

		}

		// last check ##
		if ( ! $the_post ) {

			helper::log( 'Error with post object, validate.' );

			return false;

		}

		// get author ##
		$author = $the_post->post_author;
		$authordata = \get_userdata( $author );

		// validate ##
		if (
			! $authordata
		) {

			helper::log( 'Error in returned author data' );

			return false;

		}

		// get author name ##
		$author_name = $authordata && isset( $authordata->display_name ) ? $authordata->display_name : 'Author' ;

		// assign values ##
		$array['permalink'] = \esc_url( \get_author_posts_url( $author ) );
		$array['slug'] = $authordata->user_login;
		$array['title'] = $author_name;

		// helper::log( $array );

		if ( isset( $args['return'] ) && 'return' == $args['return'] ) {
			
			return $array ;

		}

		if ( ! isset( $args['markup'] ) ) {

			helper::log( 'Missing "markup", returning false.' );

			return false;

		}

		$string = markup::apply( $args['markup'], $array );

		// helper::log( $string );

		// echo ##
		echo $string ;

		// stop ##
		return true;

	}



	/**
	 * Helper Method to get the author
	 */
	public static function get_the_date( Array $args = null ){

		// we want to return ##
		$args['return'] = 'return';

		// bounce on, and return array ##
		return \apply_filters( 'q/wordpress/get_the_date', self::the_date( $args ) );

	}



	

    /**
    * Check for a return post thumbnail images and exif-data baed on passed settings ##
    *
    */
    public static function the_post_thumbnail( $args = array() )
    {

        // pass to functions
        if ( ! $object = wp_media::get_post_thumbnail( $args ) ) { return false; }

        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = ( object )\wp_parse_args( $args, config::$the_post_thumbnail );

?>
        <img src="<?php echo $object->src[0]; ?>" alt="<?php echo $object->alt; ?>" class="<?php echo $args->class; ?>" />
<?php

    }



    public static function the_password_form()
    {

?>
        <div class="password" style="text-align: center; margin: 20px;">
            <?php echo \get_the_password_form(); ?>
        </div>
<?php

        return true;

    }

}