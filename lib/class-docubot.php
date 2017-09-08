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
        add_action( 'wp_ajax_docubot_send_message', __CLASS__ . '::docubot_send_message' );
        add_action( 'wp_ajax_nopriv_docubot_send_message', __CLASS__ . '::docubot_send_message' );
        add_shortcode( 'Docubot', __CLASS__ . '::docubot_shortcode' );
        add_filter( 'query_vars', __CLASS__ . '::add_query_vars' );

    }

    public static function docubot_assets() {

        wp_register_script( 'docubot', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/docubot.js', '', '', true );
        wp_enqueue_script( 'docubot' );
        wp_localize_script( 'docubot', 'docuajax_object',  array( 'initial_nonce' => wp_create_nonce( 'docubot-message-nonce' ), 'jszip_url' => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/vendor/jszip.min.js', 'ajax_url' => admin_url( 'admin-ajax.php' ), 'plugin_url' => plugin_dir_url( dirname( __FILE__ ) ) ));
        wp_register_style( 'docubot_style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/docubot.css' );
        wp_enqueue_style( 'docubot_style' );

    }

    public static function add_query_vars( $vars ) {

      $vars[] = 'doctype';
      return $vars;

    }

    public static function docubot_send_preview_message( $message, $server ) {

      $variables = $_POST['variables'];
      $docTree = $_POST['docTree'];
      $document = $_POST['document'];
      if ( !isset( $docTree ) ) {

          for ( $i=1; $i <= 3; $i++) {

              $docTreeTxt = get_option( 'docubot_doctree_' . $i );
              $docTreeObj = json_decode( $docTreeTxt, true );
              if ( !empty( $docTreeTxt ) ) {

                  if ( strtolower( $message ) == strtolower( $docTreeObj['documentName'] ) ) {

                      $docTree = $docTreeObj;
                      $document = json_decode( get_option( 'docubot_document_' . $i ), true );
                      break;

                  }

              }

          }
          if ( !isset( $docTree ) ) {

            $err = [ 'errors' => [ 'No Viable Doc Tree!' ] ];
            print json_encode( $err );
            wp_die();

          }
          // Send our entry question
          $data = [ 'messages' => [$docTree['entryQuestion']['question']], 'complete' => false, 'variables' => new stdClass(), 'docTree' => $docTree, 'document' => $document ];
          $meta = [ 'threadId' => NULL, 'nonce' => wp_create_nonce( 'docubot-message-nonce' ), 'userId' => NULL, 'messageMetaData' => $docTree['entryQuestion']['metaData'] ? [ $docTree['entryQuestion']['question'] => $docTree['entryQuestion']['metaData'] ] : new stdClass() ];
          $res = [ 'data' => $data, 'meta' => $meta ];
          print json_encode( $res );
          wp_die();

      } else {

          $docTree = json_decode( stripslashes( $docTree ), true );
          $document = json_decode( stripslashes( $document ), true );

      }
      if ( !isset( $variables ) ) {

          $variables = new stdClass();

      } else {

          $variables = json_decode( stripslashes( $variables ), true );
          if ( empty( $variables ) ) {

              $variables = new stdClass();

          }

      }
      $results = $server->send_preview_message( $message, $variables, $docTree );
      header( 'Content-Type: application/json' );
      if ( isset($results->errors) ) {

          $err = [ 'errors' => $results->errors ];
          print json_encode( $err );
          wp_die();

      }
      if ($results->data->complete) {

          $tmpFile = self::docubot_download_document( $server, $document, $variables );
          if ( rename( $tmpFile, $tmpFile . '.pdf' ) ) {
            $tmpFile = $tmpFile . '.pdf';
          }
          $doc_url = 'data:application/pdf;base64,' . base64_encode( file_get_contents( $tmpFile ) );

          if ( $doc_url == 'data:application/pdf;base64,' ) {

              $results->data->messages[] = "There was an error when trying to get your document";

          } else {

              $results->data->messages[] = "<a target=\"_blank\" href=\"" . $doc_url . "\" download=\"" . $docTree["documentName"] .".pdf\">" . "Click here " . "</a>to view your document.";

              $bcc = get_option( 'docubot_bcc_email' );
              if ($bcc) {

                  wp_mail(
                      $bcc,
                      get_site_url() . ' Docubot Document Generated',
                      "Hi,\n\nA user recently generated a document on your site. The generated document is attached.\n\nThanks for using Docubot!",
                      '',
                      array(
                        $tmpFile
                      )
                  );

              }

          }
          unlink( $tmpFile );

      }
      $data = [ 'messages' => $results->data->messages, 'complete' => $results->data->complete, 'variables' => $results->data->variables, 'docTree' => $docTree, 'document' => $document ];
      $meta = [ 'threadId' => $results->meta->threadId, 'nonce' => wp_create_nonce( 'docubot-message-nonce' ), 'userId' => $results->meta->userId, 'messageMetaData' => $results->meta->messageMetaData ];
      $res = [ 'data' => $data, 'meta' => $meta ];
      print json_encode( $res );
      wp_die();

    }

    private static function docubot_download_document( $server, $document, $variables ) {

        if ( !isset( $document ) ) {

            $err = [ 'errors' => [ 'No Viable Doc!' ] ];
            print json_encode( $err );
            wp_die();

        }
        if ( !isset( $variables ) ) {

            $variables = new stdClass();

        }
        $temp = tmpfile();
        $tmpFile = tempnam( sys_get_temp_dir(), 'tmp' );
        $temp = fopen( $tmpFile, 'w+' );
        if ( !$temp ) {

            $err = [ 'errors' => [ 'Couldn\'t get tmp file!' ] ];
            print json_encode( $err );
            wp_die();

        }
        $result = $server->get_preview_document( $variables, $document, $temp );
        if ( isset( $result ) ) {

            $err = [ 'errors' => $result->errors ];
            print json_encode( $err );
            wp_die();

        }
        fclose( $temp );
        return $tmpFile;

    }

    public static function docubot_send_message() {

        check_ajax_referer( 'docubot-message-nonce', 'security' );
        header('Content-Type: application/json');
        $key = get_option('docubot_api_key');
        $secret = get_option('docubot_api_secret');
        $server = new \OneLaw\Docubot($key, $secret, self::$docubotAPIURL);
        $message = $_POST['message'];
        $useDocubotFiles = get_option('docubot_use_files');
        if ( $useDocubotFiles == '1' ) {

            self::docubot_send_preview_message( $message, $server );
            return;

        }
        $thread = $_POST['thread'];
        $sender = $_POST['sender'];
        if ( isset($thread) && isset($sender) ) {

            $results = $server->send_message( $message, $thread, $sender );

        } else {

            $results = $server->send_message( $message );

        }
        if ( isset($results->errors) ) {

            $err = [ 'errors' => $results->errors ];
            print json_encode( $err );
            wp_die();

        }
        if ($results->data->complete) {

            $url_response = $server->get_document_url( $thread, $sender );

            if ( isset($url_response->errors) ) {

                $results->data->messages[] = "There was an error when trying to get your document";

            } else {

                $results->data->messages[] = "<a target=\"_blank\" href=\"" . $url_response->data->url . "\">" . "Click here " . "</a>to view your document. It will expire after 12hrs";

                $bcc = get_option( 'docubot_bcc_email' );
                if ($bcc) {

                    wp_mail(
                        $bcc,
                        get_site_url() . ' Docubot Document Generated',
                        "Hi,\n\nA user recently generated a document on your site. The document can be access at this URL:\n\n" . $url_response->data->url . "\n\nThis URL will expire after 12 hours.\n\nThanks for using Docubot!"
                    );

                }

            }

        }
        $data = [ 'messages' => $results->data->messages, 'complete' => $results->data->complete ];
        $meta = [ 'threadId' => $results->meta->threadId, 'nonce' => wp_create_nonce( 'docubot-message-nonce' ), 'userId' => $results->meta->userId ];
        $res = [ 'data' => $data, 'meta' => $meta ];
        print json_encode( $res );
        wp_die();

    }



    public static function docubot_shortcode() {

        if ( !get_option( 'docubot_api_key' ) || !get_option( 'docubot_api_secret' ) ) {

            return;

        }
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
            <ul class="docubot_message_display">
            </ul>
            <div class="docubot_loading docubot_hidden">
                <div class="onelaw-loader">
                    <div class="bounce1"></div>
                    <div class="bounce2"></div>
                </div>
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
                <div class="docubot_message_accessory" style="display: none;"></div>
                <form class="docubot_message_form">
                    <div class="docubot_message_div">
                        <input class="docubot_message" type="text" placeholder="Type your text here"/>
                        <button class="docubot_send_message docubot_sendicon" type="submit">
                            <?php readfile(plugin_dir_path( __DIR__ ) . 'assets/img/send-icon.svg');?>
                        </button>
                    </div>
                    <button class="docubot_send_message docubot_letsgo" type="submit">LET'S GO!</button>
                </form>
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
