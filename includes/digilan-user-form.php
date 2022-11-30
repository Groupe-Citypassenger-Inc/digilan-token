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

    public static function create_lang_select_component()
    {
        $user_lang = DigilanToken::get_user_lang();
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

        $current_src = 'images/flags/'. $user_lang['name'] .'.svg';
        ob_start();
        ?>
        <div class="lang-select">
            <input type="button" id="form-lang-selector" style="background: center / cover url(<?= plugins_url($current_src, DLT_ADMIN_PATH) ?>);" />
            <div class="language-list-container">
                <ul id="language-list">
                    <?php foreach ($languages_available as $lang):
                        if ($lang === $user_lang) {
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

    public static function translate_field($x, $value_need_explode = false)
    {
        $user_lang = DigilanToken::get_user_lang();
        $lang_code = $user_lang['code'];

        $value;
        if ($x[$lang_code]) {
            $value = array($x[$lang_code], '');
        } elseif ($x['en_US']) {
            $value = array($x['en_US'], 'missing-translation');
        } elseif ($x['fr_FR']) {
            $value = array($x['fr_FR'], 'missing-translation');
        }

        if ($value_need_explode) {
            $value[0] = explode(',', $value[0]);
            $value[0] = array_map('trim', $value[0]);
        }
        return $value;
    }

    public static function create_form_component($user_form_fields_in)
    {
        ob_start(); ?>
        <form action="" method="post" id="custom-form-portal">
        <?php foreach ($user_form_fields_in as $field_key => $field_data):
            switch ($field_data['type']):
                case 'text':
                case 'tel':
                case 'number':
                case 'email':
                    [$unit, $unit_class] = self::translate_field($field_data['unit']);
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$instruction, $instruction_class] = self::translate_field($field_data["instruction"]);
                    [$value, $value_class] = self::translate_field($fields_array[$field_key]);
                    ?>
                    <label for="dlt-<?= $field_key ?>"><strong class="<?= $display_name_class ?>"><?= $display_name ?></strong></label>
                    <div style="display: flex; align-items: center;">
                        <input class="regular-text <?= $instruction_class ?>" pattern="" type="<?= $field_data["type"] ?>" placeholder="<?= $instruction ?>" name="dlt-<?= $field_key ?>" <?= $field_data['required'] ?>>
                        <?php if(isset($unit)): ?>
                            <span style='margin-left:10px;' class="<?= $unit_class ?>"><?= $unit ?></span>
                        <?php endif; ?>
                    </div>
                    <?php break;
                case 'radio':
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$options, $options_class] = self::translate_field($field_data["options"], true);
                    ?>
                    <label for="dlt-<?= $field_key ?>"><strong class="<?= $display_name_class ?>"><?= $display_name ?></strong></label>
                    <div style="text-align: left">
                        <?php foreach($options as $radioButton): ?>
                            <div>
                                <input type="radio" id="<?= $radioButton ?>" name="dlt-<?= $field_key ?>" value="<?= $radioButton ?>"<?= $field_data['required'] ?>>
                                <label style="margin-left: 5px;" class="<?= $options_class ?>" for="<?= $radioButton ?>"><?= $radioButton ?></label>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php break;
                case 'select':
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$instruction, $instruction_class] = self::translate_field($field_data["instruction"]);
                    [$options, $options_class] = self::translate_field($field_data["options"], true);
                    ?>
                    <label for="dlt-<?= $field_key ?>"><strong class="<?= $display_name_class ?>"><?= $display_name ?></strong></label>
                    <div style="display: flex; align-items: center;">
                        <select name="dlt-<?= $field_key ?>" id="<?= $field_key ?>" <?= $field_data['required'] ?> style="text-align-last:center;">
                            <option value="" class="<?= $instruction_class ?>" disabled selected>-- <?= $instruction ?> --</option>
                            <?php foreach($options as $option): ?>
                                <option value="<?= $option ?>"><span class="<?= $options_class ?>"><?= $option ?></span></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                <?php break;
            endswitch;
        endforeach; ?>
        </form>
        <?php 
        $component = ob_get_contents();
        ob_end_clean();
        return $component;
    }
}
