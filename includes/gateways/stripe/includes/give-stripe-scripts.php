<?php
/**
 * Give - Stripe Scripts
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Frontend javascript
 *
 * @since 1.0
 *
 * @return void
 */
function give_stripe_frontend_scripts() {

	// Get publishable key.
	$publishable_key = give_stripe_get_publishable_key();

	// Checkout options.
	// @TODO: convert checkboxes to radios.
	$zip_option      = give_is_setting_enabled( give_get_option( 'stripe_checkout_zip_verify' ) );
	$remember_option = give_is_setting_enabled( give_get_option( 'stripe_checkout_remember_me' ) );

	$stripe_card_update = false;
	$get_data           = give_clean( filter_input_array( INPUT_GET ) );
	$is_footer          = give_get_option( 'stripe_footer' );

	if ( isset( $get_data['action'] ) &&
		'update' === $get_data['action'] &&
		isset( $get_data['subscription_id'] ) &&
		is_numeric( $get_data['subscription_id'] )
	) {
		$stripe_card_update = true;
	}

	// Set vars for AJAX.
	$stripe_vars = array(
		'zero_based_currency'          => give_is_zero_based_currency(),
		'zero_based_currencies_list'   => give_get_zero_based_currencies(),
		'sitename'                     => give_get_option( 'stripe_checkout_name' ),
		'publishable_key'              => $publishable_key,
		'checkout_image'               => give_get_option( 'stripe_checkout_image' ),
		'checkout_address'             => give_get_option( 'stripe_collect_billing' ),
		'checkout_processing_text'     => give_get_option( 'stripe_checkout_processing_text', __( 'Donation Processing...', 'give-stripe' ) ),
		'zipcode_option'               => $zip_option,
		'remember_option'              => $remember_option,
		'give_version'                 => get_option( 'give_version' ),
		'cc_fields_format'             => give_get_option( 'stripe_cc_fields_format', 'multi' ),
		'card_number_placeholder_text' => __( 'Card Number', 'give' ),
		'card_cvc_placeholder_text'    => __( 'CVC', 'give' ),
		'donate_button_text'           => __( 'Donate Now', 'give' ),
		'element_font_styles'          => give_stripe_get_element_font_styles(),
		'element_base_styles'          => give_stripe_get_element_base_styles(),
		'element_complete_styles'      => give_stripe_get_element_complete_styles(),
		'element_empty_styles'         => give_stripe_get_element_empty_styles(),
		'element_invalid_styles'       => give_stripe_get_element_invalid_styles(),
		'float_labels'                 => give_is_float_labels_enabled( array(
			'form_id' => get_the_ID(),
		) ),
		'base_country'                 => give_get_option( 'base_country' ),
		'stripe_card_update'           => $stripe_card_update,
		'stripe_account_id'            => give_stripe_is_connected() ? give_get_option( 'give_stripe_user_id' ) : false,
		'preferred_locale'             => give_stripe_get_preferred_locale(),
	);

	// Is Stripe's checkout enabled?
	if ( give_stripe_is_checkout_enabled() ) {

		// Stripe checkout js.
		wp_register_script( 'give-stripe-checkout-js', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), GIVE_VERSION, $is_footer );
		wp_enqueue_script( 'give-stripe-checkout-js' );

		$deps = array(
			'jquery',
			'give',
			'give-stripe-checkout-js',
		);

		// Give Stripe Checkout JS.
		// wp_register_script( 'give-stripe-popup-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-popup.js', $deps, GIVE_STRIPE_VERSION );
		// wp_enqueue_script( 'give-stripe-popup-js' );
		// wp_localize_script( 'give-stripe-popup-js', 'give_stripe_vars', $stripe_vars );

		return;
	}

	// Load Stripe on-page checkout scripts.
	if ( apply_filters( 'give_stripe_js_loading_conditions', give_is_gateway_active( 'stripe' ) ) ) {

		wp_register_script( 'give-stripe-js', 'https://js.stripe.com/v3/', array( 'jquery' ), GIVE_VERSION, $is_footer );
		wp_enqueue_script( 'give-stripe-js' );

		wp_register_script( 'give-stripe-onpage-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-stripe.js', array( 'give-stripe-js' ), GIVE_VERSION, $is_footer );
		wp_enqueue_script( 'give-stripe-onpage-js' );
		wp_localize_script( 'give-stripe-onpage-js', 'give_stripe_vars', $stripe_vars );

		// Add Payment Request Script to support Apple/Google Pay.
		// if ( give_stripe_is_apple_google_pay_enabled() ) {
		// 	wp_enqueue_script( 'give-stripe-payment-request-js', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-payment-request.js', array( 'give-stripe-js' ), GIVE_STRIPE_VERSION );
		// }

		// wp_enqueue_style( 'give-stripe', GIVE_STRIPE_PLUGIN_URL . 'assets/dist/css/give-stripe.css', array(), GIVE_STRIPE_VERSION );
	}

}

add_action( 'wp_enqueue_scripts', 'give_stripe_frontend_scripts' );

/**
 * Load Admin javascript
 *
 * @since  1.0
 *
 * @return void
 */
function give_stripe_admin_js() {

	wp_register_script( 'give-stripe-admin-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-stripe-admin.js', 'jquery', GIVE_VERSION, true );
	wp_enqueue_script( 'give-stripe-admin-js' );

	wp_register_style( 'give-stripe-admin-css', GIVE_PLUGIN_URL . 'assets/dist/css/give-stripe-admin.css', false, GIVE_VERSION );
	wp_enqueue_style( 'give-stripe-admin-css' );

}

// add_action( 'admin_enqueue_scripts', 'give_stripe_admin_js', 100 );

/**
 * Load Transaction-specific admin javascript.
 *
 * Allows the user to refund non-recurring donations.
 *
 * @since  1.0
 *
 * @param int $payment_id Payment ID.
 */
function give_stripe_admin_payment_js( $payment_id = 0 ) {

	if (
		'stripe' !== give_get_payment_gateway( $payment_id )
		&& 'stripe_ach' !== give_get_payment_gateway( $payment_id )
	) {
		return;
	}
	?>
	<script type="text/javascript">
		jQuery( function( $ ) {
			$( 'select[name="give-payment-status"]' ).on( 'change', function() {
				if ( 'refunded' === $(this).val() ) {
					$(this).parent().parent().append('<p class="give-stripe-refund"><input type="checkbox" id="give_refund_in_stripe" name="give_refund_in_stripe" value="1"/><label for="give_refund_in_stripe"><?php esc_html_e( 'Refund Charge in Stripe?', 'give-stripe' ); ?></label></p>');
				} else {
					$('.give-stripe-refund').remove();
				}
			});
		});
	</script>
	<?php

}

// add_action( 'give_view_donation_details_before', 'give_stripe_admin_payment_js', 100 );


/**
 * WooCommerce checkout compatibility.
 *
 * This prevents Give from outputting scripts on Woo's checkout page.
 *
 * @since 1.4.3
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function give_stripe_woo_script_compatibility( $ret ) {

	if (
		function_exists( 'is_checkout' )
		&& is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'give_stripe_js_loading_conditions', 'give_stripe_woo_script_compatibility', 10, 1 );


/**
 * EDD checkout compatibility.
 *
 * This prevents Give from outputting scripts on EDD's checkout page.
 *
 * @since 1.4.6
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function give_stripe_edd_script_compatibility( $ret ) {

	if (
		function_exists( 'edd_is_checkout' )
		&& edd_is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'give_stripe_js_loading_conditions', 'give_stripe_edd_script_compatibility', 10, 1 );
