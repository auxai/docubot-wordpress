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
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'DocubotAdmin' ) ):

class DocubotAdmin {

    private static $instance;
    private static $menu_page_name;

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

      self::$menu_page_name = add_menu_page(
        'Docubot Settings',
        'Docubot',
        'manage_options',
        'docubot',
        __CLASS__ . '::docubot_options',
        'data:image/svg+xml;base64,' . base64_encode( '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" style="fill:#82878c" viewBox="0 0 159.04 241.07"><defs></defs><title>docubot-silhouette</title><path d="M79.61,227.44c26.63,0,48.22,3.05,48.22,6.81s-21.59,6.81-48.22,6.81S31.39,238,31.39,234.26s21.59-6.81,48.22-6.81Z"/><path d="M144.73,92.46c-1.51-15.51-8-29.57-20.34-40-11.41-9.66-26.18-14.77-41-15.57l-1-8.08H76.65l-1,8.08c-14.84.8-29.62,5.9-41,15.57C22.3,62.89,15.82,77,14.31,92.46a26.37,26.37,0,0,0-2.18,45.64l.27.7a32.87,32.87,0,0,0,13.29,15.78,37.9,37.9,0,0,0,5.56,2.89L23.18,197C19.5,215.11,33.93,218.54,43,215.3v13.1a7.38,7.38,0,0,0,7.36,7.36H65a7.38,7.38,0,0,0,7.36-7.36v-7.66c2.53.25,5,.36,7.2.36s4.66-.11,7.2-.36v7.66a7.38,7.38,0,0,0,7.36,7.36h14.72a7.38,7.38,0,0,0,7.36-7.36V215.29c9,3.26,23.5-.15,19.81-18.27L128,157.39a38,38,0,0,0,5.36-2.81,32.87,32.87,0,0,0,13.29-15.78l.26-.7a26.37,26.37,0,0,0-2.18-45.64Zm-92,25.84a11.69,11.69,0,1,1,11.69-11.69A11.69,11.69,0,0,1,52.75,118.3Zm53.73,0a11.69,11.69,0,1,1,11.69-11.69A11.69,11.69,0,0,1,106.47,118.3Z"/><path d="M52.75,96.56c-.28,0-.56,0-.84,0a10,10,0,0,1,0,20c.28,0,.55,0,.84,0a10,10,0,0,0,0-20.09Zm53.73,0c-.28,0-.56,0-.84,0a10,10,0,0,1,0,20q.41,0,.84,0a10,10,0,1,0,0-20.09Z"/><path d="M87.15,0H71.71A.74.74,0,0,0,71,.74V28a.74.74,0,0,0,.74.74H87.15a.74.74,0,0,0,.74-.74V.74A.74.74,0,0,0,87.15,0ZM82.71,23.44H77.5V9.13H74.23V6.39l.3-.13a25.26,25.26,0,0,0,4.8-2.6l.13-.1h3.25Z"/></svg>' )
      );

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
          do_settings_sections( 'docubot-options' );
          $apiKey = esc_attr( get_option('docubot_api_key') );
          $apiSecret = esc_attr( get_option('docubot_api_secret') );
          $useDocubotFiles = get_option('docubot_use_files'); ?>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">Docubot API Key</th>
              <td><input type="text" name="docubot_api_key" value="<?php echo $apiKey; ?>" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">Docubot API Secret</th>
              <td><input type="password" name="docubot_api_secret" value="<?php echo $apiSecret; ?>" /></td>
            </tr>
            <?php if ( !empty( $apiKey ) && !empty( $apiSecret ) ) { ?>
              <tr valign="top">
                <th scope="row">Docubot BCC Email(s)</th>
                <td><input type="email" name="docubot_bcc_email" value="<?php echo esc_attr( get_option('docubot_bcc_email') ); ?>" placeholder="Comma separate multiple addresses" title="Comma separate multiple addresses" /></td>
              </tr>

              <tr valign="top">
                <th scope="row">Use Docubot Files</th>
                <td><input type="checkbox" name="docubot_use_files" id="docubot_use_files" value="1" <?php checked( $useDocubotFiles, '1' ); ?>/></td>
              </tr>

              <tr valign="top" class="docubot-files-row" <?php if ( $useDocubotFiles != '1' ) { ?>style="display: none;"<?php } ?>>
                <th scope="row">Docubot File (1)</th>
                <td>
                  <?php $docTreeOne = get_option( 'docubot_doctree_1' );
                  $documentOne = get_option( 'docubot_document_1' );
                  $docTreeOneObj = json_decode( $docTreeOne );
                  $docName = '';
                  if ( !empty( $docTreeOne ) ) {
                    $docName = $docTreeOneObj->documentName;
                  } ?>
                  <span class="doc-name">
                    <span id="doc_name_1"><?php echo $docName; ?></span>
                    <button type="button" class="delete-doc-button button" data-docnumber="1" <?php if ( empty( $docTreeOne ) ) { ?>style="display:none;"<?php } ?>>
                      &times;
                    </button>
                  </span>
                  <input type="hidden" name="docubot_doctree_1" id="docubot_doctree_1" value="<?php echo esc_attr( $docTreeOne ); ?>">
                  <input type="hidden" name="docubot_document_1" id="docubot_document_1" value="<?php echo esc_attr( $documentOne ); ?>">
                  <button type="button" id="docubot_file_1_upload_button" class="docubot-file-picker-button button">
                    <input type="file" id="docubot_file_1_input" class="docubot-file-picker-input" data-docnumber="1">
                    Select Docubot File
                  </button>
                </td>
              </tr>

              <tr valign="top" class="docubot-files-row" <?php if ( $useDocubotFiles != '1' ) { ?>style="display: none;"<?php } ?>>
                <th scope="row">Docubot File (2)</th>
                <td>
                  <?php $docTreeTwo = get_option( 'docubot_doctree_2' );
                  $documentTwo = get_option( 'docubot_document_2' );
                  $docTreeTwoObj = json_decode( $docTreeTwo );
                  $docName = '';
                  if ( !empty( $docTreeTwo ) ) {
                    $docName = $docTreeTwoObj->documentName;
                  } ?>
                  <span class="doc-name">
                    <span id="doc_name_2"><?php echo $docName; ?></span>
                    <button type="button" class="delete-doc-button button" data-docnumber="2" <?php if ( empty( $docTreeTwo ) ) { ?>style="display:none;"<?php } ?>>
                      &times;
                    </button>
                  </span>
                  <input type="hidden" name="docubot_doctree_2" id="docubot_doctree_2" value="<?php echo esc_attr( $docTreeTwo ); ?>">
                  <input type="hidden" name="docubot_document_2" id="docubot_document_2" value="<?php echo esc_attr( $documentTwo ); ?>">
                  <button type="button" id="docubot_file_2_upload_button" class="docubot-file-picker-button button">
                    <input type="file" id="docubot_file_2_input" class="docubot-file-picker-input" data-docnumber="2">
                    Select Docubot File
                  </button>
                </td>
              </tr>

              <tr valign="top" class="docubot-files-row" <?php if ( $useDocubotFiles != '1' ) { ?>style="display: none;"<?php } ?>>
                <th scope="row">Docubot File (3)</th>
                <td>
                  <?php $docTreeThree = get_option( 'docubot_doctree_3' );
                  $documentThree = get_option( 'docubot_document_3' );
                  $docTreeThreeObj = json_decode( $docTreeThree );
                  $docName = '';
                  if ( !empty( $docTreeThree ) ) {
                    $docName = $docTreeThreeObj->documentName;
                  } ?>
                  <span class="doc-name">
                    <span id="doc_name_3"><?php echo $docName; ?></span>
                    <button type="button" class="delete-doc-button button" data-docnumber="3" <?php if ( empty( $docTreeThree ) ) { ?>style="display:none;"<?php } ?>>
                      &times;
                    </button>
                  </span>
                  <input type="hidden" name="docubot_doctree_3" id="docubot_doctree_3" value="<?php echo esc_attr( $docTreeThree ); ?>">
                  <input type="hidden" name="docubot_document_3" id="docubot_document_3" value="<?php echo esc_attr( $documentThree ); ?>">
                  <button type="button" id="docubot_file_3_upload_button" class="docubot-file-picker-button button">
                    <input type="file" id="docubot_file_3_input" class="docubot-file-picker-input" data-docnumber="3">
                    Select Docubot File
                  </button>
                </td>
              </tr>

              <tr valign="top">
                <th scope="row">Logo</th>
                <td>
                  <div class='docubot-logo-preview-wrapper'>
                    <?php $logoId = get_option( 'docubot_site_logo_id' );
                    $logoUrl = wp_get_attachment_url( $logoId ); ?>
                    <div class="docubot_remove_image">&times;</div>
                		<img id='docubot-logo-preview' src='<?php echo !empty( $logoUrl ) ? $logoUrl : '//via.placeholder.com/100x100?text=Your+Logo'; ?>' width='100px' height='100px' style='max-height: 100px; width: 100px'>
                  </div>
                  <input id="upload_image_button" type="button" class="button" value="<?php _e( 'Upload image' ); ?>" />
                  <input type='hidden' name='docubot_site_logo_id' id='docubot_site_logo_id' value='<?php echo get_option( 'docubot_site_logo_id' ); ?>'>
                </td>
              </tr>

              <tr valign="top">
                <th scope="row">Docubot Instruction Text</th>
                <td><?php wp_editor( get_option( 'docubot_instruction_text' ), 'docubotinstructiontext', array( 'textarea_name' => 'docubot_instruction_text' ) ); ?></td>
              </tr>
            <?php } ?>
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
        register_setting( 'docubot-options', 'docubot_instruction_text' );
        register_setting( 'docubot-options', 'docubot_use_files' );
        register_setting( 'docubot-options', 'docubot_doctree_1' );
        register_setting( 'docubot-options', 'docubot_document_1' );
        register_setting( 'docubot-options', 'docubot_doctree_2' );
        register_setting( 'docubot-options', 'docubot_document_2' );

    }

    public static function docubot_admin_enqueue_scripts( $hook_suffix ) {

      if ( self::$menu_page_name == $hook_suffix ) {
        wp_enqueue_media();
        wp_register_script( 'jszip', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/vendor/jszip.min.js' );
        wp_enqueue_script( 'jszip' );
        wp_register_script( 'docubot_file_handler', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/docubot-file-handler.js' );
        wp_enqueue_script( 'docubot_file_handler' );
        wp_register_script( 'docubot_admin_media', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-media.js' );
        wp_enqueue_script( 'docubot_admin_media' );
        wp_register_style( 'docubot_admin_style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin.css' );
        wp_enqueue_style( 'docubot_admin_style' );
      }

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
