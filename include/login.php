<div class="sc-tab tab-content-active" id="login-tab">
  <form class="snapcall-form" id="snapcall_login_form" action="" method="POST">
    <p>
      <label for="sc_login_email"><?php _e('Email', 'snapcall'); ?></label>
      <br>
      <input type="email" id="sc_login_email" name="login_email" placeholder="<?php _e('Email', 'snapcall'); ?>" required>
    </p>
    <p>
      <label for="sc_login_password"><?php _e('Password', 'snapcall'); ?></label>
      <br>
      <input type="password" id="sc_login_password" name="login_password" placeholder="<?php _e('Password', 'snapcall'); ?>" required>
    </p>
    <p class="snapcall-form-error"><?php echo isset($login_fail) ? $login_fail : null; ?></p>
    <input type="submit" class="button button-primary button-large" value="<?php _e('Login', 'snapcall'); ?>">
  </form>
</div>
