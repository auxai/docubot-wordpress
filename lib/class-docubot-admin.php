<?php
/*
Copyright (C)  2016, 1LAW Legal Technologies, LLC

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
        add_action( 'admin_enqueue_scripts', __CLASS__ . '::docubot_admin_enqueue_scripts' );

    }

    public static function docubot_menu() {

        add_menu_page( 'Docubot Settings', 'Docubot', 'manage_options', 'docubot', __CLASS__ . '::docubot_options' );

    }

    public static function docubot_options() {

        if ( !current_user_can( 'manage_options' ) )  {
    		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    	}
        wp_enqueue_media();
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

                    <tr valign="top">
                        <th scope="row">Docubot BCC Email(s)</th>
                        <td><input type="email" name="docubot_bcc_email" value="<?php echo esc_attr( get_option('docubot_bcc_email') ); ?>" placeholder="Comma separate multiple addresses" /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Logo</th>
                        <td>
                            <div class='docubot-logo-preview-wrapper'>
                                <div class="docubot_remove_image">&times;</div>
                        		<img id='docubot-logo-preview' src='<?php echo wp_get_attachment_url( get_option( 'docubot_site_logo_id' ) ); ?>' width='100px' height='100px' style='max-height: 100px; width: 100px'>
    	                    </div>
    	                    <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
    	                    <input type='hidden' name='docubot_site_logo_id' id='docubot_site_logo_id' value='<?php echo get_option( 'docubot_site_logo_id' ); ?>'>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Docubot Instruction Text</th>
                        <td><?php wp_editor( get_option( 'docubot_instruction_text' ), 'docubotinstructiontext', array( 'textarea_name' => 'docubot_instruction_text' ) ); ?></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
    	</div>

    <?php }

    public static function register_docubot_settings() {

        register_setting( 'docubot-options', 'docubot_api_key' );
        register_setting( 'docubot-options', 'docubot_api_secret' );
        register_setting( 'docubot-options', 'docubot_bcc_email' );
        register_setting( 'docubot-options', 'docubot_site_logo_id' );
        register_setting( 'docubot-options', 'docubot_instruction_text');

    }

    public static function docubot_admin_enqueue_scripts() {

        wp_register_script( 'docubot_admin_media', plugins_url() . '/docubot_wp_plugin/assets/js/admin-media.js' );
        wp_enqueue_script( 'docubot_admin_media' );
        wp_register_style( 'docubot_admin_style', plugins_url() . '/docubot_wp_plugin/assets/css/admin.css' );
        wp_enqueue_style( 'docubot_admin_style' );

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
