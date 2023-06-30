<?php
/**
 * Plugin Name: Repayas Payment Gateway
 * Plugin URI: https://inovidia.com
 * Author: Inovidia
 * Author URI: https://inovidia.com
 * Description: Online Payment System for Merchants.
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: repayas-payments-woo
 * 
 * Class WC_Gateway_Repayas file.
 *
 * @package WooCommerce\Repayas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'repayas_payment_init', 11 );
add_filter( 'woocommerce_currencies', 'inovidia_add_gbp_currencies' );
add_filter( 'woocommerce_currency_symbol', 'inovidia_add_gbp_currencies_symbol', 10, 2 );
add_filter( 'woocommerce_payment_gateways', 'add_to_woo_repayas_payment_gateway');

function repayas_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-wc-payment-gateway-repayas.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/repayas-order-statuses.php';
		// require_once plugin_dir_path( __FILE__ ) . '/includes/repayas-checkout-description-fields.php';
	}
}

function add_to_woo_repayas_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_repayas';
    return $gateways;
}

function inovidia_add_gbp_currencies( $currencies ) {
	$currencies['GBP'] = __( 'Great British Pounds', 'repayas-payments-woo' );
	return $currencies;
}

function inovidia_add_gbp_currencies_symbol( $currency_symbol, $currency ) {
	switch ( $currency ) {
		case 'GBP': 
			$currency_symbol = 'GBP'; 
		break;
	}
	return $currency_symbol;
}