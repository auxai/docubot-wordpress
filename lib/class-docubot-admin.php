<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'DocubotAdmin' ) ):

class DocubotAdmin {

    private static $instance;

    public static function instance() {

        if ( !isset( self::$instance ) || !( self:: $instance instanceof DocubotAdmin ) ) {

            self::$instance = new DocubotAdmin();

        }
        return self::$instance;

    }

    public function __construct() {

        add_action( 'admin_menu', __CLASS__ . '::docubot_menu' );
        add_action( 'admin_init', __CLASS__ . '::register_docubot_settings' );

    }

    public static function docubot_menu() {

        add_menu_page( 'Docubot Settings', 'Docubot', 'manage_options', 'docubot', __CLASS__ . '::docubot_options' );

    }

    public static function docubot_options() {

        if ( !current_user_can( 'manage_options' ) )  {
    		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    	}
        ?>
    	<div class="wrap">
            <h1>Docubot Settings</h1>
        	<form method="post" action="options.php">
                <?php settings_fields( 'docubot-options' );
                do_settings_sections( 'docubot-options' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Docubot API Key</th>
                        <td><input type="text" name="docubot_api_key" value="<?php echo esc_attr( get_option('docubot_api_key') ); ?>" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Docubot API Secret</th>
                        <td><input type="password" name="docubot_api_secret" value="<?php echo esc_attr( get_option('docubot_api_secret') ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
    	</div>

    <?php }

    public static function register_docubot_settings() {

        register_setting( 'docubot-options', 'docubot_api_key' );
        register_setting( 'docubot-options', 'docubot_api_secret' );

    }


}

/**
 * The main function responsible for returning The DocubotAdmin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $variable = DocubotAdmin(); ?>
 *
 * @since 1.0
 * @return object The DocubotAdmin Instance
 */
function DocubotAdmin() {

    return DocubotAdmin::instance();

}

DocubotAdmin();

endif;
