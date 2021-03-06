<?php

namespace q\render;

use q\core\helper as h;
use q\ui;
use q\get;
use q\render;

class block extends \q\render {

	public static function __callStatic( $function, $args ) {

        return self::run( $args ); 
	
	}

	public static function run( $args = null ){

        // global arg validator ##
		// if ( ! $args = ui\method::prepare_args( $args ) ){ return false; }

		// validate passed args ##
        if ( ! render\args::validate( $args ) ) {

            render\log::set( $args );

            return false;

		}

		// build $args['fields'] -- @todo -- this can be moved to a pre-function call ##
		// self::$args['fields'] = [];

		// h::log( 'd:>markup: '.$args['markup'] );
		// h::log( 'd:>field: '.$args['field'] );

		// build fields array with default values ##
		// render\method::set_fields([
		// 	$args['field'] => get\post::field( $args )
		// ]);

		// empty ##
		// $array = [];

		// field ##
		// $array['field'] = $args['field'];

        // grab classes ##
		// $array['value'] = get\post::field( $args );

		// @todo -- pass field by render/format ##
		// h::log( 't:>pass field by render/format' );
		// $array['value'] =  render\format::field( $field, $value );

		// h::log( $array );

        // return ##
		// return ui\method::prepare_render( $args, $array );

		// filter field data ##
		// self::$fields = \apply_filters( 'q/render/field/'.$args['field'], self::$fields, self::$args );

		// h::log( self::$fields );

		// check each field data and apply numerous filters ##
		render\fields::prepare();

		// h::log( self::$fields );

		// Prepare template markup ##
		render\markup::prepare();

		// h::log( 'd:>markup: '.$args['markup'] );

        // optional logging to show removals and stats ##
        // render\log::set( $args );

        // return or echo ##
        return render\output::return();

    }



    /**
    * The Post Meta
    *
    * @since       1.0.2
    */
    public static function post_meta( $args = null )
    {

        // get the_post ##
        if ( ! $the_post = get\post::object( $args ) ) { return false; }

        // test ID ##
        #h::log( $the_post->ID );

        // load config from Q.. meged via filter ##
        $args = ( object ) core\config::get( 'the_post_meta' );
        #h::log( $args );

?>
        <div class="post-meta">
<?php

			// post time ##
            printf(
                __( '<span class="date">Posted %s ago </span>', self::text_domain )
                ,   \human_time_diff( \get_the_date( 'U', $the_post->ID ), \current_time('timestamp') )
            );

			// post author ##
            self::the_author( [
				'markup' => '<span class="author mr-1">Posted by <a href="%permalink%">%title%</a></span>', 
				'post'		=> $the_post // needed for loops ##
			]);
			
            // post category ##
            self::the_category( [
				'markup' 	=> '<span class="category ml-1 mr-1">in <a href="%permalink%">%title%</a></span>', 
				'post'		=> $the_post // needed for loops ##
			]);

            // if on single page and post has tags, show them ##
            // h::log( \has_tag( '', $the_post->ID ) );
            if ( 
                \is_single() 
				&& 
				\has_tag( '', $the_post->ID ) 
            ) {

                // get the tags ##
                $the_tags = \get_the_tags();
                $tags = ''; // empty ##
                if ( $the_tags ) {
                    foreach( $the_tags as $tag ) {
                        $tags .= '<span class="tag"><a href="'.\get_tag_link( $tag->term_id ).'">#'.$tag->name.'</a></span> ';
                    }
                }

                \printf(
                    \__( '<span class="tags">, Tagged: %s</span>', self::text_domain )
                     ,   $tags
                );

            }

            // comments ##
            if ( $args->allow_comments && 'open' == $the_post->comment_status ) {

                // get number of comments ##
                $comments_number = \get_comments_number( $the_post->ID );

                if ( $comments_number == 0 ) {
                    $comments = __( 'Comment', self::text_domain );
                } elseif ( $comments_number > 1 ) {
                    $comments = $comments_number.' '.__( ' Comments', self::text_domain );
                } else {
                    $comments = '1'.__( 'Comment', self::text_domain );
                }

                if ( \is_single() ) {

                    printf(
                        __( ', <span class="comment"><a href="%s" class="anchor-trigger" data-scroll="#comments">%s</a></span>', self::text_domain )
                        ,   '#comment' // variable link ##
                        ,   $comments
                    );

                } else {

                    printf(
                        __( ', <span class="comment"><a href="%s">%s</a></span>', self::text_domain )
                        ,   \get_the_permalink( $the_post->ID ).'#comment' // variable link ##
                        ,   $comments
                    );

                }

            }


?>
        </div>
<?php

    }


}
