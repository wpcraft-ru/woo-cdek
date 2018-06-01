<?php
/**
 * Plugin Name: WooCommerce CDEK
 * Plugin URI: https://github.com/uptimizt/woocommerce-cdek
 * Description: Интеграция WooCommerce & СДЭК.
 * Author: WPCraft
 * Author URI: http://wpcraft.ru/?utm_source=wpplugin&utm_medium=plugin-link&utm_campaign=WooAmoConnector
 * Developer: WPCraft
 * Developer URI: https://wpcraft.ru/
 * Text Domain: woocdek
 * Domain Path: /languages
 * Version: 0.9.0
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.4
 *
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once 'inc/class-cdek-widget.php';
require_once 'inc/class-cdek-service.php';
require_once 'inc/class-cdek-tmpl.php';
require_once 'inc/class-settings.php';
/*
* Add class for init
*/
add_filter( 'woocommerce_shipping_methods', 'woocdek_add_shipping_method' );
function woocdek_add_shipping_method( $methods ) {
	$methods['cdek'] = 'WC_CDEK_Shipping_Method';
	
	return $methods;
}

add_action( 'woocommerce_shipping_init', 'wpc_cdek_shipping_init' );
function wpc_cdek_shipping_init() {
	require_once 'inc/class-cdek-shipping-method.php';
}

/**
 * Add Settings link in plugins list
 */
add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'woocdek_plugin_add_settings_link' );

function woocdek_plugin_add_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=woocdek-settings">Настройки</a>';
	array_unshift( $links, $settings_link );
	
	return $links;
}
