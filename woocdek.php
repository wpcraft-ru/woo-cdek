<?php
/*
Plugin Name: WooCommerce CDEK
Plugin URI: https://github.com/uptimizt/woocommerce-cdek
Description: WooCommerce & СДЭК - интеграция.
Author: WPCraft
Author URI: http://wpcraft.ru/?utm_source=wpplugin&utm_medium=plugin-link&utm_campaign=WooAmoConnector
Version: 0.1
*/


require_once 'inc/class-cdek-shipping-method.php';
require_once 'inc/class-cdek-widget.php';

/*
* Add class for init
*/
function woocdek_add_shipping_method( $methods ) {
  $methods['cdek_wpc'] = 'WC_CDEK_Shipping_Method';
  return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woocdek_add_shipping_method' );
