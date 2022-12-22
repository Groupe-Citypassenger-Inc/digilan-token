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
$re = '/^[0-9A-Za-z]{32}$/';
$secret = get_option('digilan_token_secret');
if (preg_match($re, $secret) == 1) :
?>
  <div class="dlt-admin-content">
    <h1><?php _e('Logs', 'digilan-token'); ?></h1>
    <form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>" novalidate="novalidate">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Download DNS logs', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="download">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="logs" />
                  <?php _e('Start date', 'digilan-token'); ?>
                  <input type="date" name="digilan-token-start" value="" />
                  <?php _e('End date', 'digilan-token'); ?>
                  <input type="date" name="digilan-token-end" value="" />
                  <input type="hidden" name="digilan-download" value="download" />
                </label>
              </fieldset>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="submit">
        <input
          type="submit"
          name="submit"
          id="submit-settings"
          class="button button-primary"
          value="<?php _e('Download', 'digilan-token'); ?>"
        />
      </p>
    </form>
  </div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif; ?>
