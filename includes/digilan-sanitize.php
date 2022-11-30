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
class DigilanTokenSanitize
{
    private static function sanitize_test_regex($unsafe_value, $regex)
    {
        if (preg_match($regex, $unsafe_value) == 1) {
            return $unsafe_value;
        }
        return false;
    }

    public static function sanitize_post($in)
    {
        if (isset($_POST[$in])) {
            $unsafe_value = $_POST[$in];
            $re = '';
            switch ($in) {
                case 'digilan-token-country-code':
                    $re = '/^[A-Z]{2}$/';
                    break;
                case 'digilan-token-hostname':
                    $re = '/^[\.\-\w]{1,63}$/';
                    break;
                case 'digilan-token-code':
                    $re = '/^[A-Z0-9]{4}$/';
                    break;
                case 'digilan-token-page':
                    $page = basename($unsafe_value);
                    $res = get_page_by_path($page);
                    if ($res == false) {
                        return false;
                    }
                    return $unsafe_value;
                case 'digilan-token-timeout':
                    $re = '/^\d+$/';
                    break;
                case 'digilan-token-ssid':
                    $re = '/^[0-9a-zA-Z][\w\W]{1,32}$/';
                    break;
                case 'digilan-token-lpage':
                    if (esc_url_raw($unsafe_value) == $unsafe_value) {
                        $res = esc_url_raw($unsafe_value);
                        return $res;
                    }
                    return false;
                case 'ordering':
                    if (!is_array($unsafe_value)) {
                        return false;
                    }
                    $providers = DigilanToken::$providers;
                    foreach ($providers as $provider) {
                        $provider_id = $provider->getId();
                        if (!in_array($provider_id, $unsafe_value)) {
                            return false;
                        }
                    }
                    return $unsafe_value;
                case 'digilan-token-schedule':
                    if (json_decode($unsafe_value) === false) {
                        return false;
                    }
                    if (json_decode($unsafe_value) === null) {
                        return false;
                    }
                    return $unsafe_value;
                case 'dlt-start-date':
                    $a = explode('-', $unsafe_value);
                    $month = $a[1];
                    $year = $a[0];
                    $day = $a[2];
                    $res = wp_checkdate($month, $day, $year, $unsafe_value);
                    if ($res) {
                        return $unsafe_value;
                    } else {
                        return false;
                    }
                case 'dlt-end-date':
                    $a = explode('-', $unsafe_value);
                    $month = $a[1];
                    $year = $a[0];
                    $day = $a[2];
                    $res = wp_checkdate($month, $day, $year, $unsafe_value);
                    if ($res) {
                        return $unsafe_value;
                    } else {
                        return false;
                    }
                case 'digilan-token-start':
                    $a = explode('-', $unsafe_value);
                    $month = $a[1];
                    $year = $a[0];
                    $day = $a[2];
                    $res = wp_checkdate($month, $day, $year, $unsafe_value);
                    if ($res) {
                        return $unsafe_value;
                    } else {
                        return false;
                    }
                case 'digilan-token-end':
                    $a = explode('-', $unsafe_value);
                    $month = $a[1];
                    $year = $a[0];
                    $day = $a[2];
                    $res = wp_checkdate($month, $day, $year, $unsafe_value);
                    if ($res) {
                        return $unsafe_value;
                    } else {
                        return false;
                    }
                case 'dlt-mail':
                    if (is_email($unsafe_value)) {
                        $res = sanitize_email($unsafe_value);
                        return $res;
                    }
                    return false;
                case 'view':
                    $re = '/^(access-point|connections|settings|form-settings|providers|logs|assistant|provider-\w+|test-connection|orderProviders)$/';
                    break;
                case 'subview':
                    $re = '/^(settings|buttons)$/';
                    break;
                case 'digilan-token-schedule-router':
                    if (json_decode($unsafe_value) === false) {
                        return false;
                    }
                    if (json_decode($unsafe_value) === null) {
                        return false;
                    }
                    return $unsafe_value;
                case 'cityscope_backend':
                    if (esc_url_raw($unsafe_value) == $unsafe_value) {
                        $res = esc_url_raw($unsafe_value);
                        return $res;
                    }
                    return false;
                default:
                    break;
            }
            return self::sanitize_test_regex($unsafe_value, $re);
        } else {
            return false;
        }
    }

    public static function sanitize_custom_lang()
    {
        $unsafe_lang = $_POST['custom_portal_lang'];
        $re = '/^(English|French|German|Portuguese|Spanish|Italian)$/';
        return self::sanitize_test_regex($unsafe_lang, $re);
    }

    public static function sanitize_request($in)
    {
        if (isset($_REQUEST[$in])) {
            $unsafe_value = $_REQUEST[$in];
            $re = '';
            switch ($in) {
                case 'view':
                    $re = '/^(access-point|connections|logs|providers|settings|form-settings|assistant|provider-\w+|test-connection|fix-redirect-uri)$/';
                    break;
                case 'subview':
                    $re = '/^(settings|buttons)$/';
                    break;
                case 'oauth_token':
                    $re = '/^[0-9a-zA-Z-_]{27}$/';
                    break;
                case 'oauth_verifier':
                    $re = '/^[0-9a-zA-Z]{32}$/';
                    break;
                case 'code':
                    $re = '/^[\w-\/%]+$/';
                    break;
                case 'loginSocial':
                    $re = '/^(google|twitter|facebook|transparent)$/';
                    break;
                case 'display':
                    $re = '/^popup$/';
                    break;
                default:
                    break;
            }
            return self::sanitize_test_regex($unsafe_value, $re);
        } else {
            return false;
        }
    }

    public static function sanitize_form_field_type($unsafe_value) {
        $re = '/^(text|email|tel|number|radio|select|checkbox)$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_form_field_display_name($unsafe_value) {
        $re = '/^[0-9a-zA-ZÀ-ú\s\-\']*$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_form_field_instruction($unsafe_value) {
        $re = '/^[a-zA-ZÀ-ú\s,\-\'.?!%$€#]*$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_form_field_unit($unsafe_value) {
        $re = '/^[a-zA-ZÀ-ú\s,\-\'.?!%$€#]*$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_form_field_to_delete($unsafe_value) {
        $re = '/^(delete)?$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_form_field_options($unsafe_value) {
        $re = '/^[0-9a-zA-ZÀ-ú\s\-\']+$/';
        $unsafe_options = explode(',', $unsafe_value);
        foreach($unsafe_options as $index=>$option) {
            // Remove if there is no text between two comma
            if (trim($option) === '') {
                unset($unsafe_options[$index]);
                continue;
            }
            $safe_option = self::sanitize_test_regex(trim($option), $re);
            if (false === $safe_option) {
                return false;
            }
        }
        $safe_value = implode(',', $unsafe_options);
        return $safe_value;
    }

    public static function sanitize_custom_form_portal_hidden_text($unsafe_value) {
        $re = '/^[0-9a-zA-ZÀ-ú\s,-.?!]*$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_custom_form_portal_hidden_number($unsafe_value) {
        $re = '/^[0-9]*(,[0-9]*)?$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_custom_form_portal_hidden_email($unsafe_value) {
        if (is_email($unsafe_value)) {
            $res = sanitize_email($unsafe_value);
            return $res;
        }
        return false;
    }

    public static function sanitize_custom_form_portal_hidden_tel($unsafe_value) {
        $re = '/^\+?(?:[0-9]\s?){6,14}[0-9]$/';
        return self::sanitize_test_regex($unsafe_value, $re);
    }

    public static function sanitize_custom_form_portal_hidden_options($unsafe_value, $options) {
        if (false == str_contains($options, $unsafe_value))
        {
            return false;
        }
        return $unsafe_value;
    }

    public static function sanitize_get($in)
    {
        if (isset($_GET[$in])) {
            $unsafe_value = $_GET[$in];
            $re = '';
            switch ($in) {
                case 'user_ip':
                    $re = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
                    break;
                case 'ap_mac':
                    $re = '/^[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}$/';
                    break;
                case 'mac':
                    $re = '/^[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}$/';
                    break;
                case 'session_id':
                    $re = '/^[0-9a-f]{32}$/';
                    break;
                case 'oauth_verifier':
                    $re = '/^[0-9a-zA-Z]{32}$/';
                    break;
                case 'provider':
                    $re = '/^(google|facebook|twitter)$/';
                    break;
                case 'secret':
                    $re = '/^[0-9a-f]{32}$/';
                    break;
                case 'digilan-token-action':
                    $re = '/^(create|validate|configure|write|add|del|reauth|archive|version|hide_bar|data)$/';
                    break;
                case 'hostname':
                    $re = '/^[\.\-\w]{1,63}$/';
                    break;
                case 'loginSocial':
                    $re = '/^(google|twitter|facebook|transparent)$/';
                    break;
                case 'digilan-token-secret':
                    $re = '/^[0-9A-Za-z]{32}$/';
                    break;
                case 'oauth_token':
                    $re = '/^[0-9a-zA-Z-_]{27}$/';
                    break;
                case 'redirect':
                    if ($unsafe_value === esc_url_raw($unsafe_value)) {
                        $res = esc_url_raw($unsafe_value);
                        return $res;
                    }
                    return false;
                case 'redirect_to':
                    if ($unsafe_value === esc_url_raw($unsafe_value)) {
                        $res = esc_url_raw($unsafe_value);
                        return $res;
                    }
                    return false;
                case 'view':
                    $re = '/^(access-point|connections|settings|form-settings|providers|logs|assistant|provider-\w+|test-connection)$/';
                    break;
                case 'state':
                    $re = '/^([0-9a-f]{32}|[0-9a-f]{32}[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2})$/';
                    break;
                case 'code':
                    $re = '/^[\w-\/%]+$/';
                    break;
                case 'display':
                    $re = '/^popup$/';
                    break;
                case 'page':
                    $re = '/^digilan-token-plugin$/';
                    break;
                case 'action':
                    $re = '/^(link|unlink)$/';
                    break;
                default:
                    return '';
            }
            return self::sanitize_test_regex($unsafe_value, $re);
        } else {
            return false;
        }
    }

    public static function int_to_mac($mac_int)
    {
        $mac = dechex($mac_int);
        if (strlen($mac) == 13) return false;
        while (strlen($mac) < 12) {
            $mac = '0' . $mac;
        }
        $mac = str_split($mac, 2);
        $mac = implode(':', $mac);
        return $mac;
    }
}
