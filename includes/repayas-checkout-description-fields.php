<?php

add_filter( 'woocommerce_gateway_description', 'inovidia_repayas_description_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'inovidia_repayas_description_fields_validation' );
add_action( 'woocommerce_checkout_update_order_meta', 'inovidia_checkout_update_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'inovidia_order_data_after_billing_address', 10, 1 );
add_action( 'woocommerce_order_item_meta_end', 'inovidia_order_item_meta_end', 10, 3 );

function inovidia_repayas_description_fields( $description, $payment_id ) {

    if ( 'repayas' !== $payment_id ) {
        return $description;
    }
    
    ob_start();

    echo '<div style="display: block; width:300px; height:auto;">';
    echo '<img src="' . plugins_url('../assets/icon.png', __FILE__ ) . '">';
    

    woocommerce_form_field(
        'customer.first_name',
        array(
            'type' => 'text',
            'label' =>__( 'First Name', 'repayas-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );

    woocommerce_form_field(
        'customer.last_name',
        array(
            'type' => 'text',
            'label' =>__( 'Last Name', 'repayas-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );

    woocommerce_form_field(
        'customer.email',
        array(
            'type' => 'text',
            'label' =>__( 'Email', 'repayas-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );

    woocommerce_form_field(
        'customer.mobile',
        array(
            'type' => 'text',
            'label' =>__( 'Phone Number', 'repayas-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );



    echo '</div>';

    $description .= ob_get_clean();

    return $description;
}

function inovidia_repayas_description_fields_validation() {
    if( 'repayas' === $_POST['payment_method'] && ! isset( $_POST['customer.first_name'] )  || empty( $_POST['customer.first_name'] ) ) {
        wc_add_notice( 'Please enter your first name', 'error' );
    }

    if( 'repayas' === $_POST['payment_method'] && ! isset( $_POST['customer.last_name'] )  || empty( $_POST['customer.last_name'] ) ) {
        wc_add_notice( 'Please enter your last name', 'error' );
    }

    if( 'repayas' === $_POST['payment_method'] && ! isset( $_POST['customer.email'] )  || empty( $_POST['customer.email'] ) ) {
        wc_add_notice( 'Please enter your email', 'error' );
    }

    if( 'repayas' === $_POST['payment_method'] && ! isset( $_POST['customer.mobile'] )  || empty( $_POST['customer.mobile'] ) ) {
        wc_add_notice( 'Please enter your phone number', 'error' );
    }
}

function inovidia_checkout_update_order_meta( $order_id ) {
    if( isset( $_POST['customer.first_name'] ) || ! empty( $_POST['customer.first_name'] ) ) {
       update_post_meta( $order_id, 'customer.first_name', $_POST['customer.first_name'] );
    }

    if( isset( $_POST['customer.last_name'] ) || ! empty( $_POST['customer.last_name'] ) ) {
        update_post_meta( $order_id, 'customer.last_name', $_POST['customer.last_name'] );
    }

    if( isset( $_POST['customer.email'] ) || ! empty( $_POST['customer.email'] ) ) {
        update_post_meta( $order_id, 'customer.email', $_POST['customer.email'] );
    }

    if( isset( $_POST['customer.mobile'] ) || ! empty( $_POST['customer.mobile'] ) ) {
        update_post_meta( $order_id, 'customer.mobile', $_POST['customer.mobile'] );
    }
}

function inovidia_order_data_after_billing_address( $order ) {
    echo '<p><strong>' . __( 'First Name:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.first_name', true ) . '</p>';
    echo '<p><strong>' . __( 'Last Name:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.last_name', true ) . '</p>';
    echo '<p><strong>' . __( 'Email:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.email', true ) . '</p>';
    echo '<p><strong>' . __( 'Phone Number:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.mobile', true ) . '</p>';
}

function inovidia_order_item_meta_end( $item_id, $item, $order ) {
    echo '<p><strong>' . __( 'First Name:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.first_name', true ) . '</p>';
    echo '<p><strong>' . __( 'Last Name:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.last_name', true ) . '</p>';
    echo '<p><strong>' . __( 'Email:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.email', true ) . '</p>';
    echo '<p><strong>' . __( 'Phone Number:', 'repayas-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'customer.mobile', true ) . '</p>';
}
