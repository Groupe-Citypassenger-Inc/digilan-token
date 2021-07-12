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
     * @param string $portal portal page
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
        $portal = self::sanitize_portal_settings('digilan-token-page',$portal);
        $landing = self::sanitize_portal_settings('digilan-token-lpage',$landing);
        $timeout = self::sanitize_portal_settings('digilan-token-timeout',$timeout);
        $error_page = self::sanitize_portal_settings('digilan-token-error-page',$error_page);
        $schedule = self::sanitize_portal_settings('digilan-token-schedule',$schedule);
        $ssid = self::sanitize_portal_settings('digilan-token-ssid',$ssid);
        $country_code = self::sanitize_portal_settings('digilan-token-country-code',$country_code);

        if ($portal !== false) {
            $this->portal = $portal;
        } else {
            error_log($portal.' is not a correct portal format.');
            die();
        }
        if ($landing !== false) {
            $this->landing = $landing;
        } else {
            error_log($landing.' is not a correct landing format.');
            die();
        }
        if ($timeout !== false) {
            $this->timeout = $timeout;
        } else {
            error_log($timeout.' is not a correct timeout format.');
            die();
        }
        if ($error_page !== false) {
            $this->error_page = $error_page;
        } else {
            error_log($error_page.' is not a correct error page format.');
            die();
        }
        if ($schedule !== false) {
            $this->schedule = $schedule;
        } else {
            error_log($schedule.' is not a correct schedule format.');
            die();
        }
        if ($ssid !== false) {
            $this->ssid = $ssid;
        } else {
            error_log($ssid.' is not a correct ssid format.');
            die();
        }
        if ($country_code !== false) {
            $this->country_code = $country_code;
        } else {
            error_log($country_code.' is not a correct sountry code format.');
            die();
        }
    }
    
    public function get_config() 
    {
        $config = array(
            'portal' => $this->portal,
            'landing' => $this->landing,
            'timeout' => $this->timeout,
            'error_page' => $this->error_page,
            'schedule' => $this->schedule,
            'ssid' => $this->ssid,
            'country_code' => $this->country_code
        );
        return $config;
    }

    public static function sanitize_portal_settings($in,$unsafe_value)
    {
        if (!empty($unsafe_value)) {
            $re = '';
            switch ($in) {
                case 'digilan-token-page':
                    $unsafe_value = filter_var($unsafe_value, FILTER_SANITIZE_URL);
                    if (filter_var($unsafe_value, FILTER_VALIDATE_URL)) {
                        return $unsafe_value;
                    }
                    return false;
                case 'digilan-token-lpage':
                    $unsafe_value = filter_var($unsafe_value, FILTER_SANITIZE_URL);
                    if (filter_var($unsafe_value, FILTER_VALIDATE_URL)) {
                        return $unsafe_value;
                    }
                    return false;
                case 'digilan-token-timeout':
                    $re = '/^\d+$/';
                    break;
                case 'digilan-token-error-page':
                    $unsafe_value = filter_var($unsafe_value, FILTER_SANITIZE_URL);
                    if (filter_var($unsafe_value, FILTER_VALIDATE_URL)) {
                        return $unsafe_value;
                    }
                    return false;
                case 'digilan-token-schedule':
                    if (json_decode($unsafe_value) === false) {
                        return false;
                    }
                    if (json_decode($unsafe_value) === null) {
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
                    break;
            }
            if (empty($re)) {
                return false;
            }
            if (preg_match($re, $unsafe_value) == 1) {
                return $unsafe_value;
            }
            return false;
        } else {
            return false;
        }
    }
    
}