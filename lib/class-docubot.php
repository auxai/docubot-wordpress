<?php
    include 'src/docubot.php';

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'DocubotWP' ) ):

class DocubotWP {

    private static $instance;

    public static function instance() {

        if ( !isset( self::$instance ) || !( self:: $instance instanceof DocubotWP ) ) {

            self::$instance = new DocubotWP();

        }
        return self::$instance;

    }

    public function __construct() {

        add_action( 'wp_enqueue_scripts', __CLASS__ . '::docubot_assets' );
        add_action( 'wp_ajax_docubot_send_message', __CLASS__ . '::docubot_send_message' );
        add_action('wp_ajax_nopriv_docubot_send_message', __CLASS__ . '::docubot_send_message');
        add_shortcode('Docubot', __CLASS__ . '::docubot_shortcode');

    }

    public function docubot_assets() {

        wp_register_script( 'docubot', plugins_url() . '/docubot_wp_plugin/assets/js/docubot.js', '', '', true );
        wp_enqueue_script( 'docubot' );
        wp_localize_script( 'docubot', 'docuajax_object',  array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'plugins_url' => plugins_url() ));
        wp_register_style( 'docubot_style', plugins_url() . '/docubot_wp_plugin/assets/css/docubot.css' );
        wp_enqueue_style( 'docubot_style' );

    }

    public function docubot_send_message() {

        $key = get_option('docubot_api_key');
        $secret = get_option('docubot_api_secret');
        $server = new \OneLaw\Docubot($key, $secret);
        $thread = $_POST['thread'];
        $sender = $_POST['sender'];
        $message = $_POST['message'];
        if ( isset($thread) && isset($sender) ) {

            $results = $server->send_message( $message, $thread, $sender );

        } else {

            $results = $server->send_message( $message );

        }
        header( 'Content-Type: application/json' );
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

                $results->data->messages[] = "Here is a link to your document. It will expire after 12hrs: <a target=\"_blank\" href=\"" . $url_response->data->url . "\">" . $url_response->data->url . "</a>";

            }

        }
        $data = [ 'messages' => $results->data->messages, 'complete' => $results->data->complete ];
        $meta = [ 'threadId' => $results->meta->threadId, 'userId' => $results->meta->userId ];
        $res = [ 'data' => $data, 'meta' => $meta ];
        print json_encode( $res );
        wp_die();

    }



    public function docubot_shortcode() {

        $instructionText = get_option( 'docubot_instruction_text' );
        if ( !isset( $instructionText ) || $instructionText === '' ) {

            $instructionText = "To get started, please tell DocuBot what you’d like to do.";

        }
        $doctype = $_GET['doctype'];
        ?>

        <div class="docubot_container <?php if ( isset( $doctype ) ) : ?>docubot_conversation_started<?php endif ?>">
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
            <div class="docubot_message_container">
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
