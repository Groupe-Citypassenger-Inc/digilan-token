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
console_log($_POST);
defined('ABSPATH') || die();?>
<div class="dlt-admin-content">
  <h1><?php _e('Form configuration', 'digilan-token'); ?></h1>
  <h2><?php _e('Select the fields you want to know about the visitors of your city:', 'digilan-token'); ?></h1>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="digilan-token-settings-form">
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="form" />
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('First name', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="first-name">
 
                <input type="checkbox" name="digilan-token-first-name" id="dlt-first-name"  value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('first-name'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Last name', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="last-name">
                <input type="checkbox" name="digilan-token-last-name" id="dlt-last-name" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('last-name'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Gender', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="gender">
                <input type="checkbox" name="digilan-token-gender" id="dlt-gender" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('gender'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Age', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="age">
                <input type="checkbox" name="digilan-token-age" id="dlt-age" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('age'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Nationality', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="nationality">
                <input type="checkbox" name="digilan-token-nationality" id="dlt-nationality" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('nationality'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Email address', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="email-address">
                <input type="checkbox" name="digilan-token-email-address" id="dlt-email-address" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('email-address'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Phone number', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="phone-number">
                <input type="checkbox" name="digilan-token-phone-number" id="dlt-phone-number" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('phone-number'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Length of stay', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="stay-length">
                <input type="checkbox" name="digilan-token-stay-length" id="dlt-stay-length" value="0" <?php echo DigilanTokenAdmin::get_is_checked_field('stay-length'); ?> />
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit-settings-form" class="button button-primary" value="<?php _e('Save settings', 'digilan-token'); ?>">
    </p>
  </form>
</div>
