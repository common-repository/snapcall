<?php
/*
Plugin Name: Snapcall
Plugin URI:  https://web.snapcall.io/en/
Description: Connect your CMS with Snapcall !
Version:     2.1.0
Author:      Snapcall
Author URI:  https://web.snapcall.io/en/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: snapcall
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
  exit;
}
require('config.php');
date_default_timezone_set(get_option('timezone_string'));

class Snapcall extends SC_Config {
  function __construct() {
    add_action('plugins_loaded', [$this, 'version_upgrade']);
    add_action('admin_menu', [$this, 'admin_menu']);
    add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
    add_action('get_header', [$this, 'load_script']);
    add_action('woocommerce_checkout_order_processed', [$this, 'after_wc_order']);
    add_action('rest_api_init', function() {
      register_rest_route('snapcall/v2', 'get-pages', [
        'methods' => ['POST'],
        'callback' => [$this, 'get_pages']
      ]);
      register_rest_route('snapcall/v2', 'set-context', [
        'methods'  => ['GET', 'POST'],
        'callback' => [$this, 'set_context']
      ]);
    });
    load_plugin_textdomain('snapcall', false, basename(dirname( __FILE__ )).'/languages');
  }

  /**
   * Handle plugin activation
   */
  public function activate() {
    $this->autopilot('installed');
  }

  /**
   * Handle plugin desactivation
   */
  public function desactivate() {
    $uid = get_option('snapcall_uid');
    $api_key = get_option('snapcall_api_key');
    $api_secret = get_option('snapcall_api_secret');
    if ($uid) delete_option('snapcall_uid');
    if ($api_key) delete_option('snapcall_api_key');
    if ($api_secret) delete_option('snapcall_api_secret');
    $this->autopilot('removed');
  }


  /**
   * Handle plugin upgrade
   * It will delete tables used by old versions of Snapcall.
   */
  public function version_upgrade() {
    global $wpdb;

    $snapcall_db_version = get_option('snapcall_db_version');
    if ($snapcall_db_version !== false) {
      $login_table = $wpdb->prefix.'snapcall';
      $button_table = $wpdb->prefix.'snapcall_buttons';
      $context_table = $wpdb->prefix.'snapcall_contexts';
      $order_table = $wpdb->prefix.'snapcall_orders';
      $query = $wpdb->query("
        DROP TABLE IF EXISTS
        `{$login_table}`,
        `{$button_table}`,
        `{$context_table}`,
        `{$order_table}`;
      ");
      if ($query === false) {
        exit("<b><u>Please contact us at hello@snapcall.io with the content of this page.</u></b><br><b>Snapcall plugin upgrade query encountered an error:</b><br>{$wpdb->last_error}<br><b>Query:</b> {$wpdb->last_query}");
      } else {
        delete_option('snapcall_db_version');
      }
    }
  }

  /**
   * Add Snapcall to admin menu
   */
  public function admin_menu() {
    add_menu_page(
        'Snapcall',
        'Snapcall',
        'manage_options',
        'snapcall',
        [$this, 'content'],
        $this->SNAPCALL_ADMIN_ICON,
        58
    );
  }

  /**
   * Enqueue Snapcall admin style and script
   * snapcall_admin_icon is necessary to have a good looking admin icon
   */
  public function admin_assets($hook) {
    wp_enqueue_style('snapcall_admin_icon', plugins_url('assets/css/snapcall_admin_icon.css', __FILE__));
    if($hook == 'toplevel_page_snapcall') {
      $registrationErr = __('An error occured during the registration', 'snapcall');
      $sc_str = [
        'checking' => __('Checking the connection between your CMS and Snapcall..', 'snapcall'),
        'cmsConnected' => __('Your CMS is correctly connected to Snapcall !', 'snapcall'),
        'errStandard' => __('An error occured, please try again later', 'snapcall'),
        'errStandardMail' => __('An error occured, please try again later, if it still doesn\'t work, contact us at hello@snapcall.io.', 'snapcall'),
        'errBrowser' => __('Snapcall plugin do not support your navigator, please upgrade to a more recent one..', 'snapcall'),
        'registerErrName' => __('Your name should contain between 2 and 50 characters', 'snapcall'),
        'registerErrEmail' => __('Your email is not valid', 'snapcall'),
        'registerErrPassword' => __('Your password should contain between 8 and 35 characters', 'snapcall'),
        'registerErrPasswordConfirm' => __('The password confirmation does not match with your password', 'snapcall'),
        'registerErrStripe' => $registrationErr.' (err: stripe)',
        'registerErrUser' => $registrationErr.' (err: user)',
        'registerErrCompany' => $registrationErr.' (err: company)',
        'registerErrUpgrade' => $registrationErr.' (err: upgrade)',
        'registerErrSubscribe' => $registrationErr.' (err: subscribe)',
        'registerErrUpdate' => $registrationErr.' (err: update)',
        'buttonSuccess' => __('Button successfully created !', 'snapcall'),
        'manage' => __('You can now manage your buttons on our', 'snapcall'),
        'backOffice' => __('back office', 'snapcall'),
        'firstButtonErrLicence' => __('Not enough licence available', 'snapcall'),
        'firstButtonErrAgentId' => __('Invalid Agent ID', 'snapcall'),
        'firstButtonErrNotFirst' => __('Is this really your first button ?', 'snapcall')
      ];
      wp_enqueue_style('snapcall_admin_style', plugins_url('assets/css/snapcall_admin_style.css', __FILE__));
      wp_register_script('snapcall_admin_script', plugins_url('assets/js/snapcall_admin_script.js', __FILE__), [], false, true);
      wp_localize_script('snapcall_admin_script', 'sc_str', $sc_str);
      wp_enqueue_script('snapcall_admin_script');
    }
  }

  /**
   * Wordpress REST API endpoint "get-pages"
   * Return a list of Woocommerce categories
   */
  public function get_pages() {
    $args = [
      'taxonomy' => 'product_cat',
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => 0
    ];
    $categories = get_categories($args);
    $categories_names = array_map(function ($cat) {
      $arr = array_intersect_key((array)$cat, array_flip(['name']));
      return $arr['name'];
    }, $categories);
    return $categories_names;
  }

  /**
   * Wordpress REST API endpoint "set-context"
   * Return the current cart as json to set the call context and store the
   * Woocommerce session key to be able to update the cart later
   */
  public function set_context() {
    if (isset($_POST['id_call'])) {
      $call_id = sanitize_text_field($_POST['id_call']);
      $wc_cookie = $_COOKIE['wp_woocommerce_session_'.COOKIEHASH];
      $exploded_wc_cookie = explode('||', $wc_cookie);
      $wc_session_key = (isset($exploded_wc_cookie[0])) ? sanitize_text_field($exploded_wc_cookie[0]) : null;
      wp_remote_post("{$this->SNAPCALL_API_HOST}/call/set_cms_cart", [
        'body' => [
          'call_id' => $call_id,
          'cms_token' => $wc_session_key,
          'cms_url' => site_url('', 'https'),
        ]
      ]);
    }
    $wp_version = get_bloginfo('version');
    $woocommerce = WC();
    $cart = [];
    $cart['cms'] = "Wordpress {$wp_version} | Woocommerce {$woocommerce->version}";
    $cart['d'] = get_woocommerce_currency();
    $cart['t'] = floatval($woocommerce->cart->cart_contents_total);
    $cart['e'] = $woocommerce->customer->get_email();
    $cart['fn'] = $woocommerce->customer->get_first_name();
    $cart['ln'] = $woocommerce->customer->get_last_name();
    if (empty($cart['fn'])) $cart['fn'] = 'Visiteur';
    if (empty($cart['ln'])) $cart['ln'] = $woocommerce->customer->get_id();
    $cart['c'] = [];
    foreach ($woocommerce->cart->cart_contents as $product) {
      $pid = $product['data']->get_id();
      $cart['c'][$pid] = [];
      $cart['c'][$pid]['p'] = floatval($product['data']->get_price());
      $cart['c'][$pid]['q'] = intval($product['quantity']);
      $cart['c'][$pid]['n'] = $product['data']->get_name();
      $image = wp_get_attachment_image_src($product['data']->get_image_id())[0];
      if ($image) {
        if (strpos($image, 'http://') === 0)
          $image = str_replace('http://', '', $image);
        if (strpos($image, 'https://') === 0)
          $image = str_replace('https://', '', $image);
      }
      $cart['c'][$pid]['i'] = ($image) ? $image : '';
    }
    return $cart;
  }

  /**
   * Snapcall admin content
   */
  public function content() {
    $wp_user = wp_get_current_user();
    $register_data = [
      'name' => "{$wp_user->first_name} {$wp_user->last_name}",
      'site_url' => site_url('', 'https'),
      'email' => $wp_user->user_email,
      'timezone' => get_option('timezone_string')
    ];
    $assets = plugins_url('assets', __FILE__);
    $id = get_option('snapcall_uid');
    $api_key = get_option('snapcall_api_key');
    $api_secret = get_option('snapcall_api_secret');
    $link = get_rest_url().'snapcall/v2/get-pages/';
    require('include/index.php');
  }

  /**
   * Enqueue a front script that will check if a Snapcall button have to be
   * displayed on the current page
   */
  public function load_script() {
    global $post;

    wp_register_script('snapcall_front_script', plugins_url('assets/js/snapcall_widget.js', __FILE__), [], '1.0.0', false);
    $terms = wp_get_post_terms($post->ID, 'product_cat');
    $cart_value = WC()->cart->total;
    if (isset($terms[0])) {
      $category = $terms[0]->name;
    } else {
      $category = null;
    }
    $sc_obj = [
      'category' => $category,
      'cart_value' => $cart_value,
      'api' => $this->SNAPCALL_API_HOST,
      'uid' => get_option('snapcall_uid')
    ];
    $url_cart = str_replace('http://', 'https://', get_rest_url().'snapcall/v2/set-context/');
    wp_localize_script('snapcall_front_script', 'sc_obj', $sc_obj);
    wp_localize_script('snapcall_front_script', 'snapcallUrl', $url_cart);
    wp_localize_script('snapcall_front_script', 'snapcallUrlCart', $url_cart);
    wp_enqueue_script('snapcall_front_script');
  }

  /**
   * Hook after a Woocommerce order is placed, allowing us to update the cart
   * in case the current customer has made a call
   */
  public function after_wc_order($order) {
    $order = wc_get_order($order);
    $wc_cookie = $_COOKIE['wp_woocommerce_session_'.COOKIEHASH];
    $exploded_wc_cookie = explode('||', $wc_cookie);
    $wc_session_key = (isset($exploded_wc_cookie[0])) ? sanitize_text_field($exploded_wc_cookie[0]) : null;
    if ($order && $wc_session_key) {
      $request = wp_remote_post("{$this->SNAPCALL_API_HOST}/call/get_cms_cart", [
        'body' => [
          'cms_token' => $wc_session_key,
          'cms_url' => site_url('', 'https')
        ]
      ]);
      if ($request['body']) {
        $json = json_decode($request['body']);
        $call_id = ($json) ? $json->call_id : null;
      }
      if (isset($call_id) && !empty($call_id)) {
        $order_data = $order->get_data();
        $cart = WC()->cart->get_cart();
        $cac = [];
        $cac['total'] = $order_data['total'];
        $cac['email'] = $order_data['billing']['email'];
        $cac['first_name'] = $order_data['billing']['first_name'];
        $cac['last_name'] = $order_data['billing']['last_name'];
        $cac['ref'] = '#'.$order_data['id'];
        $cac['items'] = [];
        foreach ($cart as $item) {
          $arr = [];
          $item_data = $item['data']->get_data();
          $image = wp_get_attachment_image_src($item_data['image_id']);
          $arr['id'] = $item['product_id'];
          $arr['price'] = $item_data['price'];
          $arr['quantity'] = $item['quantity'];
          $arr['name'] = $item_data['name'];
          $arr['image'] = ($image) ? $image[0] : null;
          $cac['items'][$item['product_id']] = $arr;
        }
        $update = wp_remote_post("{$this->SNAPCALL_SCRIPT_HOST}/set_cac.php", [
          'body' => [
            'call_id' => $call_id,
            'cac' => json_encode($cac)
          ]
        ]);
      }
    }
  }

  /**
   * Autopilot function that trigger on plugin activation and desactivation
   * So we can spy on you Ψ(｀▽´)Ψ
   */
  public function autopilot($status) {
    global $woocommerce;

    $base_location = wc_get_base_location();
    $wp_user = wp_get_current_user();
    $version = get_bloginfo('version');
    $url = get_bloginfo('url');
    $locale = get_locale();
    $leadsource = "Wordpress {$version} | Woocommerce {$woocommerce->version}";
    $data = [
      'firstname' => $wp_user->first_name,
      'lastname' => $wp_user->last_name,
      'email' => $wp_user->user_email,
      'phoneNumber' => '',
      'website' => $url,
      'country' => $base_location['country'],
      'language' => $locale,
      'pluginVersion' => '2.0.0',
      'storeVersion' => $leadsource,
      'step' => $status
    ];
    $request = wp_remote_post("{$this->SNAPCALL_SCRIPT_HOST}/data.php", [
      'body' => $data
      ]
    );
  }
}

/** Only initialize if Woocomerce is activated */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  $snapcall = new Snapcall();
  register_activation_hook(__FILE__, [$snapcall, 'activate']);
  register_deactivation_hook(__FILE__, [$snapcall, 'desactivate']);
}
