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
defined('ABSPATH') || die();?>
<div class="dlt-admin-content">
  <h1><?php _e('Settings', 'digilan-token'); ?></h1>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Cityscope Cloud', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="backend" id="dlt-test-result">
              <input type="hidden" name="action" value="digilan-token-plugin" />
              <input type="hidden" name="view" value="settings" />
              <input type="text" class="regular-text" id="dlt-cityscope-input" required name="cityscope-backend" value="<?php echo get_option('cityscope_backend');?>" pattern="^http(s)?:\/\/\w+(.\w+)+(:\d+)?$" placeholder="https://admin.citypassenger.com/2019/Portals" />
              <input type="button" name="dlt-test-cityscope" id="dlt-test-cityscope" class="button button-primary" value="<?php _e('Test Cityscope', 'digilan-token');?>" />
              <p style="display: none; color:green" id="valid-portal"><?php _e('Valid portal', 'digilan-token'); ?></p>
              <p style="display: none; color:red" id="invalid-portal"><?php _e('Invalid portal', 'digilan-token'); ?></p>
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit-settings" class="button button-primary" value="<?php _e('Save settings', 'digilan-token'); ?>" disabled>
    </p>
  </form>
</div>
