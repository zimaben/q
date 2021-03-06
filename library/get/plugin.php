<?php

namespace q\get;

// Q ##
use q\core;
use q\core\helper as h;
use q\ui;
use q\get;

// Q Theme ##
use q\theme;

class plugin extends \q\get {

	
    /**
     * Check if a plugin is active
     * 
     * @since       2.0.0
     * @return      Boolean
     */
    public static function is_active( $plugin ) 
    {
        
        return in_array( $plugin, (array) \get_site_option( 'active_plugins', [] ) );
    
    }

	
    /**
    * Get Q Plugin data
    *
    * @return   Object
    * @since    0.3
    */
    public static function data( $option = 'q_plugin_data', $refresh = false ){

        if ( $refresh ) {

            #echo 'refrshing stored framework data<br />'; ##
            \delete_site_option( $option ); // delete option ##

        }

        if ( ! $array = \get_site_option( $option ) ) {

            $array = array (
                'version'       => self::version // \Q::version
            );

            if ( $array ) {

                core\method::add_update_option( $option, $array, '', 'yes' );

            }

        }

        return core\method::array_to_object( $array );

	}
	

}	