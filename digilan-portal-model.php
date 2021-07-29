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

class DigilanPortalModel {

    /**
     * @var String
     */
    private $portal = '';
    /**
     * @var String
     */
    private $landing = '';
    /**
     * @var Int
     */
    private $timeout = 7200;
    /**
     * @var String
     */
    private $error_page = '';
    /**
     * @var String
     */
    private $schedule = ''; 
    /**
     * @var String
     */
    private $ssid = '';
    /**
     * @var String
     */
    private $country_code = '';
    /**
     * @var String
     */
    private $access = '';
    /**
     * @var String
     */
    private $mac = '';
    /**
     * @var String
     */
    private $schedule_router = '';

    /**
     * DigilanPortalModel constructor.
     *
     * @param string $portal portal page name
     * @param string $landing landing page
     * @param int $timeout timeout allowed connection
     * @param string $error_page error page
     * @param string $schedule schedule of connection availability
     * @param string $ssid ssid
     * @param string $country_code country code
     * 
     */
    function __construct(string $ssid, string $access,  string $country_code, string $schedule, string $mac='', string $portal ='captive-portal', string $landing='', int $timeout=7200,string $error_page='',  string $schedule_router='{"0":[],"1":[],"2":[],"3":[],"4":[],"5":[],"6":[]}' ) 
    { 
        if (empty($error_page)) {
            $error_page = get_site_url() . "/digilan-token-error";
        }
        $this->set_portal($portal);
        $this->set_landing($landing);
        $this->set_timeout($timeout);
        $this->set_error_page($error_page);
        $this->set_schedule($schedule);
        $this->set_ssid($ssid);
        $this->set_country_code($country_code);
        $this->set_mac($mac);
        $this->set_access($access);
        $this->set_schedule_router($schedule_router);
    }
    
    public function get_ap_params($default_settings) 
    {
        array_walk($default_settings,array($this,'replace_by_specific_setting'));
        return $default_settings;
    }

    public function replace_by_specific_setting(&$default_value, $param_key) 
    {
        $specific_setting_value = $this->get_setting($param_key);
        if ($specific_setting_value != false) {
            $default_value = $specific_setting_value;
        }
    }

    public function get_config() 
    {
        $config = array(
            'portal-page' => $this->portal,
            'landing-page' => $this->landing,
            'timeout' => $this->timeout,
            'error_page' => $this->error_page,
            'schedule_router' => $this->schedule_router,
            'ssid' => $this->ssid,
            'country_code' => $this->country_code,
            'access' => $this->access,
            'schedule' => $this->schedule,
            'mac' => $this->mac
        );
        return $config;
    }

    public function update_settings($new_settings) 
    {
        foreach ($new_settings as $key => $value) {
            $this->set_settings_by_key($key,$value);
        }
    }
    
    public function get_setting($param_key)
    {
        switch ($param_key) {
            case 'portal_page':
                $value = $this->portal;
                if (false == $this->is_valid_sanitize('page', $value)) {
                    return false;
                }
                return $value;
            case 'landing_page':
                $value = $this->landing;
                if (false == $this->is_valid_sanitize('lpage', $value)) {
                    return false;
                }
                return $value;
            case 'timeout':
                $value = $this->timeout;
                if (false == $this->is_valid_sanitize('timeout', $value)) {
                    return false;
                }
                return $value;
            case 'error_page':
                $value = $this->error_page;
                if (false == $this->is_valid_sanitize('error-page', $value)) {
                    return false;
                }
                return $value;
            case 'schedule':
                $value = $this->schedule;
                if (false == $this->is_valid_sanitize('schedule', $value)) {
                    return false;
                }
                return $value;
            case 'ssid':
                $value = $this->ssid;
                if (false == $this->is_valid_sanitize('ssid', $value)) {
                    return false;
                }
                return $value;
            case 'country_code':
                $value = $this->country_code;
                if (false == $this->is_valid_sanitize('country-code', $value)) {
                    return false;
                }
                return $value;
            case 'access':
                $value = $this->access;
                if (false == $this->is_valid_sanitize('access', $value)) {
                    return false;
                }
                return $value;
            case 'mac':
                $value = $this->mac;
                if (false == $this->is_valid_sanitize('mac', $value)) {
                    return false;
                }
                return $value;
            case 'schedule_router':
                $value = $this->schedule_router;
                if (false == $this->is_valid_sanitize('schedule-router', $value)) {
                    return false;
                }
                return $value;
            default:
                return false;
        }
    }

    public function is_valid_sanitize($param_key,$unsafe_value)
    {
        $result_validity_value = self::sanitize_specific_settings('digilan-token-'.$param_key,$unsafe_value);
        if ($result_validity_value === false ) {
            error_log($unsafe_value.' is not a correct '.$param_key.' format.');
        }
        return $result_validity_value;
    }

    public function set_settings_by_key($param_key,$setting_value)
    {
        switch ($param_key) {
            case 'portal_page':
            case 'portal-page':
                $this->set_portal($setting_value);
                break;
            case 'landing_page':
            case 'landing-page':
                $this->set_landing($setting_value);
                break;
            case 'timeout':
                $this->set_timeout($setting_value);
                break;
            case 'error_page':
                $this->set_error_page($setting_value);
                break;
            case 'schedule':
                $this->set_schedule($setting_value);
                break;
            case 'ssid':
                $this->set_ssid($setting_value);
                break;
            case 'country_code':
                $this->set_country_code($setting_value);
                break;
            case 'access':
                $this->set_access($setting_value);
                break;
            case 'mac':
                $this->set_mac($setting_value);
                break;
            case 'schedule_router':
                $this->set_schedule_router($setting_value);
                break;
        }
    }

    public static function sanitize_specific_settings($sanitize_key,$unsafe_value)
    {
        switch ($sanitize_key) {
            case 'digilan-token-page':
                $page = basename($unsafe_value);
                $res = get_page_by_path($page);
                if ($res == null) {
                    return false;
                }
                return true;
            case 'digilan-token-lpage':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return true;
                }
                return false;
            case 'digilan-token-timeout':
                $re = '/^\d+$/';
                return self::do_preg_match($re, $unsafe_value);
            case 'digilan-token-error-page':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return true;
                }
                return false;
            case 'digilan-token-schedule':
                $decode_result = json_decode($unsafe_value);
                if ($decode_result === false || $decode_result === null) {
                    return false;
                }
                return true;
            case 'digilan-token-ssid':
                $re = '/^[0-9a-zA-Z][\w\W]{1,32}$/';
                return self::do_preg_match($re, $unsafe_value);
            case 'digilan-token-country-code':
                $re = '/^[A-Z]{2}$/';
                return self::do_preg_match($re, $unsafe_value);
            case 'digilan-token-access':
                $re = '/^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|11)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468][048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(02)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$/';
                return self::do_preg_match($re, $unsafe_value);
            case 'digilan-token-mac':
                if (is_int($unsafe_value)) {
                    $unsafe_value = DigilanTokenSanitize::int_to_mac($unsafe_value);
                }
                $re = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
                return self::do_preg_match($re, $unsafe_value);
            case 'digilan-token-schedule-router':
                $decode_result = json_decode($unsafe_value);
                if ($decode_result === false || $decode_result === null) {
                    return false;
                }
                return true;
            default:
                return false;
                break;
        }
    }

    public static function do_preg_match($re,$unsafe_value)
    {
        $preg_result = preg_match($re, $unsafe_value);
        if ($preg_result === false) {
            error_log('An error occured during pre_match of value = '.$unsafe_value);
            die();
        }
        return (bool) $preg_result;
    }

    public function set_portal($value) 
    {
        if (false == $this->is_valid_sanitize('page', $value)) {
            return false;
        }
        $this->portal = $value;
    }

    public function set_landing($value) 
    {
        if (false == $this->is_valid_sanitize('lpage', $value)) {
            return false;
        }
        $this->landing = $value;
    }

    public function set_timeout($value) 
    {
        if (false == $this->is_valid_sanitize('timeout', $value)) {
            return false;
        }
        $this->timeout = $value;
    }

    public function set_error_page($value) 
    {
        if (false == $this->is_valid_sanitize('error-page', $value)) {
            return false;
        }
        $this->error_page = $value;
    }

    public function set_schedule($value) 
    {
        if (false == $this->is_valid_sanitize('schedule', $value)) {
            return false;
        }
        $this->schedule = $value;
    }

    public function set_ssid($value) 
    {
        if (false == $this->is_valid_sanitize('ssid', $value)) {
            return false;
        }
        $this->ssid = $value;
    }

    public function set_country_code($value) 
    {
        if (false == $this->is_valid_sanitize('country-code', $value)) {
            return false;
        }
        $this->country_code = $value;
    }

    public function set_access($value) 
    {
        if (false == $this->is_valid_sanitize('access', $value)) {
            return false;
        }
        $this->access = $value;
    }

    public function set_mac($value) 
    {
        if (false == $this->is_valid_sanitize('mac', $value)) {
            return false;
        }
        $value = str_replace(array(
            '-',
            ':'
        ), '', $value);
        $value = hexdec($value);
        $this->mac = $value;
    }

    public function set_schedule_router($value) 
    {
        if (false == $this->is_valid_sanitize('schedule-router', $value)) {
            return false;
        }
        $this->schedule_router = $value;
    }
    
}