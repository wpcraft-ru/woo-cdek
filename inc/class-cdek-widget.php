<?php

/**
 * CDEK Widget
 */
class CDEK_Widget
{

  public $base_city;
  public $destination_data;

  function __construct()
  {
    // add_action('woocommerce_checkout_process', array($this, 'checkout_control_errors'));
    $this->base_city = get_option( 'woocommerce_store_city', '' );


    add_action('woocommerce_after_cart', array($this, 'display_html'));
    add_action('woocommerce_after_checkout_form', array($this, 'display_html'));

    add_action('woocommerce_after_shipping_rate', array($this, 'display_btn_select_ship') );

    //Add JS
    add_action( 'wp_head', array($this, 'add_js_params'));
    add_action( "wp_enqueue_scripts", array( $this, "wp_enqueue_scripts" ) );
    add_filter( 'script_loader_tag', array( $this, 'script_loader_tag'), 10, 3 );
    add_action('wp_footer', array($this, 'before_display_js'), 100);
    add_action('wp_footer', array($this, 'check_city'), 200);

  }

  function check_city(){

    if(empty($this->destination_data['city'])){
      $city = $this->base_city;
    } else {
      $city = $this->destination_data['city'];
    }

    ?>
    <script type="text/javascript">

    jQuery( function( $ ) {
      $('#billing_city').on('change', function() {
        var city = $('#billing_city').val();
        if( ! city){
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
  public function wp_enqueue_scripts()
  {
    wp_enqueue_script( 'sdek-main-js', plugins_url('inc/widjet.js' , dirname(__FILE__)), array('jquery'), '1.0', true);

    // wp_add_inline_script('sdek-main-js', 'var ipjq = jQuery;', 'before'); not worked with hook script_loader_tag
  }

  /**
   * Add JS params for SDEK Widget
   */
  public function add_js_params()
  {
    ?>
    <script id="woo-sdek-js-params" type="text/javascript">
      var ipjq = jQuery;
    </script>
    <?php
  }

  /**
   * Add ID for JS tag. Need for SDEK JS.
   */
  function script_loader_tag( $tag, $handle, $src )
  {
    if($handle == 'sdek-main-js'){
      $tag = sprintf('<script type="text/javascript" id="ISDEKscript" src="%s"></script>', $src);
    }
    return $tag;
  }

  /**
   * Display JS for Widget SDEK
   */
  public function display_js($goods) {

    if(empty($this->destination_data['city'])){
      $city = $this->base_city;
    } else {
      $city = $this->destination_data['city'];
    }
    ?>

    <script id="woo-sdek-init" type="text/javascript">

      var WooSDEK_Widget = new ISDEKWidjet({
        hideMessages: true,
        defaultCity: '<?php echo $city ?>',
        cityFrom: '<?php echo $this->base_city ?>',
        country: 'Россия',
        choose: false, //скрыть кнопку выбора
        popup: true,
        // path : true,
        // link: 'forpvz',
        goods: <?php echo json_encode($goods) ?>,
        // goods: [{
        //   length: 10,
        //   width: 10,
        //   height: 10,
        //   weight: 1
        // }],
        servicepath: '<?php echo site_url('cdek-service') ?>',
        templatepath: '<?php echo site_url('cdek-tmpl') ?>',
        onReady: onReady,
        onChoose: onChoose,
        onChooseProfile: onChooseProfile,
        onCalculate: onCalculate
      });

      function onReady() {
        console.log('ready');
      }

      function onChoose(wat) {
        console.log('chosen', wat);

        var cdek_ship_data = [{
          'id': wat.id,
          'city_id': wat.city,
          'time': wat.term,
          'price': wat.price,
          'address': wat.PVZ.Address,
        }];

        document.getElementById('cdek_ship_data').value = JSON.stringify(cdek_ship_data);

        document.getElementById("order_review_heading").scrollIntoView();

        // serviceMess(
        //   'Выбран пункт выдачи заказа ' + wat.id + "\n<br/>" +
        //   'цена ' + wat.price + "\n<br/>" +
        //   'срок ' + wat.term + " дн.\n<br/>" +
        //   'город ' + wat.city
        // );

        jQuery( 'form.checkout' ).trigger( 'update' );
      }

      function onChooseProfile(wat) {
        console.log('chosenCourier', wat);
        // serviceMess(
        //   'Выбрана доставка курьером в город ' + wat.city + "\n<br/>" +
        //   'цена ' + wat.price + "\n<br/>" +
        //   'срок ' + wat.term + ' дн.'
        // );
      }

      function onCalculate(wat) {
        console.log('calculated', wat);
      }

      jQuery( function( $ ) {

        $( document ).on( 'click', '#cdek-widget-open', function() {
          // alert(1);
          WooSDEK_Widget.open();
          localStorage.setItem('woocdek_opened', 1);

        });


        // var cdek_not_opened = true;

        $( document.body ).on( 'updated_checkout', function(){

          var e = localStorage.getItem('woocdek_opened');
          if($('#shipping_method_0_cdek').is(':checked') && e != 1) {
            localStorage.setItem('woocdek_opened', 1);
            WooSDEK_Widget.open();
          }

          if( ! $('#shipping_method_0_cdek').is(':checked')){
            localStorage.removeItem('woocdek_opened');
          }
        });
      });

      function scrollIntoView(eleID) {
         var e = document.getElementById(eleID);
         if (!!e && e.scrollIntoView) {
             e.scrollIntoView();
         }
      }

    </script>
    <?php
  }

  function before_display_js()
  {
    if( ! is_checkout() and ! is_cart() ){
      return;
    }

    if(empty($this->base_city)){
      return;
    }

    $this->destination_data = $this->get_destination_data();

    if(empty($this->destination_data['city'])){
      // return;
    }

    do_action('logger_u7', ['t1', 4]);

    $cart_data = array(
      'quantity' => WC()->cart->get_cart_contents_count(),
      'weight' => WC()->cart->get_cart_contents_weight(),
      'cost' => WC()->cart->cart_contents_total,
    );

    $goods = $this->get_goods_data();

    // do_action('logger_u7', ['t2', $goods]);

    $this->display_js($goods);
  }

  /**
  * Добавляем Ссылку и Контейнер для выбора и вывода способов доставки Яндекс
  */
  function display_btn_select_ship($method)
  {
    if('cdek' == $method->id){
      echo '<input type="hidden" name="cdek_ship_data" id="cdek_ship_data"/>';
      printf('<div><a href="%s" id="cdek-widget-open">Выбрать варианты</a></div>', '#cdek-select-variants');
    }
  }


  function display_html()
  {
    if(empty($this->base_city)){
      return;
    }
    ?>
    <!-- <button onclick="addGood();">Добавить товар</button> -->
    <!-- <br id="cdek-select-variants"> -->
    <!-- <p><strong>Выберите точку доставки</strong></p> -->
    <!-- <div id="forpvz" style="width:100%; height:600px;"></div> -->
    <!-- <div id="service_message"></div> -->

    <?php
  }

  function get_goods_data(){
    $ship_data_src = WC()->cart->get_shipping_packages();
    if( ! empty($ship_data_src[0]["contents"]) ){
      $ship_data = array();
      foreach($ship_data_src[0]["contents"] as $item_ship){
        $ship_data[] = array(
          'length' => (int)$item_ship["data"]->get_length(),
          'width' => (int)$item_ship["data"]->get_width(),
          'height' => (int)$item_ship["data"]->get_height(),
          'weight' => $item_ship["data"]->get_weight(),
        );
      }
    } else {
      $ship_data = array();
    }


    // do_action('logger_u7', ['t1', $ship_data_src[0]["destination"]["city"]]);

    return $ship_data;
  }

  function get_destination_data()
  {
    $ship_data_src = WC()->cart->get_shipping_packages();
    if(empty($ship_data_src[0]["destination"])){
      return false;
    } else {
      return $ship_data_src[0]["destination"];
    }
  }


}
new CDEK_Widget;
