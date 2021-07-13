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
    function __construct(string $ssid, string $mac, string $access,  string $country_code, string $schedule, string $portal ='', string $landing='', int $timeout=7200, string $error_page='',  ) 
    {
        $this->set_ssid($ssid);
        $this->set_mac($mac);
        $this->set_access($access);
        $this->set_country_code($country_code);
        $this->set_schedule($schedule);
        $this->set_landing($landing);
        $this->set_timeout($timeout);
        $this->set_error_page($error_page);
    }
    
    public function get_config() 
    {
        $config = array(
            'global_settings' => array(
                'portal' => $this->portal,
                'landing' => $this->landing,
                'timeout' => $this->timeout,
                'error_page' => $this->error_page
            ),
            'ap_settings' => array(
                'ssid' => $this->ssid,
                'country_code' => $this->country_code,
                'access' => $this->access,
                'schedule' => $this->schedule,
                'mac' => $this->mac
            )
        );
        return $config;
    }

    public function update_settings($new_settings) 
    {
        foreach ($new_settings as $key => $value) {
            set_settings_by_key($key,$value);
        }
    }
    
    public static function set_settings_by_key($key,$value)
    {
        switch ($key) {
            case 'portal':
                $this->set_portal($value);
                break;
            case 'landing':
                $this->set_landing($value);
                break;
            case 'timeout':
                $this->set_timeout($value);
                break;
            case 'error_page':
                $this->set_error_page($value);
                break;
            case 'schedule':
                $this->set_schedule($value);
                break;
            case 'ssid':
                $this->set_ssid($value);
                break;
            case 'country_code':
                $this->set_country_code($value);
                break;
            case 'access':
                $this->set_access($value);
                break;
            case 'mac':
                $this->set_mac($value);
                break;
        }
    }

    public static function sanitize_portal_settings($in,$unsafe_value)
    {
        $re = '';
        switch ($in) {
            case 'digilan-token-page':
                $page = basename($unsafe_value);
                $res = get_page_by_path($page);
                if ($res == null) {
                    return 0;
                }
                return 1;
            case 'digilan-token-lpage':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return 1;
                }
                return 0;
            case 'digilan-token-timeout':
                $re = '/^\d+$/';
                return do_preg_match($re, $unsafe_value);
            case 'digilan-token-error-page':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return 1;
                }
                return 0;
            case 'digilan-token-schedule':
                $decode_result = json_decode($unsafe_value);
                if ($decode_result === false || $decode_result === null) {
                    return 0;
                }
                return 1;
            case 'digilan-token-ssid':
                $re = '/^[0-9a-zA-Z][\w\W]{1,32}$/';
                return do_preg_match($re, $unsafe_value);
            case 'digilan-token-country-code':
                $re = '/^[A-Z]{2}$/';
                return do_preg_match($re, $unsafe_value);
            case 'digilan-token-access':
                $re = '/^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|11)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468][048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(02)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02)(-)(29)))(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$/';
                return do_preg_match($re, $unsafe_value);
            case 'digilan-token-mac':
                $re = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$';
                return do_preg_match($re, $unsafe_value);
            default:
                return 0;
                break;
        }
    }

    public function do_preg_match($re,$unsafe_value)
    {
        $preg_result = preg_match($re, $unsafe_value);
        if ($preg_result === false) {
            error_log('An error occured during pre_match of value= '.$unsafe_value);
            die();
        }
        return $preg_result;
    }

    public function set_portal($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-page',$value);
        if ($sanitize_result === 0 ) {
            error_log($value.' is not a correct portal format.');
            return false;
        }
        $this->portal = $value;
    }

    public function set_landing($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-lpage',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct landing format.');
            return false;
        }
        $this->landing = $value;
    }

    public function set_timeout($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-timeout',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct timeout format.');
            return false;
        }
        $this->timeout = $value;
    }

    public function set_error_page($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-error-page',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct error page format.');
            return false;
        }
        $this->error_page = $value;
    }

    public function set_schedule($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-schedule',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct schedule format.');
            return false;
        }
        $this->schedule = $value;
    }

    public function set_ssid($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-ssid',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct ssid format.');
            return false;
        }
        $this->ssid = $value;
    }

    public function set_country_code($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-country-code',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct country code format.');
            return false;
        }
        $this->country_code = $value;
    }

    public function set_access($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-access',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct access format.');
            return false;
        }
        $this->access = $value;
    }

    public function set_mac($value) 
    {
        $sanitize_result = self::sanitize_portal_settings('digilan-token-mac',$value);
        if ($sanitize_result === 0) {
            error_log($value.' is not a correct mac format.');
            return false;
        }
        $this->mac = $value;
    }
    
}