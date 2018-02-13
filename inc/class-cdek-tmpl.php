<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Add endpoint /cdek-tmpl/ for example: https://site.dev/cdek-tmpl/
 */
class WP_SDEK_Template
{
  public function __construct()
  {
    add_action('init', array($this, 'add_endpoint'));
    add_action('wp_loaded', array($this, 'flush_rewrite_rules_hack') );

    add_action('template_redirect', array($this, 'request'));
  }

  function request() {
    $call = get_query_var('cdek-tmpl', false);
    //проверям на наличие запроса в URL Endpoint
    if( $call === false ){
      return;
    }


    header('Access-Control-Allow-Origin: *');
    $files = scandir($D = __DIR__ . '/scripts/tpl');
    unset($files[0]);
    unset($files[1]);

    $arTPL = array();

    foreach ($files as $filesname) {
        $file_tmp = explode('.', $filesname);
      $arTPL[strtolower($file_tmp[0])] = file_get_contents($D . '/' . $filesname);
    }

    echo str_replace(array('\r','\n','\t',"\n","\r","\t"),'',json_encode($arTPL));

    exit;
  }

  function flush_rewrite_rules_hack(){
    $rules = get_option( 'rewrite_rules' );
    if ( ! isset( $rules['cdek-tmpl(/(.*))?/?$'] ) ) {
        flush_rewrite_rules( $hard = false );
    }
  }

  function add_endpoint() {
    add_rewrite_endpoint( 'cdek-tmpl', EP_ROOT );
  }
}
new WP_SDEK_Template;
