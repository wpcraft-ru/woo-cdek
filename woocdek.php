<?php
/*
Plugin Name: WooCommerce CDEK
Plugin URI: https://github.com/uptimizt/woocommerce-cdek
Description: Интеграция WooCommerce & СДЭК.
Author: WPCraft
Author URI: http://wpcraft.ru/?utm_source=wpplugin&utm_medium=plugin-link&utm_campaign=WooAmoConnector
Version: 0.8.3
*/

require_once 'inc/class-cdek-shipping-method.php';
require_once 'inc/class-cdek-widget.php';
require_once 'inc/class-cdek-service.php';
require_once 'inc/class-cdek-tmpl.php';
require_once 'inc/class-settings.php';

/*
* Add class for init
*/
function woocdek_add_shipping_method( $methods ) {
  $methods['cdek_wpc'] = 'WC_CDEK_Shipping_Method';
  return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woocdek_add_shipping_method' );

/**
* Add Settings link in plugins list
*/
function woocdek_plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=woocdek-settings">Настройки</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'woocdek_plugin_add_settings_link' );
