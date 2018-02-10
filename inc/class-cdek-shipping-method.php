<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

function wpc_cdek_shipping_init() {
if ( ! class_exists( 'WC_Yandex_Delivery_Method' ) ) {
  class WC_CDEK_Shipping_Method extends WC_Shipping_Method
  {
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
      $this->id                 = 'cdek';
      $this->method_title       = 'СДЭК';
      $this->method_description = __( 'Поддержка системы СДЭК' ); //
      $this->init();
      $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
      $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : "Доставка СДЭК";
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init()
    {
      // Load the settings API
      $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
      $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
      $this->enabled	= $this->get_option( 'enabled' );
      $this->title 		= $this->get_option( 'title' );
      // Save settings in admin if you have any defined
      add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
      add_action('woocommerce_checkout_update_order_meta', array( $this, 'add_order_meta'), 10, 2);
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
      $this->form_fields = array(
        'enabled' => array(
          'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
          'type' 			=> 'checkbox',
          'label' 		=> "",
          'default' 		=> 'no',
        ),
        'title' => array(
          'title' 		=> __( 'Method title', 'woocommerce' ),
          'type' 			=> 'text',
          'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
          'default'		=> "Служба СДЭК",
          'desc_tip'		=> true,
        ),
      );
    }
    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package ) {
      if( ! empty($_REQUEST["post_data"])){
        $post_data = wp_parse_args($_REQUEST["post_data"]);
        // if( ! empty($post_data["yd_cost"])){
        //   $cost = (int)$post_data["yd_cost"];
        //   WC()->session->set( 'yd_cost', $cost );
        // }
      }
      if( ! empty($post_data["yd_params"])){
        $params = json_decode($post_data["yd_params"], true);
        $params = $params[0];
        WC()->session->set( 'yd_params', $params );
      }
      $params = WC()->session->get('yd_params');
      if(empty($params['cost'])){
        $cost = 0;
      } else {
        $cost = $params['cost'];
      }
      if(empty($params["name"])){
        $label = $this->title;
      } else {
        $label = sprintf('%s (%s)', $this->title, $params["name"]);
      }
      $rate = array(
        'id' => $this->id,
        'label' => $label, //@TODO: add name a variant
        'cost' => $cost
      );
      // do_action('logger_u7', ['test4', $rate]); //@TODO: remove logger
      $this->add_rate( $rate );
    }
    function add_order_meta($order_id, $data){
      $order = wc_get_order($order_id);
      $items = $order->get_items();
      $shipping_methods = $order->get_shipping_methods();
      $params = WC()->session->get('yd_params');
      foreach ( $order->get_shipping_methods() as $shipping_method ) {
            $mid = $shipping_method->get_id();
            $shipping_method->update_meta_data( 'yd_id', $params["id"] );
            $shipping_method->update_meta_data( 'yd_delivery_service_id', $params["delivery_service_id"] );
            $shipping_method->update_meta_data( 'yd_unique_name', $params["unique_name"] );
            $shipping_method->update_meta_data( 'yd_name', $params["name"] );
            $shipping_method->save();
            // wc_update_order_item_meta($mid,'dsf', 'sdfsdf');
            // $mid = $shipping_method->get_id();
      }
      // $shipping_method = $shipping_methods[0];
      // do_action('logger_u7', ['test2', $params]); //@TODO: remove logger
    }
  }
}
}
add_action( 'woocommerce_shipping_init', 'wpc_cdek_shipping_init' );
