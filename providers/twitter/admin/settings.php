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
  <?php
  $this->renderSettingsHeader();
  ?>

  <form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>" novalidate="novalidate">

    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="provider-<?= esc_attr($provider->getId()); ?>" />
    <input type="hidden" name="subview" value="settings" />
    <input type="hidden" name="settings_saved" value="1" />
    <input type="hidden" name="tested" id="tested" value="<?= esc_attr($settings->get('tested')); ?>" />
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">
            <label for="consumer_key">
              <?php _e('API Key', 'digilan-token'); ?>
              - <em>(<?php _e('Required', 'digilan-token'); ?>)</em>
            </label>
          </th>
          <td>
            <input
              name="consumer_key"
              type="text"
              id="consumer_key"
              value="<?= esc_attr($settings->get('consumer_key')); ?>"
              class="regular-text"
            />
            <p class="description" id="tagline-consumer_key">
              <?php
              printf(
                __('If you are not sure what is your %1$s, please head over to <a href="%2$s">Getting Started</a>', 'digilan-token'),
                'API secret key',
                $this->getUrl()
              );
              ?>
            </p>
          </td>
        </tr>
        <tr>
          <th scope="row">
            <label for="consumer_secret">
              <?php _e('API secret key', 'digilan-token'); ?>
            </label>
          </th>
          <td>
            <input
              name="consumer_secret"
              type="text"
              id="consumer_secret"
              value="<?= esc_attr($settings->get('consumer_secret')); ?>"
              class="regular-text"
              style="width:40em;"
            />
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
    </p>
  </form>
</div>
