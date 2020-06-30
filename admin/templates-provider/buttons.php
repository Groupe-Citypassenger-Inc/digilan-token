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
wp_register_script('dlt-button-menu', plugins_url('/js/admin/template-provider/buttons.js', DLT_PLUGIN_BASENAME));
wp_enqueue_script('dlt-button-menu');
$data = array(
  'login_label' => $settings->get('login_label', 'default'),
  'link_label' => $settings->get('link_label', 'default'),
  'unlink_label' => $settings->get('unlink_label', 'default'),
  'default_button' => $provider->getRawDefaultButton(),
  'icon_button' => $provider->getRawIconButton()
);
wp_localize_script('dlt-button-menu', 'button_values', $data);
?>
<div class="dlt-admin-sub-content">

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">

    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="provider-<?php echo $provider->getId(); ?>" /> <input type="hidden" name="subview" value="buttons" />

    <table class="form-table">
      <tbody>
        <?php
        $buttonsPath = $provider->getPath() . '/admin/buttons.php';
        if (file_exists($buttonsPath)) {
          include($buttonsPath);
        }
        ?>

        <tr>
          <th scope="row"><label for="login_label"><?php _e('Login label', 'digilan-token'); ?></label></th>
          <td><input name="login_label" type="text" id="login_label" value="<?php echo esc_attr($settings->get('login_label')); ?>" class="regular-text">
            <p class="description">
              <a href="#" onclick="return resetButtonToDefault('#login_label');"><?php _e('Reset to default', 'digilan-token'); ?></a>
            </p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="custom_default_button"><?php _e('Default button', 'digilan-token'); ?></label>
          </th>
          <td>
            <?php
            $useCustom = false;
            $buttonTemplate = $settings->get('custom_default_button');
            if (!empty($buttonTemplate)) {
              $useCustom = true;
            } else {
              $buttonTemplate = $provider->getRawDefaultButton();
            }
            ?>
            <fieldset>
              <label for="custom_default_button_enabled"> <input name="custom_default_button_enabled" type="checkbox" id="custom_default_button_enabled" value="1" <?php if ($useCustom) : ?> checked <?php endif; ?>>
                <?php _e('Use custom button', 'digilan-token'); ?></label>
            </fieldset>
            <div id="custom_default_button_textarea_container" <?php if (!$useCustom) : ?> style="display: none;" <?php endif; ?>>
              <textarea cols="160" rows="6" name="custom_default_button" id="custom_default_button" class="digilan-token-html-editor" aria-describedby="editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4"><?php echo esc_textarea($buttonTemplate); ?></textarea>
              <p class="description">
                <a href="#" onclick="return resetButtonToDefault('#custom_default_button');"><?php _e('Reset to default', 'digilan-token'); ?></a><br>
                <br><?php printf(__('Use the %s in your custom button\'s code to make the label show up.', 'digilan-token'), "<code>{{label}}</code>"); ?>
              </p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <input name="link_label" type="hidden" id="link_label" value="<?php echo esc_attr($settings->get('link_label')); ?>" class="regular-text">
    <input name="unlink_label" type="hidden" id="unlink_label" value="<?php echo esc_attr($settings->get('unlink_label')); ?>" class="regular-text">
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>">
    </p>
  </form>
</div>