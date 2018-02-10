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

    add_action('wp_footer', array($this, 'display_js'), 100);

    add_action('woocommerce_after_cart', array($this, 'display_html'));
    add_action('woocommerce_after_checkout_form', array($this, 'display_html'));



    add_action('woocommerce_after_shipping_rate', array($this, 'display_btn_select_ship') );

    $this->base_city = get_option( 'woocommerce_store_city', '' );


  }



  function display_js()
  {
    if( ! is_checkout()){
      return;
    }


    if(empty($this->base_city)){
      return;
    }

    $this->destination_data = $this->get_destination_data();

    if(empty($this->destination_data['city'])){
      return;
    }

    $cart_data = array(
      'quantity' => WC()->cart->get_cart_contents_count(),
      'weight' => WC()->cart->get_cart_contents_weight(),
      'cost' => WC()->cart->cart_contents_total,
    );

    $goods = $this->get_goods_data();


    // do_action('logger_u7', $this->base_city);

    printf('<script id="ISDEKscript" type="text/javascript" src="%s"></script>', plugins_url( 'inc/widjet.js' , dirname(__FILE__)));

    ?>

    <script id="woo-sdek-init" type="text/javascript">

      var WooSDEK_Widget = new ISDEKWidjet({
        hideMessages: true,
        defaultCity: '<?php echo $this->destination_data['city'] ?>',
        cityFrom: '<?php echo $this->base_city ?>',
        country: 'Россия',
        choose: true, //скрыть кнопку выбора
        popup: true,
        //path : true,
        // link: 'forpvz',
        goods: <?php echo json_encode($goods) ?>,
        // goods: [{
        //   length: 10,
        //   width: 10,
        //   height: 10,
        //   weight: 1
        // }],
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
        }];

        document.getElementById('cdek_ship_data').value = JSON.stringify(cdek_ship_data);

        document.getElementById("order_review_heading").scrollIntoView();

        serviceMess(
          'Выбран пункт выдачи заказа ' + wat.id + "\n<br/>" +
          'цена ' + wat.price + "\n<br/>" +
          'срок ' + wat.term + " дн.\n<br/>" +
          'город ' + wat.city
        );

        jQuery( 'form.checkout' ).trigger( 'update' );
      }

      function onChooseProfile(wat) {
        console.log('chosenCourier', wat);
        serviceMess(
          'Выбрана доставка курьером в город ' + wat.city + "\n<br/>" +
          'цена ' + wat.price + "\n<br/>" +
          'срок ' + wat.term + ' дн.'
        );
      }

      function onCalculate(wat) {
        console.log('calculated', wat);
      }

        // addGood = function () {
        //   widjet.cargo.add({
        //     length: 20,
        //     width: 20,
        //     height: 20,
        //     weight: 1
        //   });
        //         ipjq('#cntItems').html ( parseInt(ipjq('#cntItems').html()) + 1 );
        //         ipjq('#weiItems').html ( parseInt(ipjq('#weiItems').html()) + 2 );
        // }
    </script>

    <script>
      window.servmTimeout = false;
      serviceMess = function (text) {
        clearTimeout(window.servmTimeout);
        ipjq('#service_message').show().html(text);
        window.servmTimeout = setTimeout(function () {
          ipjq('#service_message').fadeOut(1000);
        }, 4000);
      }
    </script>

    <script type="text/javascript">
      jQuery( function( $ ) {

        $( document ).on( 'click', '#cdek-widget-open', function() {
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

  /**
  * Добавляем Ссылку и Контейнер для выбора и вывода способов доставки Яндекс
  */
  function display_btn_select_ship($method){

    // do_action('logger_u7', ['t1', $method]);
    if('cdek' == $method->id){
      echo '<input type="hidden" name="cdek_ship_data" id="cdek_ship_data"/>';
      printf('<div><a href="%s" id="cdek-widget-open">Выбрать варианты</a></div>', '#cdek-select-variants1');
      // echo '<div><small id="delivery_description"></small></div>';
    }
  }



  function display_html(){

    if(empty($this->base_city)){
      return;
    }
    ?>
    <!-- <button onclick="addGood();">Добавить товар</button> -->
    <br id="cdek-select-variants">
    <br>
    <br>
    <p><strong>Выберите точку доставки</strong></p>
    <div id="forpvz" style="width:100%; height:600px;"></div>
    <div id="service_message"></div>

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
