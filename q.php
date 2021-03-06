<?php

/*
 * WordPress Framework
 *
 * @package         q
 * @author          Q Studio <social@qstudio.us>
 * @license         GPL-2.0+
 * @link            http://qstudio.us/
 * @copyright       2019 Q Studio
 *
 * @wordpress-plugin
 * Plugin Name:     Q
 * Plugin URI:      https://www.qstudio.us
 * Description:     Q is a Development Framework that provides an API to manage libraries, themes, plugins and widgets.
 * Version:         4.1.0
 * Author:          Q Studio
 * Author URI:      https://www.qstudio.us
 * License:         GPL
 * Copyright:       Q Studio
 * Class:           Q
 * Text Domain:     q
 * Domain Path:     /languages
 * GitHub Plugin URI: qstudio/q
*/

// quick check :) ##
defined( 'ABSPATH' ) OR exit;

/* Check for Class */
if ( ! class_exists( 'Q' ) ) {

    // instatiate plugin via WP plugins_loaded ##
    add_action( 'plugins_loaded', array ( 'Q', 'get_instance' ), 0 );

    // Q Class ##
    class Q {

        // Refers to a single instance of this class. ##
        private static $instance = null;

        // Plugin Settings
        const version = '4.1.0';
        const text_domain = 'q-textdomain'; // for translation ##
        static $debug = false; // global debugging ##
		static $device = false; // current device ##
		static $log = []; // global log ##

        /// Theme Settings
		// public static $config = []; // shared config array ##

        /**
         * Creates or returns an instance of this class.
         *
         * @return  Foo     A single instance of this class.
         */
        public static function get_instance()
        {

            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;

        }


        /**
         * Instatiate Class
         *
         * @since       0.2
         * @return      void
         */
        private function __construct()
        {

            // activation ##
            register_activation_hook( __FILE__, array ( $this, 'register_activation_hook' ) );

            // deactvation ##
            register_deactivation_hook( __FILE__, array ( $this, 'register_deactivation_hook' ) );

            // set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

            // load libraries ##
            self::load_libraries();

        }




        /**
         * plugin activation
         *
         * @since   0.2
         */
        public function register_activation_hook()
        {

            $q_options = array(
                'configured'    => true
                ,'version'      => self::version
            );

            // init running, so update configuration flag ##
            add_option( 'q_plugin', $q_options, '', true );

        }


        /**
         * plugin deactivation
         *
         * @since   0.2
         */
        public function register_deactivation_hook()
        {

            // de-configure plugin ##
            delete_option('q_plugin');

        }


        /**
         * Load Text Domain for translations
         *
         * @since       1.7.0
         *
         */
        public function load_plugin_textdomain()
        {

            // set text-domain ##
            $domain = self::text_domain;

            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain );

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );

            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'library/languages/' );

        }



        /**
         * Get Plugin URL
         *
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_url( $path = '' )
        {

            #return plugins_url( ltrim( $path, '/' ), __FILE__ );
            return plugins_url( $path, __FILE__ );

        }


        /**
         * Get Plugin Path
         *
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_path( $path = '' )
        {

            return plugin_dir_path( __FILE__ ).$path;

		}




        /**
         * Check for required breaking dependencies
         *
         * @return      Boolean
         * @since       1.0.0
         */
        public static function has_dependencies()
        {

            // check for what's needed ##
            if (
                ! class_exists( 'ACF' )
            ) {

                helper::log( 'e:>Q requires ACF to run correctly..' );

                return false;

            }

            // ok ##
            return true;

        }



        /**
        * Load Libraries
        *
        * @since        2.0
        */
		private static function load_libraries()
        {

            // core ##
			require_once self::get_plugin_path( 'library/core/_load.php' );

			// getter ##
			require_once self::get_plugin_path( 'library/get/_load.php' );

			// render engine ##
            require_once self::get_plugin_path( 'library/render/_load.php' );

			// theme ##
			require_once self::get_plugin_path( 'library/ui/_load.php' );

			// extensions ##
			require_once self::get_plugin_path( 'library/extension/_load.php' );

            // admin ##
			require_once self::get_plugin_path( 'library/admin/_load.php' );

			// test suite ##
            require_once self::get_plugin_path( 'library/test/_load.php' );

            // hooks ##
            require_once self::get_plugin_path( 'library/hook/_load.php' );

            // check for dependencies, required for UI components - admin will still run ##
            if ( ! self::has_dependencies() ) {

                return false;

            }

            // plugins ##
            require_once self::get_plugin_path( 'library/plugin/_load.php' );

        }


    }

}
