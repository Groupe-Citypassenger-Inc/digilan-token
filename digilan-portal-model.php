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
    function __construct(string $portal ='', string $landing='', int $timeout=7200, string $error_page='', string $schedule='', string $ssid='', string $country_code='') 
    {
        $this->set_portal($portal);
        $this->set_landing($landing);
        $this->set_timeout($timeout);
        $this->set_error_page($error_page);
        $this->set_schedule($schedule);
        $this->set_ssid($ssid);
        $this->set_country_code($country_code);
    }
    
    public function get_config() 
    {
        $config = array(
            'global_settings' => array(
                'portal' => $this->portal,
                'landing' => $this->landing,
                'timeout' => $this->timeout,
                'error_page' => $this->error_page,
                'schedule' => $this->schedule
            ),
            'ap_settings' => array(
                'ssid' => $this->ssid,
                'country_code' => $this->country_code
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
                    return false;
                }
                return $unsafe_value;
            case 'digilan-token-lpage':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return $res;
                }
                return false;
            case 'digilan-token-timeout':
                $re = '/^\d+$/';
                break;
            case 'digilan-token-error-page':
                if ($unsafe_value === esc_url_raw($unsafe_value)) {
                    $res = esc_url_raw($unsafe_value);
                    return $res;
                }
                return false;
            case 'digilan-token-schedule':
                $decode_result = json_decode($unsafe_value);
                if ($decode_result === false || $decode_result === null) {
                    return false;
                }
                return $unsafe_value;
            case 'digilan-token-ssid':
                $re = '/^[0-9a-zA-Z][\w\W]{1,32}$/';
                break;
            case 'digilan-token-country-code':
                $re = '/^[A-Z]{2}$/';
                break;
            default:
                return false;
                break;
        }
        if (preg_match($re, $unsafe_value) == 1) {
            return $unsafe_value;
        }
        return false;
        
    }

    public function set_portal($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-page',$value);
        if ($value === false) {
            error_log($value.' is not a correct portal format.');
            die();
        }
        $this->portal = $value;
    }

    public function set_landing($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-lpage',$value);
        if ($value === false) {
            error_log($value.' is not a correct landing format.');
            die();
        }
        $this->landing = $value;
    }

    public function set_timeout($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-timeout',$value);
        if ($value === false) {
            error_log($value.' is not a correct timeout format.');
            die();
        }
        $this->timeout = $value;
    }

    public function set_error_page($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-error-page',$value);
        if ($value === false) {
            error_log($value.' is not a correct error page format.');
            die();
        }
        $this->error_page = $value;
    }

    public function set_schedule($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-schedule',$value);
        if ($value === false) {
            error_log($value.' is not a correct schedule format.');
            die();
        }
        $this->schedule = $value;
    }

    public function set_ssid($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-ssid',$value);
        if ($value === false) {
            error_log($value.' is not a correct ssid format.');
            die();
        }
        $this->ssid = $value;
    }

    public function set_country_code($value) 
    {
        $value = self::sanitize_portal_settings('digilan-token-country-code',$value);
        if ($value === false) {
            error_log($value.' is not a correct country code format.');
            die();
        }
        $this->country_code = $value;
    }
    
}