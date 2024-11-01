<div class="sc-first-button">
  <h3><?php _e('Create your first Snapcall button !', 'snapcall'); ?></h3>
  <a class="app-badge" href="https://itunes.apple.com/fr/app/snapcall/id1291870272?mt=8" target="_blank">
    <img src="<?php echo $assets; ?>/img/badge-apple-store-en.svg">
  </a>
  <a class="app-badge" href="https://play.google.com/store/apps/details?id=io.snapcall.snapcall&" target="_blank">
    <img src="<?php echo $assets; ?>/img/badge-google-play-en.png">
  </a>
  <a class="app-badge" href="https://www.zendesk.com/apps/support/snapcall/" target="_blank">
    <img src="<?php echo $assets; ?>/img/logo-zendesk.svg">
  </a>
  <form class="snapcall-form" method="POST" data-endpoint="script|/cms_first_button.php" data-callback="firstButton">
    <p>
      <label class="tooltip" for="sc_agent_id">
        <?php _e('Enter your Agent ID', 'snapcall'); ?>
        <span class="tooltiptext"><?php _e('Your agent ID is available on the smartphone app or Zendesk !', 'snapcall'); ?></span>
      </label>
      <br>
      <input type="text" id="sc_agent_id" name="agent_id" placeholder="23264361381073672" required>
    </p>
    <p class="snapcall-form-error" id="sc_first_button_error"></p>
    <input type="hidden" name="api_key" value="<?php echo $api_key; ?>">
    <input type="hidden" name="api_secret" value="<?php echo $api_secret; ?>">
    <input type="hidden" name="timezone" value="<?php echo $register_data['timezone']; ?>">
    <input type="submit" class="button button-primary button-large" value="<?php _e('Create !', 'snapcall'); ?>">
  </form>
</div>
<p class="snapcall-form-success" id="sc_first_button_success"></p>
