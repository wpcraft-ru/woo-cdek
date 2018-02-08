<?php

/**
 * CDEK Widget
 */
class CDEK_Widget
{
  function __construct()
  {
    // add_action('woocommerce_checkout_process', array($this, 'checkout_control_errors'));

    add_action('wp_footer', array($this, 'display_js'), 100);

    add_action('woocommerce_after_cart', array($this, 'display_html'));
    add_action('woocommerce_after_checkout_form', array($this, 'display_html'));

    add_action( 'wp_enqueue_scripts', array($this, 'add_scripts') );

    add_action('woocommerce_after_shipping_rate', array($this, 'display_btn_select_ship') );

  }

  /**
  * Добавляем Ссылку и Контейнер для выбора и вывода способов доставки Яндекс
  */
  function display_btn_select_ship($method){

    // do_action('logger_u7', ['t1', $method]);
    if('cdek' == $method->id){
      echo '<input type="hidden" name="cdek_params" id="cdek_params"/>';
      printf('<div><a href="%s" data-ydwidget-open>Выбрать варианты</a></div>', '#cdek-select-variants');
      // echo '<div><small id="delivery_description"></small></div>';
    }
  }


  function add_scripts(){
    // wp_enqueue_script( 'woo-cdek', plugins_url( 'inc/widjet.js' , dirname(__FILE__)), array(), '1.0', true );
  }

  function display_html(){
    ?>
    <!-- Элемент-контейнер виджета. Класс yd-widget-modal обеспечивает отображение виджета в модальном окне -->
    <div id="cdek_widget" class="cdek-widget-modal"></div>

    <!-- элемент для отображения ошибок валидации -->
    <div id="cdek_widget_errors"></div>



    <button onclick="addGood();">Добавить товар</button>

    <div id="forpvz" style="width:100%; height:600px;"></div>
    <div id="service_message"></div>

    <?php
  }

  function display_js()
  {
    if( ! is_checkout()){
      return;
    }

    $cart_data = array(
      'quantity' => WC()->cart->get_cart_contents_count(),
      'weight' => WC()->cart->get_cart_contents_weight(),
      'cost' => WC()->cart->cart_contents_total,
    );

    printf('<script id="ISDEKscript" type="text/javascript" src="%s"></script>', plugins_url( 'inc/widjet.js' , dirname(__FILE__)));

    ?>

    <script type="text/javascript">
      var widjet = new ISDEKWidjet({
        hideMessages: false,
        defaultCity: 'Санкт-Петербург',
        cityFrom: 'Москва',
        country: 'Россия',
        choose: true, //скрыть кнопку выбора
        //path : true,
        link: 'forpvz',
        goods: [{
          length: 10,
          width: 10,
          height: 10,
          weight: 1
        }],
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
        serviceMess(
          'Выбран пункт выдачи заказа ' + wat.id + "\n<br/>" +
          'цена ' + wat.price + "\n<br/>" +
          'срок ' + wat.term + " дн.\n<br/>" +
          'город ' + wat.city
        );
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

        addGood = function () {
          widjet.cargo.add({
            length: 20,
            width: 20,
            height: 20,
            weight: 1
          });
                ipjq('#cntItems').html ( parseInt(ipjq('#cntItems').html()) + 1 );
                ipjq('#weiItems').html ( parseInt(ipjq('#weiItems').html()) + 2 );
        }
    </script>

    <?php
  }

}
new CDEK_Widget;
