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
if (DigilanToken::isFromCitybox() || preg_match($re, $secret) == 1) :
?>
  <div class="dlt-admin-content">
    <h1><?php _e('Connection charts', 'digilan-token'); ?></h1>
    <div>
      <div style="position: relative; width: 400px; height: 400px; display: inline-block;">
        <canvas id="repartitionPieChart" style="position: relative; height: 1px; width: 1px"></canvas>
      </div>
      <div style="position: relative; width: 800px; height: 400px; display: inline-block;">
        <canvas id="connectionsChart"></canvas>
      </div>
      <h1><?php _e('List of authenticated users', 'digilan-token'); ?></h1>
      <table>
        <tbody>
          <tr>
            <td><?php _e('Start date', 'digilan-token'); ?></td>
            <td><input type="date" id="dlt-start" name="start"></td>
          </tr>
          <tr>
            <td><?php _e('End date', 'digilan-token'); ?></td>
            <td><input type="date" id="dlt-end" name="end"></td>
          </tr>
        </tbody>
      </table>
      <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row" style="vertical-align: middle;">
                <fieldset><label for="dlt-start-dl">
                    <input type="hidden" id="dlt-start-date" name="dlt-start-date" value="">
                  </label>
                </fieldset>
                <fieldset><label for="dlt-end-dl">
                    <input type="hidden" id="dlt-end-date" name="dlt-end-date" value="">
                  </label></fieldset>
                <input type="hidden" id="dlt-start-date" name="start">
                <input type="hidden" id="dlt-end-date" name="end">
                <input type="submit" name="submit" id="submit-settings" class="button button-primary" value="<?php _e('Download logs', 'digilan-token'); ?>">
              </th>
              <td>
                <fieldset>
                  <label for="download">
                    <input type="hidden" name="action" value="digilan-token-plugin" />
                    <input type="hidden" name="view" value="connections" />
                    <input type="hidden" name="digilan-mail-download" value="download" />
                  </label>
                </fieldset>
              </td>
            </tr>
          </tbody>
        </table>
      </form>
      <table id="connection-table" class="table-bordered stripe row-border hover cell-border">
        <thead>
          <tr>
            <th><?php _e('Access Point', 'digilan-token'); ?></th>
            <th><?php _e('Connection date', 'digilan-token'); ?></th>
            <th><?php _e('Date authentication', 'digilan-token'); ?></th>
            <th><?php _e('Authentication mode', 'digilan-token'); ?></th>
            <th><?php _e('Social ID', 'digilan-token'); ?></th>
            <th><?php _e('User mac', 'digilan-token'); ?></th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif; ?>
