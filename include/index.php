<?php
if (!defined('ABSPATH')) {
  exit;
}
if (isset($_POST['login_email']) && !empty($_POST['login_email']) &&
    isset($_POST['login_password']) && !empty($_POST['login_password'])
) {
  $req = wp_remote_post("{$this->SNAPCALL_API_HOST}/user/login", [
    'body' => [
      'email' => sanitize_email($_POST['login_email']),
      'password' => sanitize_text_field($_POST['login_password'])
    ]
  ]);
  $login = json_decode($req['body']);
  if ($login && $login != 'false') {
    $id = $login->user_id;
    $api_key = $login->api_key;
    $api_secret = $login->api_secret;
    add_option('snapcall_uid', $id);
    add_option('snapcall_api_key', $api_key);
    add_option('snapcall_api_secret', $api_secret);
  } else {
    $login_fail = __('Wrong username or password.', 'snapcall');
  }
}
?>

<script type="text/javascript">
  const sc_user = {
    id: "<?php echo $id; ?>" || false,
    api_key: "<?php echo $api_key; ?>" || false,
    api_secret: "<?php echo $api_secret; ?>" || false,
    link: "<?php echo $link; ?>" || false
  };
  const cfg = {
    api: "<?php echo $this->SNAPCALL_API_HOST; ?>" || false,
    script: "<?php echo $this->SNAPCALL_SCRIPT_HOST; ?>" || false
  };
</script>

<div class="snapcall-content">
  <img src="<?php echo "{$assets}/img/snapcall.png"; ?>">
  <div class="snapcall-body">
    <p id="statusText"></p>
    <?php
      if (!$id || !$api_key || !$api_secret) {
    ?>
        <div class="nav-tab-wrapper woo-nav-tab-wrapper">
          <a href="#login" class="sc-nav-tab nav-tab nav-tab-active"><?php _e('Login', 'snapcall'); ?></a>
          <a href="https://registerwordpress.snapcall.io" target="_blank" class="sc-nav-tab nav-tab"><?php _e('Register', 'snapcall'); ?></a>
        </div>
    <?php
        require('login.php');
      }
      require('first_button.php');
    ?>
    <img id="loader" src="<?php echo "{$assets}/img/loader.gif"; ?>">
  </div>
</div>
