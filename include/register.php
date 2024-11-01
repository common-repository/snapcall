<div class="sc-tab" id="register-tab">
  <form class="snapcall-form" method="POST" data-endpoint="script|/cms_register.php" data-callback="register">
    <p>
      <label for="sc_register_name"><?php _e('Name', 'snapcall'); ?></label>
      <br>
      <input type="text" id="sc_register_name" name="name" value="<?php echo $register_data['name']; ?>"  placeholder="<?php _e('Name', 'snapcall'); ?>" required>
    </p>
    <p>
      <label for="sc_register_site_url"><?php _e('Site url', 'snapcall'); ?></label>
      <br>
      <input type="url" id="sc_register_site_url" name="site_url" value="<?php echo $register_data['site_url']; ?>" placeholder="https://*.com" required>
    </p>
    <p>
      <label for="sc_register_email"><?php _e('Email', 'snapcall'); ?></label>
      <br>
      <input type="email" id="sc_register_email" name="email" value="<?php echo $register_data['email']; ?>" placeholder="<?php _e('Email', 'snapcall'); ?>" required>
    </p>
    <p>
      <label for="sc_register_password"><?php _e('Password', 'snapcall'); ?></label>
      <br>
      <input type="password" id="sc_register_password" name="password" placeholder="<?php _e('Password', 'snapcall'); ?>" required>
    </p>
    <p>
      <label for="sc_register_password_confirm"><?php _e('Confirm password', 'snapcall'); ?></label>
      <br>
      <input type="password" id="sc_register_password_confirm" name="password_confirm" placeholder="<?php _e('Confirm password', 'snapcall'); ?>" required>
    </p>
    <p class="snapcall-form-error" id="sc_register_error"><?php echo isset($register_fail) ? $register_fail : null; ?></p>
    <input type="hidden" name="timezone" value="<?php echo $register_data['timezone']; ?>">
    <input type="hidden" name="cms" value="Wordpress">
    <input type="hidden" name="site_email" value="<?php echo $register_data['email']; ?>">
    <input type="submit" class="button button-primary button-large" value="<?php _e('Register', 'snapcall'); ?>">
  </form>
</div>
