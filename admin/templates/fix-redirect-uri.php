<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */
?>
<div class="dlt-admin-content">
  <h1 class="title"><?php _e('Fix Oauth Redirect URIs', 'digilan-token'); ?></h1>
  <?php
  /** @var DigilanTokenSocialProvider[] $wrongOauthProviders */
  $wrongOauthProviders = array();
  foreach (DigilanToken::$allowedProviders as $provider) {
    if ($provider->getState() != 'configured') {
      continue;
    }
    if (!$provider->checkOauthRedirectUrl()) {
      $wrongOauthProviders[] = $provider;
    }
  }

  if (count($wrongOauthProviders) === 0) {
      echo '<div class="updated"><p>' . __('Every Oauth Redirect URI seems fine', 'digilan-token') . '</p></div>';
  } else {
  ?>
  <p>
    <?php printf(__('%s detected that your login url changed. You must update the Oauth redirect URIs in the related social applications.', 'digilan-token'), '<b>Digilan Token</b>'); ?>
  </p>
  <?php
  foreach ($wrongOauthProviders as $provider) {
    $provider->getAdmin()->renderOauthChangedInstruction();
  }
  ?>
    <a
      href="<?php echo wp_nonce_url(DigilanTokenAdmin::getAdminUrl('update_oauth_redirect_url'), 'digilan-token-plugin_update_oauth_redirect_url'); ?>"
      class="button button-primary"
    >
      <?php _e('Got it', 'digilan-token'); ?>
    </a>
  <?php
  } 
  ?>
</div>
