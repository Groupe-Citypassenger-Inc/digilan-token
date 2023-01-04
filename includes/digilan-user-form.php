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
class DigilanTokenUserForm
{
  public static function add_hidden_inputs($user_form_fields_in)
  {
    ob_start(); 
    foreach ($user_form_fields_in as $key => $value): ?>
      <input
        type="hidden"
        name="custom-form-portal-hidden/<?= esc_attr($value['type']); ?>/<?= esc_attr($key); ?>"
        value=""
      />
    <?php endforeach;
    $component = ob_get_contents();
    ob_end_clean();
    return $component;
  }

  public static function create_lang_select_component($display_lang)
  {
    $form_languages = get_option('digilan_token_form_languages');
    if (false == $form_languages) {
      \DLT\Notices::addError(__('There is no languages available'));
      wp_redirect(self::getAdminUrl('form-settings'));
      exit();
    }

    $languages_available = array_filter(
      $form_languages,
      fn ($lang) => $lang['implemented'] === 1,
    );
    if (count($languages_available) === 1) {
      return '';
    }

    $current_src = 'images/flags/'. $display_lang['name'] .'.svg';
    ob_start();
    ?>
    <div class="lang-select">
      <input
        type="button"
        id="form-lang-selector"
        style="background: center / cover url(<?= esc_attr(plugins_url($current_src, DLT_ADMIN_PATH)); ?>);"
      />
      <div class="language-list-container">
        <ul id="language-list">
          <?php foreach ($languages_available as $lang):
            if ($lang === $display_lang) {
              continue;
            }
            $src = 'images/flags/'. $lang["name"] .'.svg';
          ?>
          <li id="<?= esc_attr($lang['name']); ?>">
            <button type="button">
              <img
                class="language-flag"
                src="<?= esc_url(plugins_url($src, DLT_ADMIN_PATH)); ?>"
                alt="<?= esc_attr($lang["name"]); ?> flag"
                title="<?= esc_attr($lang["name"]); ?>"
                value="<?= esc_attr($lang['name']); ?>"
              />
              <span><?= $lang['name'] ?></span>
            </button>
          </li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
    <?php
    $component = ob_get_contents();
    ob_end_clean();
    return $component;
  }

  public static function translate_field($field)
  {
    $user_lang = DigilanToken::get_display_lang_from_url_or_first();
    $lang_code = $user_lang['code'];

    if (array_key_exists($lang_code, $field)) {
      echo $field[$lang_code];
      return 1;
    }
    echo reset($field);
    return 0;
  }

  public static function print_number_min_max($field)
  {
    $min = $field['min'];
    $max = $field['max'];
    if ($min === PHP_INT_MIN && $max === PHP_INT_MAX) {
      return;
    }
    if ($min === PHP_INT_MIN) {
      echo " (max $max)";
    } else if ($max === PHP_INT_MAX) {
      echo " (min $min)";
    } else {
      echo " ($min - $max)";
    }
  }

  public static function get_select_options_and_translate($field)
  {
    $user_lang = DigilanToken::get_display_lang_from_url_or_first();
    $lang_code = $user_lang['code'];

    $select_options = reset($field);
    $no_translation = true;
    if (array_key_exists($lang_code, $field)) {
      $select_options = $field[$lang_code];
      $no_translation = false;
    }

    ob_start(); 
    foreach($select_options as $option): ?>
      <option value='<?= esc_attr($option); ?>'>
        <?= $option;
        if($no_translation): ?>
          (no translation)
        <?php endif; ?>
      </option>
    <?php endforeach;
    $select = ob_get_contents();
    ob_end_clean();
    echo $select;
  }

  public static function get_radio_options_and_translate($field, $field_key)
  {
    $user_lang = DigilanToken::get_display_lang_from_url_or_first();
    $lang_code = $user_lang['code'];

    $radio_options = reset($field);
    $no_translation = true;
    if (array_key_exists($lang_code, $field)) {
      $radio_options = $field[$lang_code];
      $no_translation = false;
    }

    ob_start(); 
    foreach($radio_options as $option): ?>
      <div>
        <input
          type="radio"
          id="<?= esc_attr($option); ?>"
          name="dlt-<?= esc_attr($field_key); ?>"
          value="<?= esc_attr($option); ?>"<?= $field_data['required'] ?>
        />
        <label style="margin-left: 5px;" for="<?= esc_attr($option); ?>">
          <?= $option;
          if($no_translation): ?>
            (no translation)
          <?php endif; ?>
        </label>
      </div>
    <?php endforeach;
    $radios = ob_get_contents();
    ob_end_clean();
    echo $radios;
  }

  public static function portal_create_text_input_component($field_data, $field_key)
  {
      ?>
      <label for="dlt-<?= esc_attr($field_key); ?>">
        <strong>
          <?php if(self::translate_field($field_data['display-name']) === 0): ?>
            (no translation)
          <?php endif; ?>
        </strong>
      </label>
      <div style="display: flex; align-items: center;">
        <input
          class="regular-text"
          pattern="(?!^[\s]+$).+"
          type="text"
          placeholder="<?php if(self::translate_field($field_data['instruction']) === 0): ?> (no translation)<?php endif; ?>"
          name="dlt-<?= esc_attr($field_key); ?>"
          title="<?php _e('Only space content is an error', 'digilan-token'); ?>"
          <?= $field_data['required'] ?>
        />
      </div>
      <?php
  }

  public static function portal_create_number_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="display: flex; align-items: center;">
      <input
        class="regular-text"
        type="number"
        min="<?= esc_attr($field_data['min']); ?>"
        max="<?= esc_attr($field_data['max']); ?>"
        placeholder="<?php if(self::translate_field($field_data['instruction']) === 0): ?> (no translation)<?php endif; esc_attr(self::print_number_min_max($field_data)); ?> "
        name="dlt-<?= esc_attr($field_key); ?>"
        title="<?php _e('Only accept valid number', 'digilan-token'); ?>"
        <?= $field_data['required'] ?>
      />
    </div>
    <?php
  }

  public static function portal_create_tel_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="display: flex; align-items: center;">
      <input
        class="regular-text"
        pattern="^\s*(?:\+?(\d{1,3}))?([-. (]*(\d{3})[-. )]*)?((\d{3})[-. ]*(\d{2,4})(?:[-.x ]*(\d+))?)\s*$"
        type="tel"
        placeholder="<?php if(self::translate_field($field_data['instruction']) === 0): ?> (no translation)<?php endif; ?>"
        name="dlt-<?= esc_attr($field_key); ?>"
        title="<?php _e('Only accept valid phone number', 'digilan-token'); ?>"
        <?= $field_data['required'] ?>
      />
    </div>
    <?php
  }

  public static function portal_create_email_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="display: flex; align-items: center;">
      <input
        class="regular-text"
        type="email"
        placeholder="<?php if(self::translate_field($field_data['instruction']) === 0): ?> (no translation)<?php endif; ?>"
        name="dlt-<?= esc_attr($field_key); ?>"
        title="<?php _e('Only accept valid email address', 'digilan-token'); ?>"
        <?= $field_data['required'] ?>
      />
    </div>
    <?php
  }

  public static function portal_create_radio_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="text-align: left">
      <?php self::get_radio_options_and_translate($field_data['options'], $field_key); ?>
    </div>
    <?php
  }

  public static function portal_create_select_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="display: flex; align-items: center;">
      <select
        name="dlt-<?= esc_attr($field_key); ?>"
        id="<?= esc_attr($field_key); ?>"
        <?= $field_data['required'] ?>
        style="text-align-last:center;"
      >
        <option value="" disabled selected>
          <?php if(self::translate_field($field_data['instruction']) === 0): ?>
            (no translation)
          <?php endif; ?>
        </option>
        <?php self::get_select_options_and_translate($field_data['options']); ?>
      </select>
    </div>
    <?php
  }

  public static function portal_create_nationality_input_component($field_data, $field_key)
  {
    $nationality_iso_code = get_option('digilan_token_nationality_iso_code');
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="display: flex; align-items: center;">
      <select
        name="dlt-<?= esc_attr($field_key); ?>"
        id="<?= esc_attr($field_key); ?>"
        <?= $field_data['required'] ?>
        style="text-align-last:center;"
      >
        <option value="" disabled selected>
          <?php if(self::translate_field($field_data['instruction']) === 0): ?>
              (no translation)
          <?php endif; ?>
        </option>
        <?php foreach($nationality_iso_code as $code => $country): ?>
          <option value="<?= esc_attr($code); ?>"><span><?= $country ?></span></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php
  }

  public static function portal_create_checkbox_input_component($field_data, $field_key)
  {
    ?>
    <label for="dlt-<?= esc_attr($field_key); ?>">
      <strong>
        <?php if(self::translate_field($field_data['display-name']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </strong>
    </label>
    <div style="text-align: left">
      <input type="checkbox" id="<?= esc_attr($field_key); ?>" name="dlt-<?= esc_attr($field_key); ?>">
      <label for="<?= esc_attr($field_key); ?>">
        <?php if(self::translate_field($field_data['instruction']) === 0): ?>
          (no translation)
        <?php endif; ?>
      </label>
    </div>
    <?php
  }

  public static function create_form_component($user_form_fields_in)
  {
    $admin_url = admin_url('admin-post.php');
    ob_start(); ?>
    <form action="<?= esc_url($admin_url); ?>" method="post" id="custom-form-portal">
    <?php foreach ($user_form_fields_in as $field_key => $field_data):
      // Nationality field has DigilanTokenCustomPortalConstants::$nationality_iso_code constant value for options
      if ($field_key === 'nationality'):
        self::portal_create_nationality_input_component($field_data, $field_key);
        continue;
      endif;
      switch ($field_data['type']):
        case 'text':
          self::portal_create_text_input_component($field_data, $field_key);
          break;
        case 'number':
          self::portal_create_number_input_component($field_data, $field_key);
          break;
        case 'tel':
          self::portal_create_tel_input_component($field_data, $field_key);
          break;
        case 'email':
          self::portal_create_email_input_component($field_data, $field_key);
          break;
        case 'radio':
          self::portal_create_radio_input_component($field_data, $field_key);
          break;
        case 'select':
          self::portal_create_select_input_component($field_data, $field_key);
          break;
        case 'checkbox':
          self::portal_create_checkbox_input_component($field_data, $field_key);
          break;
      endswitch;
    endforeach; ?>
    <!-- <input type="hidden" name="action" value="dlt_user_data">
    <input type="submit" style="display: block;" class="dlt-auth" rel="nofollow" data-plugin="dlt" data-action="connect" >
    <span id="user-form-button" class="dlt-button dlt-button-default dlt-button-user-form" style="background-color: #f32e81;">
        <span class="dlt-button-label-container">
            Add user to database
        </span>
    </span> -->
    </form>
    <?php 
    $component = ob_get_contents();
    ob_end_clean();
    return $component;
  }
}
