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
$settings = DigilanToken::$settings;
DigilanTokenActivator::cityscope_bonjour();
$secret = get_option("digilan_token_secret");
$re = "/^[0-9A-Za-z]{32}$/";
if (preg_match($re, $secret) == 1) :
  $args = array(
    'post_type' => 'page',
    'posts_per_page' => -1
  );
  $loop = new WP_Query($args);
?>

<div id="digilan-token-activation-wifi4eu-settings">
  <h1><?php _e('Activation wifi4eu', 'digilan-token') ?></h1>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Activation code', 'digilan-token') ?>
          </th>
          <td>
            <fieldset>
              <label for="digilan-token-code">
                <input type="hidden" name="action" value="digilan-token-plugin" />
                <input type="hidden" name="view" value="access-point" />
                <input type="text" name="digilan-token-code" pattern="[A-Z0-9]{4}" maxlength=4 title="<?php _e('A 4-character code', 'digilan-token'); ?>" required />
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit-activation-wifi4eu" class="button button-primary" value="<?php _e('Activation request', 'digilan-token'); ?>">
    </p>
  </form>
</div>
<div id="digilan-token-ap-settings">
  <h1><?php _e('Access Point configuration', 'digilan-token'); ?></h1>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="digilan-token-settings">
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="digilan-token-global" value="true" />
    <h2><?php _e('General settings', 'digilan-token'); ?></h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('Portal login page', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <select name="digilan-token-page" id="digilan-token-select-page" class="regular-text" form="digilan-token-settings">
                <?php
                if ($loop->have_posts()) {
                  while ($loop->have_posts()) {
                    $loop->the_post();
                    global $post;
                    $is_selected = get_permalink($post->ID) == $settings->get('portal-page');
                    $selected = '';
                    if ($is_selected) {
                      $selected_id = $post->ID;
                      $selected = 'selected';
                    }
                ?>
                <option value="<?php echo get_permalink($post->ID); ?>" <?php echo $selected; ?>>
                  <?php echo $post->post_name; ?>
                  </option>
                  <?php
                  }
                  if ($selected = '') {
                  ?>
                    <option value="">Please select a page.</option>
                  <?php
                  }
                }
                wp_reset_query();
                ?>
              </select>
            </fieldset>
          </td>
        </tr>
        <tr>
          <th scope="row" style="vertical-align: middle;"><?php _e('User timeout', 'digilan-token'); ?></th>
          <td>
            <fieldset>
              <label for="settings">
              <input type="hidden" name="action" value="digilan-token-plugin" />
              <input type="hidden" name="view" value="access-point" />
              <input name="digilan-token-timeout" pattern="^\d+$" class="regular-text" type="text" value="<?php echo DigilanTokenAdmin::get_timeout($settings->get('timeout')); ?>" />
                minutes.
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php if (DigilanToken::isFromCitybox()) : ?>
      <h2><?php _e('Schedule configuration', 'digilan-token'); ?></h2>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Configure schedule', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="activate-schedule-router"> <input type="button" name="dlt-show-scheduler-router" id="dlt-show-scheduler-router" class="button button-primary" value="<?php _e('Show/Hide schedule', 'digilan-token'); ?>" />
                </label>
              </fieldset>
            </td>
          </tr>
        </tbody>
      </table>
      <div id="weekly-schedule-caption-router" style="display: none;">
        <table>
          <tbody>
            <tr>
              <td style="background-color: #4ef542; width: 65px;"></td>
              <td><?php _e('Hotspot enabled', 'digilan-token'); ?></td>
            </tr>
            <tr>
              <td style="background-color: #f5424b; width: 65px;"></td>
              <td><?php _e('Hotspot disabled', 'digilan-token'); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div id="weekly-schedule-router" style="display: none;"></div>
      <input type="hidden" name="digilan-token-schedule-router" id="digilan-token-schedule-router" value="" />
<?php endif; ?>

      <p class="submit">
        <input type="submit" name="submit" id="submit-settings" class="button button-primary" value="<?php _e('Save settings', 'digilan-token'); ?>">
      </p>
    </form>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="digilan-token-settings-ap">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <input type="hidden" name="digilan-token-access-point-settings" value="true" /> <input type="hidden" name="view" value="access-point" />
      <input type="hidden" name="action" value="digilan-token-plugin" />
      <h2><?php _e('Access point settings', 'digilan-token'); ?></h2>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Select all access points', 'digilan-token'); ?></th>
            <th>
              <fieldset>
                <label for="select-all"> <input type="checkbox" name="digilan-token-select-all" id="dlt-select-all" value="0" />
                </label>
              </fieldset>
            </th>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Access Point hostname', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <select name="digilan-token-hostname" id="digilan-token-select-hostname" class="regular-text" form="digilan-token-settings-ap">
                  <?php
                  $hostnames = array_keys($settings->get('access-points'));
                  foreach ($hostnames as $hostname) :
                  ?>
                    <option value="<?php echo $hostname; ?>"><?php echo $hostname; ?></option>
                  <?php endforeach; ?>
                </select>
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('SSID', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="ssid"> <input placeholder="Borne Autonome" id="digilan-token-ssid-input" name="digilan-token-ssid" class="regular-text" type="text" maxlength=32>
                </label>
                <input class="button button-primary" style="margin-top: 5px;" type="button" value="<?php _e('Display QRCode', 'digilan-token'); ?>" id="open_qrcode_modal" />
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Landing page', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="landing-page"> 
                  <input 
                    type="url" 
                    placeholder="<?php _e("Valid URL adress"); ?>" 
                    id="digilan-token-lpage-input" 
                    pattern="^http(s)?:\/\/[\w\-]+(\.[\w\-]+)+(:\d+)?[\/\w\-]+$" 
                    name="digilan-token-lpage" 
                    class="regular-text" 
                    value="<?php htmlspecialchars($settings->get('landing-page'), ENT_QUOTES, 'UTF-8'); ?>" />
                </label>
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Country code', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="ssid"> <input placeholder="FR" id="digilan-token-country-input" name="digilan-token-country-code" pattern="^[A-Z][A-Z]$" required class="regular-text" type="text" maxlength=2 />
                </label>
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;">
              <?php _e('Configure schedule', 'digilan-token'); ?></th>
            <td>
              <fieldset>
                <label for="activate-schedule"> <input type="button" name="dlt-show-scheduler" id="dlt-show-scheduler" class="button button-primary" value="<?php _e('Show/Hide schedule', 'digilan-token'); ?>" />
                </label>
              </fieldset>
            </td>
          </tr>
        </tbody>
      </table>
      <div id="weekly-schedule-caption" style="display: none;">
        <table>
          <tbody>
            <tr>
              <td style="background-color: #4ef542; width: 65px;"></td>
              <td><?php _e('Hotspot enabled', 'digilan-token'); ?></td>
            </tr>
            <tr>
              <td style="background-color: #f5424b; width: 65px;"></td>
              <td><?php _e('Hotspot disabled', 'digilan-token'); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div id="weekly-schedule" style="display: none;"></div>
      <input type="hidden" name="digilan-token-schedule" id="digilan-token-schedule" value="" />
      <p class="submit">
        <input type="submit" name="submit" id="submit-settings-ap" class="button button-primary" value="<?php _e('Save settings', 'digilan-token'); ?>">
      </p>
    </form>
  </div>
  <h1 style="padding-left: 15px;">
    <?php _e('Curent Portal', 'digilan-token'); ?>
    </h1>
    <?php if (empty($selected_id)) : ?>
    <p style="color: red; padding-left: 15px;">
      <?php _e('Select a portal page and validate your settings.', 'digilan-token'); ?>
      </p>
    <?php else : ?>
    <p style="padding-left: 15px;">
      <a class="button button-primary" href="<?php echo get_admin_url() . 'post.php?post=' . $selected_id . '&action=edit'; ?>">
        <?php _e('Edit portal page', 'digilan-token'); ?>
      </a>
    </p>
    <?php endif; ?>
  <?php
  $first_access_point = key($settings->get('access-points'));
  if ($first_access_point === NULL) {
    $mac = '';
  } else {
    $mac = $settings->get('access-points')[$first_access_point]['mac'];
  }
  ?>
  <iframe id="digilan-portal-preview" src="<?php echo $settings->get('portal-page'); ?>/?digilan-token-action=hide_bar&mac=<?php echo $mac; ?>" style="margin-left: 2em;margin-top: 2em; border: none; width: 50%; height: 600px; position: relative;"></iframe>
<?php else : ?>
  <div id="digilan-token-container-ap">
    <h1><?php _e('Upgrade plugin for Solo Access Points', 'digilan-token'); ?></h1>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;">
              <?php _e('Activate digilan-token plugin', 'digilan-token'); ?>
            </th>
            <td>
              <fieldset>
                <label for="activation">
                <input type="hidden" name="action" value="digilan-token-plugin" />
                <input type="hidden" name="view" value="access-point" />
                <input type="text" name="digilan-token-code" pattern="[A-Z0-9]{4}" maxlength=4 title="<?php _e('A 4-character code', 'digilan-token'); ?>" required />
                </label>
              </fieldset>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="submit">
        <input type="submit" name="submit" id="submit-activation-code" class="button button-primary" value="<?php _e('Submit code', 'digilan-token'); ?>">
      </p>
    </form>
  </div>
  <div id="digilan-token-send-url">
    <?php
    if ($settings->get('pre-activation')) {
      _e('Code has been sent.', 'digilan-token');
    } else {
      _e('Code has not been sent. Please click on "Query code"', 'digilan-token');
    }
    ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <input type="hidden" name="action" value="digilan-token-plugin" />
      <input type="hidden" name="view" value="access-point" />
      <input type="hidden" name="digilan-token-activator" value="true" />
      <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Activation request', 'digilan-token'); ?>">
      </p>
    </form>
  </div>
<?php endif; ?>
<div id="qrcode-bg-modal">
  <div>
    <p><?php _e('Print the qrcode to connect !', 'digilan-token') ?></p>
    <div id="qrcode"></div>
    <input class="button button-primary" type="button" value="<?php _e('Close', 'digilan-token'); ?>" id="close_qrcode_modal"/>
  </div>
</div>