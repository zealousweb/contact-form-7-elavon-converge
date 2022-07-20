<?php
/**
 * Plugin Name: Accept Elavon Payments using Contact Form 7
 * Plugin URL: http://wordpress.org/plugins/contact-form-7-Elavon-converge-payment-gateway
 * Description:  This plugin will integrate elavon payment gateway for making your payments through Contact Form 7.
 * Version: 3.1
 * Author: ZealousWeb
 * Author URI: http://zealousweb.com
 * Developer: The Zealousweb Team
 * Developer E-Mail: opensource@zealousweb.com
 * Text Domain: accept-elavon-payments-using-contact-form-7
 * Domain Path: /languages
 * 
 * Copyright: © 2009-2015 ZealousWeb Technologies.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Register the [elavon] shortcode for payment response in emails
 *
 * It will allow you to pass values in new tab 'elavon' like 
 * Test API/Secret key, Test Publishable key, Live API/Secret key, Live Publishable key, API mode, Currency, Item description, Item amount, Item quantity
 *
 * @access      public
 * @since       3.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

require_once (dirname(__FILE__) . '/accept-elavon-payments-using-contact-form-7.php');

//require_once (dirname(__FILE__) . '/assets/TCPDF/tcpdf.php');

/**
  * Deactivate plugin on deactivation of Contact Form 7
  */ 
register_deactivation_hook(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php', 'accept_payment_using_elavon_deactivate' );
function accept_payment_using_elavon_deactivate()
{
	deactivate_plugins(WP_PLUGIN_DIR . '/contact-form-7-Elavon-converge-payment-gateway/contact-form-7-elavon-converge.php');
	wp_die( __( '<b>Warning</b> : Deactivating Contact Form 7 will deactivate "Accept Elavon Payments using Contact Form 7" plugin automatically.', 'contact-form-7' ) );
}

/** 
  * Create table 'cf7elavon_extension' on plugin activation 
  **/
register_activation_hook (__FILE__, 'accept_payment_using_elavon_activation_check');
function accept_payment_using_elavon_activation_check()
{	
	//Check if Contact Form 7 is active and add table to database for elavon extension
    if ( !in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        wp_die( __( '<b>Warning</b> : Install/Activate Contact Form 7 to activate "Accept Elavon Payments using Contact Form 7" plugin', 'contact-form-7' ) );
    } 
    else {
    	global $wpdb;
		$table_name = $wpdb->prefix . "cf7elavon_extension";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    		$sql = "CREATE TABLE $table_name (
    				`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			      	`form_id` INT(11) NOT NULL,			      	
			      	`field_values` TEXT NOT NULL,
			      	`payment_details` TEXT NOT NULL,
			      	`submit_time` INT(11) NOT NULL,
			      	`user` VARCHAR(255) NOT NULL,
			      	`ip` VARCHAR(255) NOT NULL,
			      	`token` VARCHAR(255) NOT NULL,
			      	`status` TINYINT NOT NULL DEFAULT '0',
			      	`unsubscribe` TINYINT NOT NULL DEFAULT '0',
			      	PRIMARY KEY (`id`)
				) DEFAULT COLLATE=utf8_general_ci";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);			
		}
	}	
}


require_once (dirname(__FILE__) . '/cf7-elavon-converge-payment-form.php');
require_once (dirname(__FILE__) . '/cf7-elavon-converge-settings.php');


/**
  * Add Script to Admin Footer
  */

if(isset($_GET['page']) && $_GET['page']  == 'elavon-extension-payments'){
	add_action( 'admin_footer', 'accept_payment_using_elavon_extension_action_includes' ); 
}
function accept_payment_using_elavon_extension_action_includes() { 
	/* Style */
	wp_register_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
	wp_enqueue_style( 'elavon_extension_style',plugins_url('/css/elavon-extension.css', __FILE__));
	wp_enqueue_style( 'fontawesome');
}


/**
  * Add Script to Admin Footer
  */
add_action( 'admin_footer', 'accept_payment_using_elavon_back_action_includes' ); 
function accept_payment_using_elavon_back_action_includes() { 
	wp_enqueue_style( 'elavon-extension_style',plugins_url('/css/elavon-extension.css', __FILE__));
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
	wp_register_style( 'fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
	wp_enqueue_style( 'fontawesome');

	?>
	<script>
		jQuery(document).ready(function(){

			if (jQuery(".elavon-settings input[name='use_elavon']").is(':checked')) {
				jQuery(".elavon-settings input[name='amounts']").attr('required','required');
				// Add code 20/05/2016  
				jQuery(".elavon-settings input[name='transaction_type_elavon']").attr('required','required');
				jQuery(".elavon-settings input[name='merchant_id_elavon']").attr('required','required');
				jQuery(".elavon-settings input[name='user_id_elavon']").attr('required','required');
				jQuery(".elavon-settings input[name='pin_elavon']").attr('required','required');
			}
			jQuery('#wpcf7-mail fieldset legend').append('<span class="mailtag code used">[elavon]</span>');
			jQuery(".elavon-settings input[name='apimodes']").change(function() {
				if (jQuery(this).prop('checked')) {
					jQuery(".elavon-settings input[name='merchant_id_elavon']").val('');
					jQuery(".elavon-settings input[name='user_id_elavon']").val('');
					jQuery(".elavon-settings input[name='pin_elavon']").val('');	
				}else{
					jQuery(".elavon-settings input[name='merchant_id_elavon']").val('');
					jQuery(".elavon-settings input[name='user_id_elavon']").val('');
					jQuery(".elavon-settings input[name='pin_elavon']").val('');
				}
			});
			jQuery(".elavon-settings input[name='use_elavon']").change(function() {
				if (jQuery(this).prop('checked')) {
					jQuery(".elavon-settings input[name='amounts']").attr('required','required');
					jQuery(".elavon-settings input[name='merchant_id_elavon']").attr('required','required');
					jQuery(".elavon-settings input[name='user_id_elavon']").attr('required','required');
					jQuery(".elavon-settings input[name='pin_elavon']").attr('required','required');

				}else{
					jQuery(".elavon-settings input[name='amounts']").removeAttr('required');
					jQuery(".elavon-settings input[name='merchant_id_elavon']").removeAttr('required');
					jQuery(".elavon-settings input[name='user_id_elavon']").removeAttr('required');
					jQuery(".elavon-settings input[name='pin_elavon']").removeAttr('required');
				}
			});
		});
	</script>
<?php
}

add_action( 'wp_footer', 'accept_payment_using_elavon_front_action_includes' ); 
function accept_payment_using_elavon_front_action_includes() { 
	wp_enqueue_style( 'elavon_extension_style',plugins_url('/css/elavon-extension.css', __FILE__));
}
add_action( 'wp_head', 'accept_payment_using_elavon_front_action_includes_script' ); 
function accept_payment_using_elavon_front_action_includes_script() { 
	wp_enqueue_script( 'elavon_extension_script','https://js.elavon.com/v2/',array(),false,false);
}
?>