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
        $res = '';
        foreach ($user_form_fields_in as $key => $value)
        {
            $res .= '<input type="hidden" name="dlt-hidden-' . $key .'" value="">';
        }
        return $res;
    }

    public static function create_lang_select_component()
    {
        $user_lang = DigilanToken::get_user_lang();
        $languages = get_option('form_languages');

        $languages_available = array_filter(
            $languages,
            fn ($lang) => $lang['implemented'] === true,
        );
        if (count($languages_available) === 1) {
            return '';
        }

        $lang_options = '';
        foreach ($languages_available as $lang) {
            if ($lang === $user_lang) {
                continue;
            }
            $src = 'images/flags/'. $lang["name"] .'.svg';
            $lang_options .= 
            '<li id="'. $lang['name'] .'">
                <img
                    class="language-flag"
                    src="'. plugins_url($src, DLT_ADMIN_PATH) .'"
                    alt="'. $lang["name"] .' flag"
                    title="'. $lang["name"] .'"
                    value="'. $lang['name'] .'"
                />
                <span>'. $lang['name'] .'</span>
            </li>';
        }

        $lang_select = '<ul id="language_list">'. $lang_options .'</ul>';
        $src = 'images/flags/'. $user_lang['name'] .'.svg';
        $lang_container =
        '<div class="lang-select">
            <input
                type="button"
                id="form_lang_selector"
                style="background: center / cover url('. plugins_url($src, DLT_ADMIN_PATH) .');"
            />
            <div class="language_list_container">'. $lang_select .'</div>
        </div>';
        return $lang_container;
    }

    public static function translate_field($x, $value_need_explode = false)
    {
        $user_lang = DigilanToken::get_user_lang();
        $lang_code = $user_lang['code'];

        $value;
        if ($x[$lang_code]) {
            $value = array($x[$lang_code], '');
        } elseif ($x['en_US']) {
            $value = array($x['en_US'], 'missing_translation');
        } elseif ($x['fr_FR']) {
            $value = array($x['fr_FR'], 'missing_translation');
        }

        if ($value_need_explode) {
            $value[0] = explode(',', $value[0]);
            $value[0] = array_map('trim', $value[0]);
        }
        return $value;
    }

    public static function create_form_component($user_form_fields_in)
    {
        foreach ($user_form_fields_in as $field_key => $field_data) {
            switch ($field_data['type']) {
                case 'text':
                case 'tel':
                case 'number':
                case 'email':
                    [$unit, $unit_class] = self::translate_field($field_data['unit']);
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$instruction, $instruction_class] = self::translate_field($field_data["instruction"]);
                    [$value, $value_class] = self::translate_field($fields_array[$field_key]);
                    $form_inputs .= 
                    '<label for="dlt-' . $field_key . '"><strong class="' .$display_name_class .'">' . $display_name . '</strong></label>
                    <div style="display: flex; align-items: center;">
                        <input 
                            class="regular-text '. $instruction_class .'" 
                            type="' . $field_data["type"]  . '" 
                            placeholder="' . $instruction  . '" 
                            name="dlt-' . $field_key .'" '.
                            $field_data['required'] .'
                        >'
                        . (isset($unit) ? '<span style="margin-left:10px;" class="' . $unit_class . '">' .$unit . '</span>' : "") .'
                    </div>';
                    break;
                case 'radio':
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$options, $options_class] = self::translate_field($field_data["options"], true);
                    $form_inputs .= 
                    '<label for="dlt-' . $field . '"><strong class="' .$display_name_class .'">' . $display_name . '</strong></label>
                    <div style="text-align: left">';
                    foreach($options as $radioButton) {
                        $form_inputs .='
                        <div>
                            <input type="radio" id="' . $radioButton .'" name="dlt-' . $field_key .'" value="' . $radioButton . '" ' . $field_data['required'] .'>
                            <label class="' .$display_name_class .'" for="' . $radioButton . '">' . $radioButton . '</label>
                        </div>';
                    }
                    $form_inputs .= '</div>';
                    break;
                case 'select':
                    [$display_name, $display_name_class] = self::translate_field($field_data["display-name"]);
                    [$instruction, $instruction_class] = self::translate_field($field_data["instruction"]);
                    [$options, $options_class] = self::translate_field($field_data["options"], true);

                    $form_inputs .= 
                    '<label for="dlt-' . $field_key . '"><strong class="' .$display_name_class .'">' . $display_name . '</strong></label>
                    <div style="display: flex; align-items: center;">
                        <select name="dlt-' . $field_key . '" id="' . $field_key . '" '. $field_data['required'] .' style="text-align-last:center;">
                            <option value="" class="' .$instruction_class .'" disabled selected>-- ' . $instruction . ' --</option>
                    ';
                    foreach($options as $option) {
                        $form_inputs .= '<option value="'. $option . '"><span class="' .$options_class .'">' . $option . '</span></option>';
                    }
                    $form_inputs .= '</select></div>';
                    break;
            }
        }

        $form_structure = '<form action="" method="post" id="dlt-user-form">' . $form_inputs . '</form>';
        return $form_structure;
    }
}
