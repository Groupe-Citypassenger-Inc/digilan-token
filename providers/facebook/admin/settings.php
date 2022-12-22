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

defined('ABSPATH') || die();
/** @var $this DigilanTokenSocialProviderAdmin */

$provider = $this->getProvider();

$settings = $provider->settings;
?>

<div class="dlt-admin-sub-content">
  <?php if (substr($provider->getLoginUrl(), 0, 8) !== 'https://') : ?>
    <div class="error">
      <p>
        <?php
        printf(
          __('%1$s allows HTTPS OAuth Redirects only. You must move your site to HTTPS in order to allow login with %1$s.', 'digilan-token'),
          'Facebook'
        );
        ?>
      </p>
    </div>
  <?php endif; ?>
  <?php
  $this->renderSettingsHeader();
  ?>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">

    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="provider-<?php echo $provider->getId(); ?>" />
    <input type="hidden" name="subview" value="settings" />
    <input type="hidden" name="settings_saved" value="1" />
    <input type="hidden" name="tested" id="tested" value="<?php echo esc_attr($settings->get('tested')); ?>" />
    <table class="form-table">
      <tbody>
        <?php if (!defined('DIGILAN_FB_APP_ID')) : ?>
          <tr>
            <th scope="row">
              <label for="appid">
                <?php _e('App ID', 'digilan-token'); ?>
                - <em>(<?php _e('Required', 'digilan-token'); ?>)</em>
              </label>
            </th>
            <td>
              <input
                name="appid"
                type="text"
                id="appid"
                value="<?php echo esc_attr($settings->get('appid')); ?>"
                class="regular-text"
              />
              <p class="description" id="tagline-appid">
                <?php
                printf(
                  __('If you are not sure what is your %1$s, please head over to <a href="%2$s">Getting Started</a>', 'digilan-token'),
                  'App ID',
                  $this->getUrl()
                );
                ?>
              </p>
            </td>
          </tr>
        <?php endif; ?>
        <?php if (!defined('DIGILAN_FB_APP_SECRET')) : ?>
          <tr>
            <th scope="row">
              <label for="secret"><?php _e('App Secret', 'digilan-token'); ?>
                - <em>(<?php _e('Required', 'digilan-token'); ?>)</em>
              </label>
            </th>
            <td>
              <input
                name="secret"
                type="text"
                id="secret"
                value="<?php echo esc_attr($settings->get('secret')); ?>"
                class="regular-text"
              />
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
    </p>

  </form>
</div>
