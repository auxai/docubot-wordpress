<?php
/*
Copyright (C)  2017, 1LAW Legal Technologies, LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
include 'src/docubot.php';

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'DocubotWP' ) ):

class DocubotWP {

    private static $instance;
    private static $docubotAPIURL = 'https://docubotapi.1law.com';

    public static function instance() {

        if ( !isset( self::$instance ) || !( self:: $instance instanceof DocubotWP ) ) {

            self::$instance = new DocubotWP();

        }
        return self::$instance;

    }

    public function __construct() {

        add_action( 'wp_enqueue_scripts', __CLASS__ . '::docubot_assets' );
        add_shortcode( 'Docubot', __CLASS__ . '::docubot_shortcode' );
        add_filter( 'query_vars', __CLASS__ . '::add_query_vars' );

    }

    public static function add_query_vars( $vars ) {

        $vars[] = 'doctype';
        return $vars;

    }


    public static function docubot_assets() {

        wp_register_script( 'docubot', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/docubot.js', '', '', true );
        wp_enqueue_script( 'docubot' );
        if ( get_option('docubot_use_files') == '1' ) {

            //√TODO: Get file info, pass it to js to handle file based docubot usage
            wp_localize_script(
              'docubot',
              'docubot_script',
              array(
                // 'docNameOne' => 'docubot_doctree_1',
                // 'docNameTwo' => 'docubot_doctree_2',
                // 'docNameThree' => 'docubot_doctree_3'
                'initial_nonce' => wp_create_nonce( 'docubot-message-nonce' ),
                'jszip_url' => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/vendor/jszip.min.js',
                'adminjs_url' => admin_url( 'admin-media.php' ),
                'plugin_url' => plugin_dir_url( dirname( __FILE__ ) ) )
              )

            );

        }
        wp_register_style( 'docubot_style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/docubot.css' );
        wp_enqueue_style( 'docubot_style' );

    }

    public static function docubot_shortcode( $atts ) {

        if ( !get_option( 'docubot_api_key' ) || !get_option( 'docubot_api_secret' ) ) {

            return;

        }
        $embedurl = 'https://docubotembed.1law.com/';
        $useDocubotFiles = get_option('docubot_use_files');
        $instructionText = get_option( 'docubot_instruction_text' );
        if ( !isset( $instructionText ) || $instructionText === '' ) {

            $instructionText = "To get started, please tell DocuBot what you’d like to do.";

        }
        $docNameOne = '';
        $docNameTwo = '';
        $docNameThree = '';
        $doctype = get_query_var( 'doctype', NULL );
        if ( $useDocubotFiles == '1' ) {

            $docTreeOne = get_option( 'docubot_doctree_1' );
            $docTreeOneObj = json_decode( $docTreeOne );
            if ( !empty( $docTreeOne ) ) {

                $docNameOne = $docTreeOneObj->documentName;

            }
            $docTreeTwo = get_option( 'docubot_doctree_2' );
            $docTreeTwoObj = json_decode( $docTreeTwo );
            if ( !empty( $docTreeTwo ) ) {

                $docNameTwo = $docTreeTwoObj->documentName;

            }
            $docTreeThree = get_option( 'docubot_doctree_3' );
            $docTreeThreeObj = json_decode( $docTreeThree );
            if ( !empty( $docTreeThree ) ) {

                $docNameThree = $docTreeThreeObj->documentName;

            }
            if (
                strtolower( $doctype ) != strtolower( $docNameOne ) &&
                strtolower( $doctype ) != strtolower( $docNameTwo ) &&
                strtolower( $doctype ) != strtolower( $docNameThree )
            ) {

              $doctype = NULL;

            }

        } else {

            //√TODO: BUILD query string with client api keys and document id if applicable document
            \$embedurl .= 'd=' . $documentid . 'c=' . $clientid .  's=' . $clientsecret . 'primaryColor=' . $primaryColor . 'primaryColorContrast=' . $PCC . 'bg=' . $bg . 'secondaryColor=' . $sc . 'secondaryColorContrast=' . $SCC \

        }
        ?>

        <div class="docubot_container<?php if ( $useDocubotFiles == '1' ) { ?> docubot_use_files<?php } if ( isset( $doctype ) ) : ?> docubot_conversation_started<?php endif ?>">
            <div class="sprite-Docubot"></div>
            <div class="docubot_logo_container">
                <div class="docubot_logo">
                    <?php readfile(plugin_dir_path( __DIR__ ) . 'assets/img/docubot-logo.svg');?>
                </div>
                <div class="docubot_site_logo_container">
                    <img class="docubot_site_logo" src="<?php echo wp_get_attachment_url( get_option( 'docubot_site_logo_id' ) ); ?>" />
                </div>
                <div class="docubot_getstarted_text"><?php echo $instructionText?></div>
            </div>
            <?php
            if ( $useDocubotFiles == '1' ) { ?>
            <div class="docubot_document_buttons">
              <?php if ( $docNameOne ) { ?>
                <button type="button" data-value="<?php echo $docNameOne; ?>" class="docubot_document_button"><?php echo $docNameOne; ?></button>
              <?php } ?>
              <?php if ( $docNameTwo ) { ?>
                <button type="button" data-value="<?php echo $docNameTwo; ?>" class="docubot_document_button"><?php echo $docNameTwo; ?></button>
              <?php } ?>
              <?php if ( $docNameThree ) { ?>
                <button type="button" data-value="<?php echo $docNameThree; ?>" class="docubot_document_button"><?php echo $docNameThree; ?></button>
              <?php } ?>
            </div>
            <?php } ?>
            <div class="docubot_message_container">
                <!-- √ TODO: add iframe here -->
                <iframe src='https://docubotembed.1law.com/'/>
            </div>
        </div>

    <?php }


}

/**
 * The main function responsible for returning The DocubotWP
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $variable = DocubotWP(); ?>
 *
 * @since 1.0
 * @return object The DocubotWP Instance
 */
function DocubotWP() {

    return DocubotWP::instance();

}

DocubotWP();

endif;
