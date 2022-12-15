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
            <input type="hidden" name="custom-form-portal-hidden/<?= $value['type'] ?>/<?= $key ?>" value="">
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
            <input type="button" id="form-lang-selector" style="background: center / cover url(<?= plugins_url($current_src, DLT_ADMIN_PATH) ?>);" />
            <div class="language-list-container">
                <ul id="language-list">
                    <?php foreach ($languages_available as $lang):
                        if ($lang === $display_lang) {
                            continue;
                        }
                        $src = 'images/flags/'. $lang["name"] .'.svg';
                    ?>
                    <li id="<?= $lang['name'] ?>">
                        <button type="button">
                            <img class="language-flag" src="<?= plugins_url($src, DLT_ADMIN_PATH) ?>" alt="<?= $lang["name"] ?> flag" title="<?= $lang["name"] ?>" value="<?= $lang['name'] ?>" />
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
        $lang_code = $display_lang['code'];

        if (array_key_exists($lang_code, $field)) {
            echo $field[$lang_code];
            return 1;
        }
        echo reset($field);
        return 0;
    }

    public static function get_select_options_and_translate($field)
    {
        $user_lang = DigilanToken::get_user_lang();
        $lang_code = $user_lang['code'];

        $select_options = reset($field);
        $no_translation = true;
        if (array_key_exists($lang_code, $field)) {
            $select_options = $field[$lang_code];
            $no_translation = false;
        }

        ob_start(); 
        foreach($select_options as $option): ?>
            <option value='<?= $option ?>'>
                <?php echo $option;
                if($no_translation): ?>
                    (no translation)
                <?php endif; ?>
            </option>
        <?php endforeach;
        $select = ob_get_contents();
        ob_end_clean();
        echo $select;
    }

    public static function get_radio_options_and_translate($field)
    {
        $user_lang = DigilanToken::get_user_lang();
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
                <input type="radio" id="<?= $option ?>" name="dlt-<?= $field_key ?>" value="<?= $option ?>"<?= $field_data['required'] ?>>
                <label style="margin-left: 5px;" class="<?= $options_class ?>" for="<?= $option ?>">
                    <?php echo $option;
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

    public static function portal_create_text_tel_number_email_input_component($field_data, $field_key)
    {
        ?>
        <label for="dlt-<?= $field_key ?>">
            <strong class="<?= $display_name_class ?>">
                <?php if(self::translate_field($field_data['display-name']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </strong>
        </label>
        <div style="display: flex; align-items: center;">
            <input
                class="regular-text <?= $instruction_class ?>"
                pattern=""
                type="<?= $field_data['type'] ?>"
                placeholder="<?php if(self::translate_field($field_data['instruction']) === 0): ?> (no translation)<?php endif; ?>"
                name="dlt-<?= $field_key ?>"
                <?= $field_data['required'] ?>
            />
            <?php if($field_data['unit']): ?>
                <span style='margin-left:10px;' class="<?= $unit_class ?>">
                    <?php if(self::translate_field($field_data['unit']) === 0): ?>
                        (no translation)
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function portal_create_radio_input_component($field_data, $field_key)
    {
        ?>
        <label for="dlt-<?= $field_key ?>">
            <strong class="<?= $display_name_class ?>">
                <?php if(self::translate_field($field_data['display-name']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </strong>
        </label>
        <div style="text-align: left">
            <?php self::get_radio_options_and_translate($field_data['options']); ?>
        </div>
        <?php
    }

    public static function portal_create_select_input_component($field_data, $field_key)
    {
        ?>
        <label for="dlt-<?= $field_key ?>">
            <strong class="<?= $display_name_class ?>">
                <?php if(self::translate_field($field_data['display-name']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </strong>
        </label>
        <div style="display: flex; align-items: center;">
            <select name="dlt-<?= $field_key ?>" id="<?= $field_key ?>" <?= $field_data['required'] ?> style="text-align-last:center;">
                <option value="" class="<?= $instruction_class ?>" disabled selected>
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
        <label for="dlt-<?= $field_key ?>">
            <strong class="<?= $display_name_class ?>">
                <?php if(self::translate_field($field_data['display-name']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </strong>
        </label>
        <div style="display: flex; align-items: center;">
            <select name="dlt-<?= $field_key ?>" id="<?= $field_key ?>" <?= $field_data['required'] ?> style="text-align-last:center;">
                <option value="" class="<?= $instruction_class ?>" disabled selected>
                    <?php if(self::translate_field($field_data['instruction']) === 0): ?>
                        (no translation)
                    <?php endif; ?>
                </option>
                <?php foreach($nationality_iso_code as $code => $country): ?>
                    <option value="<?= $code ?>"><span class="<?= $options_class ?>"><?= $country ?></span></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    public static function portal_create_checkbox_input_component($field_data, $field_key)
    {
        ?>
        <label for="dlt-<?= $field_key ?>">
            <strong class="<?= $display_name_class ?>">
                <?php if(self::translate_field($field_data['display-name']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </strong>
        </label>
        <div style="text-align: left">
            <input type="checkbox" id="<?= $field_key ?>" name="dlt-<?= $field_key ?>">
            <label class="<?= $instruction_class ?>" for="scales">
                <?php if(self::translate_field($field_data['instruction']) === 0): ?>
                    (no translation)
                <?php endif; ?>
            </label>
        </div>
        <?php
    }

    public static function create_form_component($user_form_fields_in)
    {
        $admin_url = esc_url(admin_url('admin-post.php'));
        ob_start(); ?>
        <form action="<?= $admin_url ?>" method="post" id="custom-form-portal">
        <?php foreach ($user_form_fields_in as $field_key => $field_data):
            // Nationality field has DigilanTokenCustomPortalConstants::$nationality_iso_code constant value for options
            if ($field_key === 'nationality'):
                self::portal_create_nationality_input_component($field_data, $field_key);
                continue;
            endif;
            switch ($field_data['type']):
                case 'text':
                case 'tel':
                case 'number':
                case 'email':
                    self::portal_create_text_tel_number_email_input_component($field_data, $field_key);
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
        </form>
        <?php 
        $component = ob_get_contents();
        ob_end_clean();
        return $component;
    }
}
