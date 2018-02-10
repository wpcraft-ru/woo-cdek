<?php
class WooCDEK_Settings {
  function __construct(){
    add_action('admin_menu', function () {
        add_options_page(
          $page_title = 'СДЭК',
          $menu_title = "СДЭК",
          $capability = 'manage_options',
          $menu_slug = 'woocdek-settings',
          $function = array($this, 'display_settings')
        );
    });
    add_action( 'admin_init', array($this, 'settings_init'), $priority = 10, $accepted_args = 1 );
  }

  function settings_init(){
    add_settings_section(
      'woocdek',
      'Настройки',
      null,
      'woocdek-settings'
    );

    register_setting('woocdek-settings', 'woocdek_key');
    add_settings_field(
      $id = 'woocdek_key',
      $title = 'Ключ доступа API',
      $callback = [$this, 'display_woocdek_key'],
      $page = 'woocdek-settings',
      $section = 'woocdek'
    );

  }

  function display_woocdek_key(){
    $name ='woocdek_key';
    printf('<input type="password" name="%s" value="%s"/>', $name, get_option($name));
    ?>
    <p><small>Получить ключ доступа можно на странице настроек API СДЭК</small></p>
    <?php
  }


  function display_settings(){
    ?>

    <form method="POST" action="options.php">
      <h1>Настройки интеграции СДЭК</h1>
      <?php
        settings_fields( 'woocdek-settings' );
        do_settings_sections( 'woocdek-settings' );
        submit_button();
      ?>
    </form>


    <?php
    printf('<p><a href="%s" target="_blank">Расширенная версия с дополнительными возможностями</a></p>', "https://wpcraft.ru/product/woocommerce-cdek-extra/");
    printf('<p><a href="%s" target="_blank">Помощь и техническая поддержка</a></p>', "https://wpcraft.ru/contacts/");
  }
}
new WooCDEK_Settings;
