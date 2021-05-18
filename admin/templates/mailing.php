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
    <h1><?php _e('Mailing', 'digilan-token'); ?></h1>
    <p><?php _e('For each email in the system we can send a promotional or informative email', 'digilan-token'); ?>.</p>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Frequency', 'digilan-token'); ?>:</th>
            <td>
              <input type="hidden" name="action" value="digilan-token-plugin" />
              <input type="hidden" name="view" value="mailing" />
              <p style="display:inline"><?php _e('We will send the message', 'digilan-token'); ?> </p>
              <input type="number" name="digilan-token-frequency-begin" min="0" max="31" required />
              <p style="display:inline"><?php _e('days after the first connection and then every', 'digilan-token'); ?></p>
              <input type="number" name="digilan-token-frequency" min="1" max="31" required />
              <p style="display:inline"><?php _e('days', 'digilan-token'); ?></p>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Mail subject', 'digilan-token'); ?>:</th>
            <td>
              <fieldset>
                <label for="digilan-token-subject">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="mailing" />
                  <input type="text" name="digilan-token-subject" class="regular-text" required />
                </label>
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row"><?php _e('Mail body', 'digilan-token'); ?>:</th>
            <td>
              <fieldset>
                <label for="digilan-token-body">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="mailing" />
                  <textarea name="digilan-token-body" rows="10" cols="50" required></textarea>
                </label>
              </fieldset>
            </td>
        </tbody>
      </table>
      <input type="submit" name="digilan-token-mailing-submit" class="button button-primary" value="<?php _e('Save', 'digilan-token'); ?>" />
      <input type="email" name="digilan-token-test-mail" placeholder="<?php _e('Email address', 'digilan-token'); ?>" />
      <input type="submit" name="digilan-token-mailing-test-submit" class="button button-" value="<?php _e('Test', 'digilan-token'); ?>" />
    </form>
  </div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif; ?>