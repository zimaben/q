<?php

namespace q\ui\asset;


use q\core\core;
use q\core\helper as h;
use q\ui;

// load it up ##
\q\ui\asset\css::run();

class css extends \Q {
    
    static $args = array();
    static $array = array();
    static $force = false; // force refresh of CSS file ##

    public static function run()
    {

        // h::log( 'style file loaded...' );

        // add CSS to head if debugging or file if not ##
        \add_action( 'wp_head', [ get_class(), 'wp_head' ], 10000000000 );

    }



    public static function args( $args = false )
    {

        #h::log( 'passed args to class' );
        #h::log( $args );

        // update passed args ##
        self::$args = \wp_parse_args( $args, self::$args );

    }



    public static function strip_tag( $string = null ){

        return str_replace( array( '<style>', '</style>' ), '', $string );
        #return preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $string );

    }


    public static function add_tag( $string = null ){

        return '<style>'.$string.'</style>';

    }


    public static function comment( $string = null, $priority = 10 )
    {

        // sanity ##
        if ( is_null( $string ) ) {

            return false;

        }

$return = 
"
/**
$string
Priority: {$priority}
*/
";

        // kick it back ##
        return $return;

    }



    

    public static function ob_get( Array $args = null )
    {

        // sanity ##
        if ( 
            is_null( $args )
            || ! isset( $args["view"] )
            || ! isset( $args["method"] )
        ){

            h::log( 'Missing args..' );

            return false;

        }

        if ( 
            ! method_exists( $args['view'], $args['method'] )
            || ! is_callable( array( $args['view'], $args['method'] ) )
        ){

            h::log( 'handler wrong - class: '.$args['view'].' / method: '.$args['method'] );

            return false;

        }

        // h::log( 'add css from - class: '.$args['view'].' / method: '.$args['method'] );

        // h::log( self::$args );
        ob_start();

        // call class method and pass arguments ##
        $data = call_user_func_array (
                array( $args['view'], $args['method'] )
            ,   array( $args )
        );

        // grab ##
        $data = ob_get_clean(); 

        if ( ! $data ) {
            
            h::log( 'Handler method returned bad data..' );

            return false;

        }

        // h::log( $data );

        // add script ##
        self::add( $data, $args["priority"], $args["handle"] ) ;

        // ok ##
        return true;

    }




    /**
    * build array for rendering
    *
    * @since    2.0.0
    * @return   String HTML
    */
    public static function add( $string = null, $priority = 10, $comment = false )
    {

        // h::log( 'CSS render called for: '.$comment .' --- length: '. strlen( $string ) );

        // sanity ##
        if ( is_null( $string ) ) {

            #h::log( 'nothing passed to renderer...' );

            return false;

        }

        // we need to strip the <script> tags ##
        $string = self::strip_tag( $string );

        // add the passed value to the array ##
        self::$array[$priority] = 
            isset( $array[$priority] ) ?
            $array[$priority].self::comment( $comment, $priority ).$string :
            self::comment( $comment, $priority ).$string ;

    }




    public static function header()
    {

        // version ##
        $version = self::version;

        // date ##
        $date = date( 'd/m/Y h:i:s a' );

// return it ##
return "/**
Plugin:     Q Theme
Version:    {$version}
Date:       {$date}
*/
";

    }



    /**
    * Render inline or to a script file
    *
    * @since    2.0.0
    * @return   Mixed
    */
    public static function wp_head()
    {

        // h::log( 'css header called...' );
        #h::log( self::$array );

        // sanity ##
        if ( 
            is_null( self::$array ) 
            || ! array_filter( self::$array )
        ) {

            h::log( 'array is empty.' );

            return false;

        }

        // empty string ##
        $string = '';

        // render inline or to a file - depending on debug status  ##
        switch ( self::$debug ) {

            // if we are debugging ##
            case ( true ):

                // loop over all array keys and dump value to string ##
                foreach( self::$array as $key => $value ) {

                    $string .= $value;

                }

                // prefix header to string ##
                $string = self::header().$string;

                // wrap in tags ##
                $string = self::add_tag( $string );

                #h::log( $string );

                // echo back into the end of the markup ##
                echo $string;

            break ;        

            // if we are not debugging, then we generate a file "q.theme.css" and dump the scripts in order - stripping the <style> tag wrappers ##
            case ( false ):
            default:

                //  file ##
                $file = \q_theme::get_parent_theme_path( '/library/ui/asset/css/q.theme.css' );

                // h::log( 'File: '.$file );
                // h::log( 'Theme File: '.$file );

                if ( ! file_exists( $file ) ) {

                    // h::log( 'theme/css/q.theme.css missing, so creating..' );

                    touch( $file ) ;

                }

                // flatten ##
                $string .= implode( "", self::$array );

                // mimnify ##
                $string = ui\method::minify( $string, 'css' );

                // add header to empty string ##
                $string = self::header().$string;

                // get the length of the total new string with header ##
                $length = strlen( $string );

                // check the stored length of the file to see if it has changed ##
                if ( self::is_unchanged( $length ) ) {

                    return false;

                }

                // truncate file ##
                self::truncate( $file );

                // put contents to file ##
                $file_put_contents = file_put_contents( 
                    $file, 
                    $string.PHP_EOL , 
                    FILE_APPEND | LOCK_EX 
                );

                // update transient of length ##
                \set_site_transient( 'q_css_length', $length, 1 * WEEK_IN_SECONDS );

            break ;

        }

    }



    public static function is_unchanged( $length = 0 ) 
    {

        // force refresh ##
        if ( self::$force ) {

            \delete_site_transient( 'q_css_length' );

            h::log( 'Force refresh of CSS file..' );

            return false;

        }

        // sanity ##
        if ( 
            is_null( $length ) 
        ) {

            #h::log( 'Error in passed parameters.' );

            // defer to negative ##
            return false;

        }

        // get the stored file length from teh database ##
        if ( false === ( $stored_length = \get_site_transient( 'q_css_length' ) ) ) {

            #h::log( 'Nothing found in transients.' );

            return false;

        }

        // log ##
        #h::log( 'stored length: '.$stored_length );

        // compare lengths ##
        if ( $length == $stored_length ) {

            #h::log( 'File is unchanged ( '.$length.' == '.$stored_length.' ), so not remaking' );

            return true;

        }

        #h::log( 'File length is different ( '.$length.' != '.$stored_length.' ), so remaking' );

        return false;

    }



    public static function truncate( $file = null )
    {

        $f = @fopen( $file, "r+");
        if ( $f !== false ) {
            ftruncate( $f, 0 );
            fclose( $f );
        }


    }


}
