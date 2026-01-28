<?php
/**
 * Repayas IPN / callback handler.
 *
 * Listens on the WooCommerce API endpoint: ?wc-api=repayas
 * and updates orders when Repayas notifies us of a payment event.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the WooCommerce API endpoint.
 *
 * Example callback URL (IPN URL) for Repayas:
 *   https://your-site.com/?wc-api=repayas
 */
add_action( 'woocommerce_api_repayas', 'repayas_handle_ipn_callback' );

/**
 * Handle incoming IPN / webhook from Repayas.
 */
function repayas_handle_ipn_callback() {
	if ( ! class_exists( 'WC_Order' ) ) {
		status_header( 500 );
		exit;
	}

	$logger      = function_exists( 'wc_get_logger' ) ? wc_get_logger() : null;
	$log_context = array( 'source' => 'repayas-ipn' );

	$raw_body = file_get_contents( 'php://input' );
	$data     = json_decode( $raw_body, true );

	// Fallback to standard POST if JSON decode fails.
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
		$data = wp_unslash( $_POST );
	}

	if ( $logger ) {
		$logger->info( 'Repayas IPN received: ' . print_r( $data, true ), $log_context );
	}

	if ( empty( $data ) ) {
		status_header( 400 );
		echo 'No payload';
		exit;
	}

	// Try to locate the order using several possible keys.
	$identifier = '';
	$possible_keys = array( 'identifier', 'order_id', 'order', 'reference', 'tx_ref' );

	foreach ( $possible_keys as $key ) {
		if ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) {
			$identifier = sanitize_text_field( wp_unslash( $data[ $key ] ) );
			break;
		}
	}

	$order_id = absint( $identifier );

	if ( ! $order_id ) {
		status_header( 400 );
		echo 'Missing order identifier';
		if ( $logger ) {
			$logger->error( 'Repayas IPN: missing order identifier', $log_context );
		}
		exit;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		status_header( 404 );
		echo 'Order not found';
		if ( $logger ) {
			$logger->error( 'Repayas IPN: order not found for ID ' . $order_id, $log_context );
		}
		exit;
	}

	// OPTIONAL: Basic amount / currency verification if Repayas sends them back.
	if ( isset( $data['amount'] ) ) {
		$reported_amount = floatval( $data['amount'] );
		$order_total     = floatval( $order->get_total() );

		if ( $reported_amount && abs( $reported_amount - $order_total ) > 0.01 ) {
			if ( $logger ) {
				$logger->warning( 'Repayas IPN: amount mismatch for order ' . $order_id . ' (reported ' . $reported_amount . ', expected ' . $order_total . ')', $log_context );
			}
		}
	}

	$transaction_id = isset( $data['transaction_id'] ) ? sanitize_text_field( wp_unslash( $data['transaction_id'] ) ) : '';
	$status        = isset( $data['status'] ) ? strtolower( sanitize_text_field( wp_unslash( $data['status'] ) ) ) : '';

	// Map various success strings to a paid order.
	$success_statuses = array( 'success', 'successful', 'paid', 'completed' );

	if ( in_array( $status, $success_statuses, true ) ) {
		if ( $order->has_status( array( 'pending', 'on-hold', 'failed' ) ) ) {
			$order->payment_complete( $transaction_id );
			$order->add_order_note( sprintf( 'Repayas IPN: payment marked as complete. Transaction ID: %s', $transaction_id ) );
		}

		status_header( 200 );
		echo 'OK';
	} elseif ( in_array( $status, array( 'failed', 'cancelled', 'canceled', 'expired' ), true ) ) {
		if ( ! $order->has_status( array( 'cancelled', 'refunded' ) ) ) {
			$order->update_status( 'cancelled', 'Repayas IPN: payment reported as failed/cancelled.' );
		}

		status_header( 200 );
		echo 'Cancelled';
	} else {
		// Unknown or unhandled status – log and return 200 so Repayas doesn't keep retrying.
		if ( $logger ) {
			$logger->warning( 'Repayas IPN: unhandled status "' . $status . '" for order ' . $order_id, $log_context );
		}

		status_header( 200 );
		echo 'Ignored';
	}

	exit;
}
