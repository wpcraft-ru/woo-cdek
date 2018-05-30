<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_CDEK_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->instance_id        = absint( $instance_id );
		$this->id                 = 'cdek';
		$this->method_title       = 'СДЭК';
		$this->method_description = __( 'Служба доставки СДЭК' ); //
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->enabled            = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
		$this->init();
	}
	
	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	private function init() {
		$this->init_form_fields();
		$this->init_settings();
		$this->title = $this->get_option( 'title' );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	
	
	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'title' => array(
				'title'       => __( 'Method title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => "Служба СДЭК",
				'desc_tip'    => true,
			),
		);
	}
	
	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 *
	 * @param mixed $package
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		
		$params = WC()->session->get( 'cdek_ship_data' );
		
		$param_cost    = empty( $params['price']) ? 0 : $params['price'] ;
		$param_label   = empty( $params['label'] ) ? $this->title : sprintf( '%s (%s)', $this->title, 'test' );
		$param_city_id = empty( $params['city_id'] ) ? '' : $params['city_id'];
		$param_time    = empty( $params['time'] ) ? '' : $params['time'];
		$param_address = empty( $params['address'] ) ? '' : $params['address'];
		
		
		$this->add_rate( array(
			'id'        => $this->id,
			'label'     => $param_label,
			'cost'      => $param_cost,
			'meta_data' => array(
				'cdek_id' => $this->id,
				'price'   => $param_cost,
				'city_id' => $param_city_id,
				'time'    => $param_time,
				'address' => $param_address,
			),
		) );
	}
}