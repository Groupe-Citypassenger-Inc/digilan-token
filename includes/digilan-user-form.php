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
    public static function add_hidden_inputs($formFieldsIn)
    {
        $res = '';
        foreach ($formFieldsIn as $key => $value)
        {
            $res .= '<input type="hidden" name="dlt-hidden-' . $key .'" value="">';
        }
        return $res;
    }

    public static function create_form($formFieldsIn)
    {
        $admin_url = esc_url(admin_url('admin-post.php'));
        $form = '<form action="' . $admin_url . '" method="post" id="dlt-user-form">';
        $form_inputs = '';
        $action_input = '<input type="hidden" name="action" value="dlt_user_data">';

        $submit_button = '<input type="submit" style="display: none;" class="dlt-auth" rel="nofollow" data-plugin="dlt" data-action="connect" >';
        $button = 
        '<span id="user-form-button" class="dlt-button dlt-button-default dlt-button-user-form" style="background-color: #f32e81;">
            <span class="dlt-button-label-container">
                Add user to database
            </span>
        </span>';
        foreach ($formFieldsIn as $field=>$configuration) {
            switch ($configuration['type']) {
                case 'text':
                case 'tel':
                case 'number':
                case 'email':
                    $unit = $configuration['unit'];
                    $form_inputs .= 
                    '<label for="dlt-' . $field . '"><strong>' . $configuration["display-name"] . '</strong></label>
                    <div style="display: flex; align-items: center;">
                        <input 
                            class="regular-text" 
                            type="' . $configuration["type"]  . '" 
                            placeholder="' . $configuration["placeholder"]  . '" 
                            name="dlt-' . $field .'" 
                            value="' . (isset($_POST[$field]) ? $fields_array[$field] : null) . '" ' .
                            $configuration['required'] .'
                        >
                        <span style="margin-left:10px;">' .$unit . '</span>
                    </div>';
                    break;
                case 'radio':
                    $form_inputs .= 
                    '<label for="dlt-' . $field . '"><strong>' . $configuration["display-name"] . '</strong></label>
                    <div style="text-align: left">';
                    foreach($configuration['options'] as $radioButton) {
                        $form_inputs .='
                        <div>
                            <input type="radio" id="' . $radioButton .'" name="dlt-' . $field .'" value="' . $radioButton . '" ' . $configuration['required'] .'>
                            <label for="' . $radioButton . '">' . $radioButton . '</label>
                        </div>';
                    }
                    $form_inputs .= '</div>';
                    break;
                case 'select':
                    $form_inputs .= 
                    '<label for="dlt-' . $field . '"><strong>' . $configuration["display-name"] . '</strong></label>
                    <div style="display: flex; align-items: center;">
                        <select name="dlt-' . $field . '" id="' . $field . '" '. $configuration['required'] .' style="text-align-last:center;">
                            <option value="" disabled selected>-- ' . $configuration["placeholder"] . ' --</option>
                    ';
                    foreach($configuration['options'] as $option) {
                        $form_inputs .= '<option value="'. $option . '">' . $option . '</option>';
                    }
                    $form_inputs .= '</select></div>';
                    break;
            }
        }
        $form_structure = $form . $form_inputs . $mail_input . $action_input . '<label>' . $submit_button . $button . '</label></form>';
        return $form_structure;
    }
}
