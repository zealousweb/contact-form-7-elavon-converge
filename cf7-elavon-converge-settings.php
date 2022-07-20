<?php
/* @access      public
 * @since       1.0 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/*===================== Accept Elavon Payments using Contact Form 7 ====================*/
/*================== elavon Payment Form - Frontend Section ==================*/
/**
** A base module for elavon express checkout form that allows to submit payment from Contact Form 7.
**/

//Add settings link to plugins page
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'accept_payment_using_elavon_extension' );

if( ! function_exists('accept_payment_using_elavon_extension')){
	function accept_payment_using_elavon_extension ( $links ) {
	     $settingslinks = array(
	     '<a href="' . admin_url( 'admin.php?page=contact-form-7-elavon-converge' ) . '">Settings</a>',
	     );
	    return array_merge( $settingslinks, $links );
	}

}



/* Add notice */
if( ! function_exists('accept_payment_using_elavon_my_error_notice') ){
	function accept_payment_using_elavon_my_error_notice() {
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on'){
    ?>
	    <div class="error notice">
	        <p><?php _e( 'We suggest you to enable SSL on your web site to get onsite payment using this plugin.', 'my_plugin_textdomain' ); ?></p>
	    </div>
    <?php
		}
	}

}
add_action( 'admin_notices', 'accept_payment_using_elavon_my_error_notice' );


// Add elavon Tab after additional settings

add_filter( 'wpcf7_editor_panels', 'accept_payment_using_elavon_editor_panels' );
function accept_payment_using_elavon_editor_panels ( $panels ) {	
	$new_page = array(
		'elavon' => array(
			'title' => __( 'elavon', 'contact-form-7' ),
			'callback' => 'accept_payment_using_elavon_after_additional_settings'
		)
	);	
	$panels = array_merge($panels, $new_page);	
	return $panels;	
}

add_action('wpcf7_admin_after_additional_settings', 'accept_payment_using_elavon_after_additional_settings');

function accept_payment_using_elavon_after_additional_settings($cf7) 
{		
	$post_id = sanitize_text_field($_GET['post']);
	
	$use_elavon = get_post_meta($post_id, "_cf7elavon_use", true);
	$apimode = get_post_meta($post_id, "_cf7elavon_modes", true);
	$amount_field = get_post_meta($post_id, "_cf7elavon_amounts", true);

	$merchant_id_field = get_post_meta($post_id, "_cf7elavon_merchant_id", true);
	$user_id_field = get_post_meta($post_id, "_cf7elavon_user_id", true);
	$pin_field = get_post_meta($post_id, "_cf7elavon_pin_ela", true);

	$description_field = get_post_meta($post_id, "_cf7elavon_description", true); 
	$salestax_field = get_post_meta($post_id, "_cf7elavon_salestax", true); 
	$company_field = get_post_meta($post_id, "_cf7elavon_company", true);
	$address_field = get_post_meta($post_id, "_cf7elavon_address", true);
	$city_field = get_post_meta($post_id, "_cf7elavon_city", true);
	$state_field = get_post_meta($post_id, "_cf7elavon_state", true);
	$country_field = get_post_meta($post_id, "_cf7elavon_country", true);
	$zip_code_field = get_post_meta($post_id, "_cf7elavon_zip_code", true);
	$phone_field = get_post_meta($post_id, "_cf7elavon_phone", true);
	$email_field = get_post_meta($post_id, "_cf7elavon_email", true);

	$buttonlabel = get_post_meta($post_id, "_cf7elavon_button_label", true);
	$returnURL = get_post_meta($post_id, "_cf7elavon_return_url", true);
	$message = get_post_meta($post_id, "_cf7elavon_messages", true);
	
	if ($use_elavon == "1") { $checked = "CHECKED"; } else { $checked = ""; }
	if ($apimode == "1") { $testmode = "CHECKED"; } else { $testmode = ""; }

	$currency = array('AUD'=>'Australian Dollar','BRL'=>'Brazilian Real','CAD'=>'Canadian Dollar','CZK'=>'Czech Koruna','DKK'=>'Danish Krone','EUR'=>'Euro','HKD'=>'Hong Kong Dollar','HUF'=>'Hungarian Forint','ILS'=>'Israeli New Sheqel','JPY'=>'Japanese Yen','MYR'=>'Malaysian Ringgit','MXN'=>'Mexican Peso','NOK'=>'Norwegian Krone','NZD'=>'New Zealand Dollar','PHP'=>'Philippine Peso','PLN'=>'Polish Zloty','GBP'=>'Pound Sterling','RUB'=>'Russian Ruble','SGD'=>'Singapore Dollar', 'SEK'=>'Swedish Krona','CHF'=>'Swiss Franc','TWD'=>'Taiwan New Dollar','THB'=>'Thai Baht','TRY'=>'Turkish Lira','USD'=>'U.S. Dollar');
	$selected = '';
	$elavon_admin_settings = '<div class="elavon-settings"><table class="form-table"><tbody>';
	$elavon_admin_settings .= '<tr><td width="270"><label>Use elavon Payment Form</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-use-elavon"></span></td><td><input type="checkbox" value="1" name="use_elavon" '.$checked.'></td></tr>';
	$elavon_admin_settings .= '<tr><td><label>Enable Test API Mode</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-enable-test-mode"></span></td><td><input type="checkbox" value="1" name="apimodes" '.$testmode.'></td></tr>';
	
	$elavon_admin_settings .= '<tr><td><label>Amount Field Name (required)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-amount-field"></span></td><td><input type="text" value="'.$amount_field.'" name="amounts"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-title"><label>Elavon Payment Details</label></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Elavon Merchant ID (required)</label></td><td class="elavon-payment-label"><input type="text" value="'.$merchant_id_field.'" name="merchant_id_elavon"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Elavon User ID (required)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-user-id-elavon"></span></td><td class="elavon-payment-label"><input type="text" value="'.$user_id_field.'" name="user_id_elavon"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Elavon Pin  (required)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-pin"></span></td><td class="elavon-payment-label"><input type="text" value="'.$pin_field.'" name="pin_elavon"></td></tr>';
	
	$elavon_admin_settings .= '<tr><td class="elavon-payment-title"><label>Customer Details</label></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Description (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-description"></span></td><td class="elavon-payment-label"><input type="text" value="'.$description_field.'" name="description"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Salestax (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-salestax"></span></td><td class="elavon-payment-label"><input type="text" value="'.$salestax_field.'" name="salestax"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Company (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-company"></span></td><td class="elavon-payment-label"><input type="text" value="'.$company_field.'" name="company"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Address (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-address"></span></td><td class="elavon-payment-label"><input type="text" value="'.$address_field.'" name="address"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>City (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-use-city"></span></td><td class="elavon-payment-label"><input type="text" value="'.$city_field.'" name="city"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>State (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-state"></span></td><td class="elavon-payment-label"><input type="text" value="'.$state_field.'" name="state"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Country (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-country"></span></td><td class="elavon-payment-label"><input type="text" value="'.$country_field.'" name="country"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Zip Code (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-zip-code"></span></td><td class="elavon-payment-label"><input type="text" value="'.$zip_code_field.'" name="zip_code"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Phone (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-phone"></span></td><td class="elavon-payment-label"><input type="text" value="'.$phone_field.'" name="phone"></td></tr>';
	$elavon_admin_settings .= '<tr><td class="elavon-payment-label"><label>Email (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-email"></span></td><td class="elavon-payment-label"><input type="text" value="'.$email_field.'" name="email"></td></tr>';

	$elavon_admin_settings .= '<tr><td><label>Form Buttom Label (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-button-label"></span></td><td><input type="text" class="large-text" value="'.$buttonlabel.'" name="button_label"></td></tr>';
	$elavon_admin_settings .= '<tr><td><label>Success Return URL (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-return-url"></span></td><td><input type="text" class="large-text" value="'.$returnURL.'" name="return_url"></td></tr>';
	$elavon_admin_settings .= '<tr><td><label>Success Message (Optional)</label><span class="elavon-tooltip hide-if-no-js " id="cf7ela-messages"></span></td><td><input type="text" class="large-text" value="'.$message.'" name="messages"></td></tr>';

	$elavon_admin_settings .= '<input type="hidden" name="post" value="'.$post_id.'"></tbody></table></div>';

	echo $elavon_admin_settings;		
}
	
//Save elavon settings of contact form 7 admin
add_action('wpcf7_save_contact_form', 'accept_payment_using_elavon_save_elavon_settings');
function accept_payment_using_elavon_save_elavon_settings($WPCF7_form) {
		
	$post_id = sanitize_text_field($_POST['post']);
	if (!empty(sanitize_text_field($_POST['use_elavon']))) {
		$use_elavon = sanitize_text_field($_POST['use_elavon']);
		update_post_meta($post_id, "_cf7elavon_use", $use_elavon);
	} else {
		update_post_meta($post_id, "_cf7elavon_use", 0);
	}

	if (!empty(sanitize_text_field($_POST['apimodes']))) {
		$apimode = sanitize_text_field($_POST['apimodes']);
		update_post_meta($post_id, "_cf7elavon_modes", $apimode);
	} else {
		update_post_meta($post_id, "_cf7elavon_modes", 0);
	}

	$amount_field = sanitize_text_field($_POST['amounts']);
	update_post_meta($post_id, "_cf7elavon_amounts", $amount_field);

	$merchant_id_field = sanitize_text_field($_POST['merchant_id_elavon']);
	update_post_meta($post_id, "_cf7elavon_merchant_id", $merchant_id_field);

	$user_id_field = sanitize_text_field($_POST['user_id_elavon']);
	update_post_meta($post_id, "_cf7elavon_user_id", $user_id_field);

	$pin_field = sanitize_text_field($_POST['pin_elavon']);
	update_post_meta($post_id, "_cf7elavon_pin_ela", $pin_field);	

	$description_field = sanitize_text_field($_POST['description']);
	update_post_meta($post_id, "_cf7elavon_description", $description_field);

	$salestax_field = sanitize_text_field($_POST['salestax']);
	update_post_meta($post_id, "_cf7elavon_salestax", $salestax_field);

	$company_field = sanitize_text_field($_POST['company']);
	update_post_meta($post_id, "_cf7elavon_company", $company_field);

	$address_field = sanitize_text_field($_POST['address']);
	update_post_meta($post_id, "_cf7elavon_address", $address_field);

	$city_field = sanitize_text_field($_POST['city']);
	update_post_meta($post_id, "_cf7elavon_city", $city_field);

	$state_field = sanitize_text_field($_POST['state']);
	update_post_meta($post_id, "_cf7elavon_state", $state_field);	

	$country_field = sanitize_text_field($_POST['country']);
	update_post_meta($post_id, "_cf7elavon_country", $country_field);	
	
	$zip_code_field = sanitize_text_field($_POST['zip_code']);
	update_post_meta($post_id, "_cf7elavon_zip_code", $zip_code_field);	

	$phone_field = sanitize_text_field($_POST['phone']);
	update_post_meta($post_id, "_cf7elavon_phone", $phone_field);	

	$email_field = sanitize_email($_POST['email']);
	update_post_meta($post_id, "_cf7elavon_email", $email_field);	

	$buttonlabel = sanitize_text_field($_POST['button_label']);
	update_post_meta($post_id, "_cf7elavon_button_label", $buttonlabel);	

	$returnURL = esc_url($_POST['return_url']);
	update_post_meta($post_id, "_cf7elavon_return_url", $returnURL);		

	$message = sanitize_text_field($_POST['messages']);
	update_post_meta($post_id, "_cf7elavon_messages", $message);	
}

add_action('admin_print_footer_scripts', function() {
	ob_start();
	?>
		<script type="text/javascript">
			
			jQuery(document).ready( function($) {
                
				jQuery( '#cf7ela-use-elavon' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-use-elavon' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Enable Elavon Payment</h3>' .
				'<p>To enable Elavon Payment with this form.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-pin' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-pin' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Elavon Pin </h3>' .
				'<p>Add Here Elavon Pin Code Number</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-merchant-id-elavon' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-merchant-id-elavon' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Merchant Id</h3>' .
				'<p>Enter the Merchant ID here from Elavon account.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );
				
				jQuery( '#cf7ela-user-id-elavon' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-user-id-elavon' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>User Id</h3>' .
				'<p>Enter the User ID here from Elavon account.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-description' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-description' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Description</h3>' .
				'<p>Select the name of the field from where the Description value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-salestax' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-salestax' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Sales tax</h3>' .
				'<p>Select the name of the field from where the Salestax value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-enable-test-mode' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-enable-test-mode' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Enable Test Mode</h3>' .
				'<p>Check the Elavon testing <a href="https://developer.elavon.com/"> guide </a> here.This will display "sandbox mode" warning on checkout.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );
				
				jQuery( '#cf7ela-email' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-email' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Email Name</h3>' .
				'<p>Select the name of the field from where the Email value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-button-label' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-button-label' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Buttom Label</h3>' .
				'<p>Enter here Buttom Label Name.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-quantity' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-quantity' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Quantity Field Name</h3>' .
				'<p>Select the field "Quantity" from where the amount value will be multiplied before sending as Payment Amount.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-amount-field' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-amount-field' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Amount Field Name</h3>' .
				'<p>Select the field "Amount" from where the amount value sending as Payment Amount.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				

				jQuery( '#cf7ela-description' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-description' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Description Field Name</h3>' .
				'<p>Select the field description and add a short description which will be sent to Payment Page of Elavon.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-select-currency' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-select-currency' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Select Currency</h3>' .
				'<p>Select the currency which is selected from your Elavon.net merchant account.<br/><strong>Note:</strong>Elavon dont provide multiple currencies for single account</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );


				jQuery( '#cf7ela-return-url' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-return-url' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Select Success Page URL</h3>' .
				'<p>You need to select a page from the list of all your WP pages, or you have to create a Success page and select it from this dropdown, so when any payment is canceled then, it redirects the user to the Cancel page that you have selected.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-messages' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-messages' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Success Message</h3>' .
				'<p>Add here Success Message</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-phone' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-phone' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Phone Number</h3>' .
				'<p>Select the name of the field from where the Phone Number value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-zip-code' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-zip-code' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Zip Code</h3>' .
				'<p>Select the name of the field from where the Zip Code value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-country' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-country' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Country Name</h3>' .
				'<p>Select the name of the field from where the Country value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-state' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-state' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add State Name</h3>' .
				'<p>Select the name of the field from where the State value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-use-city' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-use-city' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add City Name</h3>' .
				'<p>Select the name of the field from where the City value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-address' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-address' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Address Name</h3>' .
				'<p>Select the name of the field from where the Address value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				jQuery( '#cf7ela-company' ).on( 'mouseenter click', function() {
					jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
					jQuery( '#cf7ela-company' ).pointer({
						pointerClass: 'wp-pointer cf7sa-pointer',
						content: '<?php
				_e( '<h3>Add Company</h3>' .
				'<p>Select the name of the field from where the Company value needs to be retrieved.</p>',
				'contact-form-7-Elavon-addon'
				); ?>',
						position: 'left center',
					} ).pointer('open');
				} );

				

				
			} );
			
		</script>
	<?php
	echo ob_get_clean();
} );