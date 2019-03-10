<?php

namespace q\plugin;

use q\core\core as core;
use q\core\helper as helper;
use q\core\options as options;
use q\core\wordpress as wordpress;
use q\controller\generic as generic;

// load it up ##
\q\plugin\facebook::run();

class facebook extends \Q {

    public static function run()
    {
        
        if ( ! \is_admin() ) {

            // add facebook pixel ##
            \add_action( 'wp_head', [ get_class(), 'pixel'], 12 );

            // add <noscript> after opening <body> tag ##
            \add_action( 'q_action_body_open', [ get_class(), 'pixel_noscript'], 3 );

        }

    }



    /**
     * Add FB Pixel <head>
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function pixel()
    {

        // bulk on localhost ##
        if ( helper::is_localhost() ) { 
        
            // helper::log( 'FB pixel not added on localhost' );

            return false; 
        
        }

        // check if consent given to load script ##
        if ( ! generic::consent( 'marketing' ) ) {

            // helper::log( 'Marketing NOT allowed...' );

            // kick out ##
            return false;

        }

        // grab the options ##
        $q_options = options::get();

        #helper::log( $q_options );

        // bulk if no options found ##
        if ( 
            ! $q_options 
            || ! is_array( $q_options )    
        ) {

            helper::log( 'Error: Options missing...' );

            return false;

        }


        // check if we have tag_manager defined in config ##
        if ( ! $q_options['facebook_pixel'] ) {

            // helper::log( 'Facebook Pixel not defined in config' );

            return false;

        }

        // kick it back, cleanly... ##
        echo $q_options['facebook_pixel'];

    }



    /**
     * Add GTM noscript to the <body>
     *
     * @since       1.0.2
     * @return      string   HTML
     */
    public static function pixel_noscript()
    {

        // bulk on localhost ##
        if ( helper::is_localhost() ) { 
                
            // helper::log( 'Analytics skipped, as on localhost...' );

            return false; 

        }

        // check if consent given to load script ##
        if ( ! generic::consent( 'marketing' ) ) {

            // helper::log( 'Marketing NOT allowed...' );

            // kick out ##
            return false;

        }

        // grab the options ##
        $q_options = options::get();

        #helper::log( $q_options );

        // bulk if no options found ##
        if ( 
            ! $q_options 
            || ! is_array( $q_options )    
        ) {

            helper::log( 'Error: Options missing...' );

            return false;

        }

        // check for UI ##
        if ( ! $q_options["facebook_pixel_noscript"] ) { 

            // Log ##
            // helper::log( 'Facebook Pixel No Script not defined' );

            // kick off ##
            return false; 

        }

        // kick it back, cleanly... ##
        echo $q_options['facebook_pixel_noscript'];

    }



    public static function share( Array $args = null )
    {

        // sanity ##
        if ( 
            is_null( $args )
            || ! is_array( $args ) 
            || ! isset( $args['markup'] ) 
            || ! apply_filters( 'q/plugin/facebook/app_id', false ) // we also need to check for FB config ##
        ) {

            helper::log( 'Missing config.' );

            return false;

        }

        // we need a post to share, so let's see if we have one ##
        if ( ! $the_post = wordpress::the_post() ) { 
        
            helper::log( 'No post object found.' );

            return false; 
        
        }

        // render widget ##

?>
<?php echo $args['markup']; ?>
<div id="fb-root"></div>
<script>
    
// jQuery ##
if ( typeof jQuery !== 'undefined' ) {

    jQuery(document).ready(function() {

        // fb sharing ##
        $facebook = jQuery('.q_facebook_share');
        if ( $facebook.length != 0 ) { // load options, if '.q_facebook_share' selector found ##

            // FB async ##
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '<?php echo apply_filters( "q/plugin/facebook/app_id", false ); ?>',
                    xfbml      : true,
                    version    : 'v2.2'
                });
            };

            (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }( document, 'script', 'facebook-jssdk' ));

        }


        // FB share ##
        jQuery(".q_facebook_share").click(function(e) {
                
            e.preventDefault();
            
            if ( typeof FB !== "undefined" ) {
                
<?php 
                
                // grab some details ##
                $fb_name = \esc_js( get_the_title( $the_post->ID ));
                $fb_link = get_permalink( $the_post->ID );
                $fb_picture = \wp_get_attachment_image_src( \get_post_thumbnail_id( $the_post->ID ), 'square-small' );
                $fb_caption = \esc_js( \get_post_meta( \get_post_thumbnail_id( $the_post->ID ), '_wp_attachment_image_alt', true));
                $fb_description = \esc_js( wordpress::excerpt_from_id( $the_post->ID ));
                    
?>
                FB.ui (
                    {
                        method: 'feed',
                        name: '<?php echo $fb_name; ?>',
                        link: '<?php echo $fb_link; ?>',
                        picture: '<?php echo $fb_picture[0]; ?>',
                        caption: '<?php echo $fb_caption; ?>',
                        description: '<?php echo $fb_description; ?>'
                    },
                    function(response) {
                        if (response && response.post_id) {
                            jQuery(".q_facebook_share").text('Shared on Facebook!');
                        } else {
                            jQuery(".q_facebook_share").text('Oops!');
                            fb_restore = setTimeout(function(){
                                jQuery(".q_facebook_share").text('Share on Facebook');
                            }, 3000);
                        }
                    }
                );

            }
        
        });
          
    });

}
    
</script>
<?php

    }


}