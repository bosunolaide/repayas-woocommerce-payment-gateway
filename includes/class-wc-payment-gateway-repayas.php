<?php

/**
 * Repayas Payment Gateway.
 *
 * Provides a Repayas Payment Gateway.
 *
 * @class       WC_Gateway_Repayas
 * @extends     WC_Payment_Gateway
 * @version     2.1.0
 * @package     WooCommerce/Classes/Payment
 */
class WC_Gateway_Repayas extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->public_key         = $this->get_option( 'public_key' );
		$this->secret_key         = $this->get_option( 'secret_key' );
		$this->ipn_url            = $this->get_option( 'ipn_url' );
		$this->success_url        = $this->get_option( 'success_url' );
		$this->cancel_url         = $this->get_option( 'cancel_url' );
		$this->site_name          = $this->get_option( 'site_name' );
		$this->site_logo          = $this->get_option( 'site_logo' );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
		$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
		
		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id                 = 'repayas';
		$this->icon               = apply_filters( 'woocommerce_repayas_icon', plugins_url('../assets/icon.png', __FILE__ ) );
		$this->method_title       = __( 'Repayas', 'repayas-payments-woo' );
		$this->public_key         = __( 'Add API Public Key', 'repayas-payments-woo' );
		$this->secret_key         = __( 'Add API Secret Key', 'repayas-payments-woo' );
		$this->method_description = __( 'Have your customers pay with Repayas.', 'repayas-payments-woo' );
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'         	=> array(
				'title'       => __( 'Enable/Disable', 'repayas-payments-woo' ),
				'label'       => __( 'Enable Repayas', 'repayas-payments-woo' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'           	=> array(
				'title'       => __( 'Title', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Repayas Payment method description that the customer will see on your checkout.', 'repayas-payments-woo' ),
				'default'     => __( 'Repayas', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'public_key'      	=> array(
				'title'       => __( 'Public Key', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your API public key', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'secret_key'      	=> array(
				'title'       => __( 'Secret Key', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your API secret key', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'ipn_url'         	=> array(
				'title'       => __( 'Instant Payment Notification', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your Instant Payment Notification URL', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'success_url'     	=> array(
				'title'       => __( 'Success URL', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your Payment Success redirect url', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'cancel_url'     	=> array(
				'title'       => __( 'Cancel URL', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your Payment Cancel redirect url', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'site_name'     	=> array(
				'title'       => __( 'Site Name', 'repayas-payments-woo' ),
				'type'        => 'text',
				'description' => __( 'Add your Site Name', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'site_logo'     	=> array(
				'title'       => __( 'Site Logo', 'repayas-payments-woo' ),
				'type'        => 'url',
				'description' => __( 'Add your Site Logo URL', 'repayas-payments-woo' ),
				'desc_tip'    => false,
			),
			'description'     	=> array(
				'title'       => __( 'Description', 'repayas-payments-woo' ),
				'type'        => 'textarea',
				'description' => __( 'Repayas Payment method description that the customer will see on your website.', 'repayas-payments-woo' ),
				'default'     => __( 'Repayas Payment before delivery.', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'instructions'    	=> array(
				'title'       => __( 'Instructions', 'repayas-payments-woo' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page.', 'repayas-payments-woo' ),
				'default'     => __( 'Repayas Payment before delivery.', 'repayas-payments-woo' ),
				'desc_tip'    => true,
			),
			'enable_for_methods' 	=> array(
				'title'             => __( 'Enable for shipping methods', 'repayas-payments-woo' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'css'               => 'width: 400px;',
				'default'           => '',
				'description'       => __( 'If repayas is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'repayas-payments-woo' ),
				'options'           => $this->load_shipping_method_options(),
				'desc_tip'          => true,
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select shipping methods', 'repayas-payments-woo' ),
				),
			),
			'enable_for_virtual' 	=> array(
				'title'   => __( 'Accept for virtual orders', 'repayas-payments-woo' ),
				'label'   => __( 'Accept repayas if the order is virtual', 'repayas-payments-woo' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),
		);
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first.
		if ( WC()->cart && WC()->cart->needs_shipping() ) {
			$needs_shipping = true;
		} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			// Test if order needs shipping.
			if ( 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					$_product = $item->get_product();
					if ( $_product && $_product->needs_shipping() ) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}

		$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

		// Virtual order, with virtual disabled.
		if ( ! $this->enable_for_virtual && ! $needs_shipping ) {
			return false;
		}

		// Only apply if all packages are being shipped via chosen method, or order is virtual.
		if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
			$order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
			$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			if ( $order_shipping_items ) {
				$canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
			} else {
				$canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
			}

			if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
				return false;
			}
		}

		return parent::is_available();
	}

	/**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	private function is_accessing_settings() {
		if ( is_admin() ) {
			// phpcs:disable WordPress.Security.NonceVerification
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' !== $_REQUEST['page'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['tab'] ) || 'checkout' !== $_REQUEST['tab'] ) {
				return false;
			}
			if ( ! isset( $_REQUEST['section'] ) || 'repayas' !== $_REQUEST['section'] ) {
				return false;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			return true;
		}

		return false;
	}

	/**
	 * Loads all of the shipping method options for the enable_for_methods field.
	 *
	 * @return array
	 */
	private function load_shipping_method_options() {
		// Since this is expensive, we only want to do it if we're actually on the settings page.
		if ( ! $this->is_accessing_settings() ) {
			return array();
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();

		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		$zones[] = new WC_Shipping_Zone( 0 );

		$options = array();
		foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

			$options[ $method->get_method_title() ] = array();

			// Translators: %1$s shipping method name.
			$options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'repayas-payments-woo' ), $method->get_method_title() );

			foreach ( $zones as $zone ) {

				$shipping_method_instances = $zone->get_shipping_methods();

				foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

					if ( $shipping_method_instance->id !== $method->id ) {
						continue;
					}

					$option_id = $shipping_method_instance->get_rate_id();

					// Translators: %1$s shipping method title, %2$s shipping method id.
					$option_instance_title = sprintf( __( '%1$s (#%2$s)', 'repayas-payments-woo' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

					// Translators: %1$s zone name, %2$s shipping method instance name.
					$option_title = sprintf( __( '%1$s &ndash; %2$s', 'repayas-payments-woo' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'repayas-payments-woo' ), $option_instance_title );

					$options[ $method->get_method_title() ][ $option_id ] = $option_title;
				}
			}
		}

		return $options;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
	 * @return array $canonical_rate_ids    Rate IDs in a canonical format.
	 */
	private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

		$canonical_rate_ids = array();

		foreach ( $order_shipping_items as $order_shipping_item ) {
			$canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
		}

		return $canonical_rate_ids;
	}

	/**
	 * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
	 *
	 * @since  3.4.0
	 *
	 * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
	 * @return array $canonical_rate_ids  Rate IDs in a canonical format.
	 */
	private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

		$shipping_packages  = WC()->shipping()->get_packages();
		$canonical_rate_ids = array();

		if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
			foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
				if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
					$chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
					$canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
				}
			}
		}

		return $canonical_rate_ids;
	}

	/**
	 * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
	 *
	 * @since  3.4.0
	 *
	 * @param array $rate_ids Rate ids to check.
	 * @return boolean
	 */
	private function get_matching_rates( $rate_ids ) {
		// First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
		return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
	}

   /**
    * Process the payment and redirect to the payment gateway URL.
    *
    * @param int $order_id Order ID.
    * @return array|void
    */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Make an API call to the payment gateway to obtain the payment URL
		$payment_url = $this->get_payment_url($order);

        if ($payment_url) {
            // Redirect to the payment gateway URL
            return array(
                'result'   => 'success',
                'redirect' => $payment_url
            );
        } else {
            // Handle the case when payment URL retrieval fails
            wc_add_notice('Payment URL retrieval failed. Please try again later.', 'error');
            return;
        }
	}


	/**
     * Get the payment URL from the payment gateway API.
     *
     * @param WC_Order $order Order object.
     * @return string|false Payment URL or false on failure.
     */

	private function get_payment_url( $order ) {

		// Make an API call to the payment gateway to obtain the payment URL
        // Replace this with your actual API integration code

		$identifier = strval( $order->get_id() );

		$currency = strval( $order->get_currency() );

		$amount = intval( $order->get_total() );

		$first_name = $order->get_billing_first_name();

		$last_name = $order->get_billing_last_name();

		$email = $order->get_billing_email();

		$mobile = $order->get_billing_phone();

		foreach ( $order->get_items() as $item_id => $item ) {

		$product_name = $item->get_name();	

		}


		$parameters = [
			'identifier' => $identifier,
			'currency' => $currency,
			'amount' => $amount,
			'gateway_methods' => [
				// Please write the name of the gateway method you want to use
				'stripe', 'stripejs', 'stripev3'
			],
			'details' => $product_name ,
			'ipn_url' => $this->ipn_url,
			'cancel_url' => $this->cancel_url,
			'success_url' => $this->success_url,
			'public_key' => $this->public_key,
			'site_name' => $this->site_name,
			'site_logo' => $this->site_logo,
			'checkout_theme' => 'light',
			'customer'=>[
				'first_name'=>$first_name,
				'last_name'=>$last_name,
				'email'=>$email,
				'mobile'=>$mobile,
			],
			'shipping_info'=>[
				'address_one'=>'',
				'address_two'=>'',
				'area'=>'',
				'city'=>'',
				'sub_city'=>'',
				'state'=>'',
				'postcode'=>'',
				'country'=>'',
				'others'=>'',
			],
			'billing_info'=>[
				'address_one'=>'',
				'address_two'=>'',
				'area'=>'',
				'city'=>'',
				'sub_city'=>'',
				'state'=>'',
				'postcode'=>'',
				'country'=>'',
				'others'=>'',
			]
		];


		//include if condition for changing API URL from test to live.
		
		$response = wp_remote_post('https://repayas.com/test/payment/initiate', array(
			'method' => 'POST',
			'headers' => array(
				'content-Type' => 'application/json; charset=utf-8'
			),
			'httpversion' => '1.0',
			'sslverify' => false,
			'body' => json_encode($parameters),
			'timeout' => 45
		));

		// Check for API response success and extract the payment URL
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
		
			if (isset($response_data['redirect_url'])) {
				return $response_data['redirect_url'];
			}
		}

		return false; // Return false if payment URL retrieval fails
	}
			
			/*
			if ( 'Thank you! Your payment was successful' === $response_body) {
				$order->payment_complete();

				// Remove cart.
				WC()->cart->empty_cart();

				// Return thankyou redirect.
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ), 
				);
			} */
	
	

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Change payment complete order status to completed for repayas orders.
	 *
	 * @since  3.1.0
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && 'repayas' === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}
}