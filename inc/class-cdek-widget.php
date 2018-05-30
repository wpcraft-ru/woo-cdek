<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * CDEK Widget
 */
class CDEK_Widget {
	
	public $base_city;
	public $destination_data;
	
	public function __construct() {
		$this->base_city = get_option( 'woocommerce_store_city', '' );
		
		add_action( 'woocommerce_after_cart', array( $this, 'display_html' ) );
		add_action( 'woocommerce_after_checkout_form', array( $this, 'display_html' ) );
		add_action( 'woocommerce_after_shipping_rate', array( $this, 'display_btn_select_ship' ) );
		
		//Ajax update
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_cdek_shipping' ) );
		add_action( 'wp_ajax_updated_cdek_cart_shipping_method', array( $this, 'update_cdek_cart_shipping' ) );
		add_action( 'wp_ajax_nopriv_updated_cdek_cart_shipping_method', array( $this, 'update_cdek_cart_shipping' ) );
		
		//Add JS
		add_action( 'wp_head', array( $this, 'add_js_params' ) );
		add_action( "wp_enqueue_scripts", array( $this, "wp_enqueue_scripts" ) );
		add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 3 );
		add_action( 'wp_footer', array( $this, 'before_display_js' ), 100 );
		add_action( 'wp_footer', array( $this, 'check_city' ), 200 );
		
	}
	
	public function update_cdek_shipping( $post_data ) {
		$packages = WC()->cart->get_shipping_packages();
		
		foreach ( $packages as $key => $value ) {
			$shipping_session = "shipping_for_package_$key";
			
			unset( WC()->session->$shipping_session );
		}
		
		$params = '';
		
		if ( isset( $post_data ) && ! empty( $post_data ) ) {
			wp_parse_str( $post_data, $post_update );
		}
		
		if ( isset( $post_update['cdek_ship_data'] ) && ! empty( $post_update['cdek_ship_data'] ) ) {
			$params = json_decode( $post_update['cdek_ship_data'], true );
			WC()->session->set( 'cdek_ship_data', $params );
		}
		
		return $params;
	}
	
	public function update_cdek_cart_shipping() {
		
		$packages = WC()->cart->get_shipping_packages();
		
		foreach ( $packages as $key => $value ) {
			$shipping_session = "shipping_for_package_$key";
			
			unset( WC()->session->$shipping_session );
		}
		if ( isset( $_POST['cdek_data'] ) && ! empty( $_POST['cdek_data'] ) ) {
			WC()->session->set( 'cdek_ship_data', $_POST['cdek_data'] );
			do_action( "logger_u7", WC()->session->get( 'cdek_ship_data' ) );
		}
		
		wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );
		WC()->cart->calculate_totals();
		woocommerce_cart_totals();
		wp_die();
	}
	
	public function check_city() {
		
		if ( empty( $this->destination_data['city'] ) ) {
			$city = $this->base_city;
		} else {
			$city = $this->destination_data['city'];
		}
		
		?>
		<script type="text/javascript">

            jQuery(document).ready(function ($) {
                $('#billing_city').on('change', function () {
                    var city = $('#billing_city').val();
                    if (city.length === 0) {
                        WooSDEK_Widget.city.set('<?php echo $city ?>');
                    } else {
                        WooSDEK_Widget.city.set(city);
                    }
                });

            });
		</script>
		
		
		<?php
	}
	
	/**
	 * Add JS lib for SDEK
	 */
	public function wp_enqueue_scripts() {
		if ( ! is_checkout() and ! is_cart() ) {
			return;
		}
		
		wp_enqueue_script( 'sdek-main-js', plugins_url( 'inc/widjet.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', false );
		
	}
	
	/**
	 * Add JS params for SDEK Widget
	 */
	public function add_js_params() {
		?>
		<script id="woo-sdek-js-params" type="text/javascript">
            var ipjq = jQuery;
		</script>
		<?php
	}
	
	/**
	 * Add ID for JS tag. Need for SDEK JS.
	 */
	public function script_loader_tag( $tag, $handle, $src ) {
		if ( $handle == 'sdek-main-js' ) {
			$tag = sprintf( '<script type="text/javascript" id="ISDEKscript" src="%s"></script>', $src );
		}
		
		return $tag;
	}
	
	public function before_display_js() {
		if ( ! is_checkout() and ! is_cart() ) {
			return;
		}
		
		if ( empty( $this->base_city ) ) {
			return;
		}
		
		$this->destination_data = $this->get_destination_data();
		
		/*if ( empty( $this->destination_data['city'] ) ) {
			return;
		}*/
		
		$cart_data = array(
			'quantity' => WC()->cart->get_cart_contents_count(),
			'weight'   => WC()->cart->get_cart_contents_weight(),
			'cost'     => WC()->cart->cart_contents_total,
		);
		
		$goods = $this->get_goods_data();
		
		$this->display_js( $goods );
	}
	
	public function get_destination_data() {
		$ship_data_src = WC()->cart->get_shipping_packages();
		
		if ( empty( $ship_data_src[0]["destination"] ) ) {
			return false;
		} else {
			return $ship_data_src[0]["destination"];
		}
		
	}
	
	public function get_goods_data() {
		$ship_data_src = WC()->cart->get_shipping_packages();
		
		if ( ! empty( $ship_data_src[0]["contents"] ) ) {
			$ship_data = array();
			foreach ( $ship_data_src[0]["contents"] as $item_ship ) {
				$ship_data[] = array(
					'length' => (int) $item_ship["data"]->get_length(),
					'width'  => (int) $item_ship["data"]->get_width(),
					'height' => (int) $item_ship["data"]->get_height(),
					'weight' => $item_ship["data"]->get_weight(),
				);
			}
		} else {
			$ship_data = array();
		}
		
		return $ship_data;
	}
	
	/**
	 * Display JS for Widget SDEK
	 */
	public function display_js( $goods ) {
		
		if ( empty( $this->destination_data['city'] ) ) {
			$city = $this->base_city;
		} else {
			$city = $this->destination_data['city'];
		}
		?>
		
		<script id="woo-sdek-init">
            var WooSDEK_Widget = new ISDEKWidjet({
                hideMessages: true,
                defaultCity: '<?php echo $city ?>',
                cityFrom: '<?php echo $this->base_city ?>',
                country: 'Россия',
                choose: false,
                popup: true,
                goods: <?php echo json_encode( $goods ) ?>,
                servicepath: '<?php echo site_url( 'cdek-service' ) ?>',
                templatepath: '<?php echo site_url( 'cdek-tmpl' ) ?>',
                onReady: onReady,
                onChoose: function (info) {

                    var cdek_ship_data = {
                        'id': info.id,
                        'city_id': info.city,
                        'time': info.term,
                        'price': info.price,
                        'address': info.PVZ.Address
                    };
                    WooSDEK_UpdateCheckout(cdek_ship_data);
                },
                onChooseProfile: function (info) {

                    var cdek_ship_data = {
                        'id': info.id,
                        'city_id': info.city,
                        'time': info.term,
                        'price': info.price
                    };
                    WooSDEK_UpdateCheckout(cdek_ship_data);
                },
                //onCalculate: onCalculate
            });

            function onReady() {
                ipjq('#cdek-widget-open').css('display', 'inline');

                console.log('ready');
            }

            function onCalculate(wat) {
                console.log('calculated', wat);

            }

            var is_blocked = function ($node) {
                return $node.is('.processing') || $node.parents('.processing').length;
            };
            var block = function ($node) {
                if (!is_blocked($node)) {
                    $node.addClass('processing').block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                }
            };
            var unblock = function ($node) {
                $node.removeClass('processing').unblock();
            };


            function WooSDEK_UpdateCheckout(cdek_data) {
                console.log('choice', JSON.stringify(cdek_data));
                if (jQuery('.cart_totals').length === 0) {
                    jQuery('[name="cdek_ship_data"]').val(JSON.stringify(cdek_data));
                    jQuery('form.checkout').trigger('update');
                } else {
                    var data = {
                        action: 'updated_cdek_cart_shipping_method',
                        cdek_data: cdek_data
                    };
                    block(jQuery('div.cart_totals'));
                    jQuery.ajax({
                        type: 'POST',
                        url: wc_cart_params.ajax_url,
                        data: data,
                        success: function (response) {
                            jQuery('.cart_totals').replaceWith(response);
                        },
                        complete: function () {
                            unblock(jQuery('div.cart_totals'));
                        },
                        error: function (response) {
                            console.log('ERROR', response);
                        }
                    });
                }
                WooSDEK_Widget.close();
            }

            function scrollIntoView(eleID) {
                var e = document.getElementById(eleID);
                if (!!e && e.scrollIntoView) {
                    e.scrollIntoView();
                }
            }

            jQuery(document).ready(function ($) {
                $(document).on('click', '#cdek-widget-open', function () {
                    WooSDEK_Widget.open();
                    localStorage.setItem('woocdek_opened', 1);
                });
                $(document.body).on('updated_checkout updated_shipping_method', function () {
                    var e = localStorage.getItem('woocdek_opened');
                    if ($('#shipping_method_0_cdek').is(':checked') && e != 1) {
                        localStorage.setItem('woocdek_opened', 1);
                        WooSDEK_Widget.open();
                    }

                    if (!$('#shipping_method_0_cdek').is(':checked')) {
                        localStorage.removeItem('woocdek_opened');
                    }
                    //console.log('checkout was updated');
                });
            });
		</script>
		<?php
	}
	
	/**
	 * Добавляем Ссылку и Контейнер для выбора и вывода способов доставки
	 */
	public function display_btn_select_ship( $method ) {
		if ( 'cdek' == $method->id ) {
			echo '<input type="hidden" name="cdek_ship_data" id="cdek_ship_data" value=""/>';
			printf( '<div><a href="%s" id="cdek-widget-open" style=’display:none’>Выбрать варианты</a></div>', '#cdek-select-variants' );
		}
	}
	
	public function display_html() {
		if ( empty( $this->base_city ) ) {
			return;
		}
	}
}

new CDEK_Widget;