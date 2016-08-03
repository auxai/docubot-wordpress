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
        $data = [ 'messages' => $results->data->messages, 'complete' => $results->data->complete ];
        $meta = [ 'threadId' => $results->meta->threadId, 'userId' => $results->meta->userId ];
        $res = [ 'data' => $data, 'meta' => $meta ];
        print json_encode( $res );
        wp_die();

    }

    public function docubot_shortcode() { ?>

        <div class="docubot_container">
                <img class="docubot_image" src="<?php echo plugins_url() . '/docubot_wp_plugin/assets/img/docubot.svg';?>" />
            <div class="docubot_logo_container">
                <div class="docubot_logo">
                    <?php readfile(plugin_dir_path( __DIR__ ) . 'assets/img/docubot-logo.svg');?>
                </div>
                <p class="docubot_getstarted_text">To get started, please tell DocuBot what you’d like to do.</p>
            </div>
            <ul class="docubot_message_display">
            </ul>
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