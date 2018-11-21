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
// namespace OneLaw;
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
        add_action( 'wp_footer', __CLASS__ . '::docubot_popup' );
        add_shortcode( 'Docubot', __CLASS__ . '::docubot_shortcode' );


    }


    public static function docubot_assets() {

        wp_register_script( 'docubot_popup', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/docubot_popup.js' );
        wp_enqueue_script('docubot_popup');
        wp_register_script( 'docubot', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/docubot.js' );
        if ( get_option('docubot_use_files') == '1' ) {
            $doc1 = get_option( 'docubot_document_1' );
            $doc2 = get_option( 'docubot_document_2' );
            $doc3 = get_option( 'docubot_document_3' );
            $doctree1 = get_option( 'docubot_doctree_1' );
            $doctree2 = get_option( 'docubot_doctree_2' );
            $doctree3 = get_option( 'docubot_doctree_3' );
            $documents = array(
              'doc1' => array( 'document' => $doc1, 'doctree' => $doctree1 ),
              'doc2' => array( 'document' => $doc2, 'doctree' => $doctree2 ),
              'doc3' => array( 'document' => $doc3, 'doctree' => $doctree3 )
            );
            $queryParam = DocubotWP::get_query_param();
            wp_localize_script(
              'docubot',
              'docubot_documents',
              array(
                'documents' => $documents,
                'embedurl' => 'https://docubotembed.1law.com/',
                'queryParam' => $queryParam
              )
            );
        }
        wp_enqueue_script( 'docubot' );
        wp_register_style( 'docubot_style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/docubot.css' );
        wp_enqueue_style( 'docubot_style' );

    }

    /**
     * Determines whether or not to use Docubot Files and returns '0', '1', or false
     *
     * @return string|boolean
     */
    private static function docubot_use_files( ) {

      return get_option( 'docubot_use_files' );

    }

    /**
     * Returns an array with 3 entries, and it will either have the name of the associated document, or an empty string.
     *
     * @return array
     */
    private static function get_doc_names( ) {

      $docNames =  array('', '', '');
      $docTreeOne = get_option( 'docubot_doctree_1' );
      $docTreeOneObj = json_decode( $docTreeOne );
      if ( !empty( $docTreeOne ) ) {

          $docNames[0] = $docTreeOneObj->documentName;

      }
      $docTreeTwo = get_option( 'docubot_doctree_2' );
      $docTreeTwoObj = json_decode( $docTreeTwo );
      if ( !empty( $docTreeTwo ) ) {

          $docNames[1] = $docTreeTwoObj->documentName;

      }
      $docTreeThree = get_option( 'docubot_doctree_3' );
      $docTreeThreeObj = json_decode( $docTreeThree );
      if ( !empty( $docTreeThree ) ) {

          $docNames[2] = $docTreeThreeObj->documentName;

      }

      return $docNames;

    }

    /**
     * Gets a value from query param, then returns the value. If there is no query param it returns an empty string.
     * 
     * @return string
     */
    private static function get_query_param() {

      $docname = '';
      if ( isset( $_GET['doc'] ) ) {
        $docname = $_GET['doc'];
      } else if ( isset( $_GET['doctype'] ) ) {
        $docname = $_GET['doctype'];
      }

      return $docname;

    }

    /**
     * Takes the query param, sends it to the docubot server, and returns the document id or false if there is an error.
     * 
     * @return string|boolean
     */
    private static function get_doc_from_query_param() {

      $docname = DocubotWP::get_query_param();
      if ( $docname != '' ) {
        $docubot = new \OneLaw\Docubot( urldecode( $clientid ), urldecode( $clientsecret ), self::$docubotAPIURL );
        $result = $docubot->get_document_id( $docname );
        if ( !is_a( $result, '\OneLaw\DocubotError', true ) ) {
          $useQueryParam = true;
          return $result;
        }
      }

      return false;

    }

    /**
     * Creates embedded URL with the clients ID and Secret, then returns a string 
     * 
     * @return string
     * @param string $clientid Client's unique public API key.
     * @param string $clientsecret Client's secret associated with the $clientid / API key
     * @param string|boolean $urlQueryParam Optional. Id of document associated with user inputed url query parameter.
     * @param array $shortcodeAtts Optional. Shortcode attributes that have been formatted using the function shortcode_atts() https://developer.wordpress.org/reference/functions/shortcode_atts/
     */
    private static function get_embed_url($clientid, $clientsecret, $urlQueryParam = false, $shortcodeAtts = null ) {

      $embedurl = "https://docubotembed.1law.com/";
      $embedurl .= '?c=' . $clientid .  '&s=' . $clientsecret;

      if ($urlQueryParam != false) {

        $d = $urlQueryParam;

      } else if ($shortcodeAtts != null && $shortcodeAtts['document_id'] != '' ) {

        $d = $shortcodeAtts['document_id'];

      } else {

        $d = get_option( 'docubot_document_id' );

      }

      if ( isset($d) && $d != '' ) {

        $embedurl .= '&d=' . $d;

      }
      $colors = DocubotWP::get_embed_url_colors($shortcodeAtts);
      $embedurl .= '&' . $colors;

      return $embedurl;

    }

    /**
     * Gets custom colors for docubot and returns url query param formatted string. If no custom colors returns an empty string
     * 
     * @return string
     * @param array $shortcodeAtts Optional. Shortcode attributes that have been formatted using the function shortcode_atts() https://developer.wordpress.org/reference/functions/shortcode_atts/
     */
    private static function get_embed_url_colors( $shortcodeAtts = null ) {

      $embColors = '';

      if ( $shortcodeAtts != null && $shortcodeAtts['primary_color'] != '' ) {

        $primary_color = str_replace( '#', '%23', $shortcodeAtts['primary_color'] );

      } else {

        $primary_color = str_replace( '#', '%23', get_option( 'docubot_primary_color' ) );

      }

      if ( isset($primary_color) && $primary_color != '' ) {

        $embColors .= 'primaryColor=' . $primary_color;

      }

      if ( $shortcodeAtts != null && $shortcodeAtts['primary_color_contrast'] != '' ) {

        $primary_color_contrast = str_replace( '#', '%23', $shortcodeAtts['primary_color_contrast'] );

      } else {

        $primary_color_contrast = str_replace( '#', '%23', get_option( 'docubot_primary_color_contrast' ) );

      }

      if ( isset($primary_color_contrast) && $primary_color_contrast != '' ) {

        $embColors .= '&primaryColorContrast=' . $primary_color_contrast;

      }

      if ( $shortcodeAtts != null && $shortcodeAtts['bg'] != '' ) {

        $bg = str_replace( '#', '%23', $shortcodeAtts['bg'] );

      } else {

        $bg = str_replace( '#', '%23', get_option( 'docubot_bg' ) );

      }

      if ( isset($bg) && $bg != '' ) {

        $embColors .= '&bg=' . $bg;

      }

      if ( $shortcodeAtts != null && $shortcodeAtts['secondary_color'] != '' ) {

        $secondary_color = str_replace( '#', '%23', $shortcodeAtts['secondary_color'] );

      } else {

        $secondary_color = str_replace( '#', '%23', get_option( 'docubot_secondary_color' ) );

      }

      if ( isset($secondary_color) && $secondary_color != '' ) {

        $embColors .= '&secondaryColor=' . $secondary_color;

      }

      if ( $shortcodeAtts != null && $shortcodeAtts['secondary_color_contrast'] != '' ) {

        $secondary_color_contrast = str_replace( '#', '%23', $shortcodeAtts['secondary_color_contrast'] );

      } else {

        $secondary_color_contrast = str_replace( '#', '%23', get_option( 'docubot_secondary_color_contrast' ) );

      }

      if ( isset($secondary_color_contrast) && $secondary_color_contrast != '' ) {

        $embColors .= '&secondaryColorContrast=' . $secondary_color_contrast;

      }

      return $embColors;

    }

    /**
     * Creates a Popup window for Docubot
     *
     */
    public static function docubot_popup( ) {

      $clientid = get_option('docubot_api_key');
      $clientsecret = get_option('docubot_api_secret');
      if ( !$clientid || !$clientsecret ) {

          return;

      }
      if ( get_option('docubot_use_popup') != '1' ) {

        return;

      }
      $hasQueryParam = false;
      $queryParam = DocubotWP::get_query_param();
      $hasQueryParam = $queryParam != '';
      $useQueryParam = false;
      $embedurl = "https://docubotembed.1law.com/";
      $useDocubotFiles = DocubotWP::docubot_use_files( );
      $instructionText = get_option( 'docubot_instruction_text' );
      if ( !isset( $instructionText ) || $instructionText === '' ) {

          $instructionText = "To get started, please tell DocuBot what you’d like to do.";

      }
      $docNameOne = '';
      $docNameTwo = '';
      $docNameThree = '';
      if ( $useDocubotFiles == '1' ) {

        $docNames = DocubotWP::get_doc_names( );
        $docNameOne = $docNames[0];
        $docNameTwo = $docNames[1];
        $docNameThree = $docNames[2];
        if ($hasQueryParam) {

          $docname = $queryParam;

          if (
            strtolower( $docname ) != strtolower( $docNameOne ) &&
            strtolower( $docname ) != strtolower( $docNameTwo ) &&
            strtolower( $docname ) != strtolower( $docNameThree )
          ) {

            $useQueryParam = false;

          } else {

            $useQueryParam = true;

          }

        }

        $embedurl .= '?' . DocubotWP::get_embed_url_colors();

      } else {

        $docId = DocubotWP::get_doc_from_query_param( );
        $useQueryParam = $docId != false;
        $embedurl = DocubotWP::get_embed_url( $clientid, $clientsecret, $docId );

      }

      $style = '';
      if ( get_option( 'docubot_primary_color' ) !== false ) {
        $style .= "background-color:" . get_option( 'docubot_primary_color' ) . "; ";
      }

      $styleText = '';
      if (get_option( 'docubot_primary_color_contrast' ) !== false ) {
        $styleText .= "color:" . get_option( 'docubot_primary_color_contrast') . "; ";
      }

      $styleBackground = '';
      if (get_option( 'docubot_bg' ) !== false ) {
        $styleBackground .= "background-color:" . get_option( 'docubot_bg') . "; ";
      }

      // Popup Window ?>
      <div class="docubot_popup <?php echo ( get_option( 'docubot_use_l_r' ) == "left" ? "docubot_left" : "docubot_right" ); ?>">
          <div class="slide_off_docubot">
            <img class="slide_off_sprite_docubot" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/docubot_dance.gif'?>" alt="Docubot">
          </div>
          <div style="<?php echo $style ?>" class="docubot_header">
            <h3 style="<?php echo $styleText; ?>" class="chat_docubot">Chat With DocuBot</h3>
          </div>
          <div style="<?php echo $styleBackground; ?>" class="docubot_body">
            <div class="docubot_container<?php if ( $useDocubotFiles == '1' && !$useQueryParam ) { ?> docubot_use_files<?php } if ( isset( $doctype ) ) : ?> docubot_conversation_started<?php endif ?>">
              <?php
              if ( $useDocubotFiles == '1' && !$useQueryParam ) { ?>
                <div class="sprite-Docubot"></div>
                <div class="docubot_logo_container">
                    <div class="docubot_logo">
                        <?php readfile(plugin_dir_path( __DIR__ ) . 'assets/img/docubot-logo.svg');?>
                    </div>
                    <div class="docubot_site_logo_container">
                        <img class="docubot_site_logo" src="<?php echo wp_get_attachment_url( get_option( 'docubot_site_logo_id' ) ); ?>" />
                    </div>
                </div>
                <div class="docubot_document_buttons">
                  <?php if ( $docNameOne ) { ?>
                    <button style="<?php echo $style; echo $styleText ?>" type="button" data-value="doc1" class="docubot_document_button"><?php echo $docNameOne; ?></button>
                  <?php } ?>
                  <?php if ( $docNameTwo ) { ?>
                    <button style="<?php echo $style; echo $styleText ?>" type="button" data-value="doc2" class="docubot_document_button"><?php echo $docNameTwo; ?></button>
                  <?php } ?>
                  <?php if ( $docNameThree ) { ?>
                    <button style="<?php echo $style; echo $styleText ?>" type="button" data-value="doc3" class="docubot_document_button"><?php echo $docNameThree; ?></button>
                  <?php } ?>
                </div>
                <?php } ?>
                  <div class="docubot_message_container">
                    <iframe fxflex="grow" id="docubot_iframe" src="<?php echo $embedurl ?>" style="flex: 1 1 100%; box-sizing: border-box; max-height: 100%;"></iframe>
                  </div>
            </div>
            <div onclick="scrollBottom()"></div>
          </div>
      </div>
      <?php

    }

    /**
     * Implements the shortcode [Docubot] and embeds docubot onto the page. NOTE: the popup is disabled when on a page that contains short code.
     * 
     * @param array $atts Optional. Can be used to customize colors, and change document id.
     */
    public static function docubot_shortcode( $atts ) {

        $clientid = get_option('docubot_api_key');
        $clientsecret = get_option('docubot_api_secret');
        if ( !$clientid || !$clientsecret ) {

            return;

        }

        $hasQueryParam = false;
        $queryParam = DocubotWP::get_query_param();
        $hasQueryParam = $queryParam != '';
        $useQueryParam = false;
        $a = shortcode_atts( array(

          'document_id' => '',
          'primary_color' => '',
          'primary_color_contrast' => '',
          'bg' => '',
          'secondary_color' => '',
          'secondary_color_contrast' => ''

        ), $atts );
        $embedurl = "https://docubotembed.1law.com/";
        $useDocubotFiles = DocubotWP::docubot_use_files( );
        $instructionText = get_option( 'docubot_instruction_text' );
        if ( !isset( $instructionText ) || $instructionText === '' ) {

            $instructionText = "To get started, please tell DocuBot what you’d like to do.";

        }
        $docNameOne = '';
        $docNameTwo = '';
        $docNameThree = '';
        $doctype = get_query_var( 'doctype', NULL );
        if ( $useDocubotFiles == '1' ) {

          $docNames = DocubotWP::get_doc_names( );
          $docNameOne = $docNames[0];
          $docNameTwo = $docNames[1];
          $docNameThree = $docNames[2];
          if ($hasQueryParam) {

            $docname = $queryParam;

            if (
              strtolower( $docname ) != strtolower( $docNameOne ) &&
              strtolower( $docname ) != strtolower( $docNameTwo ) &&
              strtolower( $docname ) != strtolower( $docNameThree )
            ) {

              $useQueryParam = false;

            } else {

              $useQueryParam = true;

            }

          }

          $embedurl .= '?' . DocubotWP::get_embed_url_colors($a);

        } else {

          $docId = DocubotWP::get_doc_from_query_param( );
          $useQueryParam = $docId != false;
          $embedurl = DocubotWP::get_embed_url( $clientid, $clientsecret, $docId, $a );

        }
        ?>

        <div class="docubot_container<?php if ( $useDocubotFiles == '1' && !$useQueryParam ) { ?> docubot_use_files<?php } if ( isset( $doctype ) ) : ?> docubot_conversation_started<?php endif ?>">
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
            if ( $useDocubotFiles == '1' && !$useQueryParam ) {
              $style = '';
              if ( get_option( 'docubot_primary_color' ) !== false ) {
                $style .= "background-color:" . get_option( 'docubot_primary_color' ) . "; color:" . get_option( 'docubot_primary_color_contrast');
              }
              ?>
            <div class="docubot_document_buttons">
              <?php if ( $docNameOne ) { ?>
                <button style="<?php echo $style; ?>" type="button" data-value="doc1" class="docubot_document_button"><?php echo $docNameOne; ?></button>
              <?php } ?>
              <?php if ( $docNameTwo ) { ?>
                <button style="<?php echo $style; ?>" type="button" data-value="doc2" class="docubot_document_button"><?php echo $docNameTwo; ?></button>
              <?php } ?>
              <?php if ( $docNameThree ) { ?>
                <button style="<?php echo $style; ?>" type="button" data-value="doc3" class="docubot_document_button"><?php echo $docNameThree; ?></button>
              <?php } ?>
            </div>
            <?php } ?>
            <div class="docubot_message_container">
                <iframe id="docubot_iframe" src="<?php echo $embedurl; ?>"></iframe>
            </div>
        </div>
        <div onclick="scrollBottom()"></div>

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
