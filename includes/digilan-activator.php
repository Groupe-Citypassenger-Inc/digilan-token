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
class DigilanTokenActivator
{

    public static function cityscope_bonjour()
    {
        $wp_site_url = urlencode_deep(get_site_url());
        $secret = get_option('digilan_token_secret');
        $mode = get_option('digilan_token_mode');
        if (DigilanToken::isFromCitybox()) {
            $mode = 2;
        }
        $args = array(
            "Secret" => $secret,
            "Mode" => $mode
        );
        $endpoint = get_option('cityscope_backend');
        if ( false === $endpoint ) {
          $endpoint =  'https://admin.citypassenger.com/2019/Portals';
          error_log('cityscope_bonjour: no cityscope_backend option !');
        }
        $endpoint .= '/Bonjour';
        $url = esc_url_raw(add_query_arg($args, $endpoint . "/" . $wp_site_url));
        $request = wp_remote_get($url);
        $code = wp_remote_retrieve_response_code($request);
        if (is_wp_error($request)) {
            error_log('cityscope_bonjour:'.$endpoint.' has failed: ' . $request->get_error_message());
            return;
        } else if ($code == 400) {
            error_log('cityscope_bonjour:'.$endpoint.' Secret not sent. Response: ' . $code);
            return false;
        } else if ($code == 201 ) {
            error_log('cityscope_bonjour:'.$endpoint.' has created. Response: ' . $code);
            return true;
        } else if ($code !== 200) {
            error_log('cityscope_bonjour:'.$endpoint.' has failed. Response: ' . $code);
            return false;
        }
        $data = array(
            "pre-activation" => 1
        );
        DigilanToken::$settings->update($data);
        return true;
    }

    public static function register_ap()
    {
        if (!DigilanTokenConnection::validate_wordpress_AP_secret()) {
            _default_wp_die_handler('Wrong secret.');
        }
        $settings = DigilanToken::$settings;
        $hostname = DigilanTokenSanitize::sanitize_get('hostname');
        $mac = DigilanTokenSanitize::sanitize_get('mac');
        if (false === $hostname) {
            _default_wp_die_handler('Wrong hostname format.');
        }
        if (false === $mac) {
            _default_wp_die_handler('Wrong mac format.');
        }
        if (!empty($settings->get('access-points')[$hostname])) {
            $inap = $settings->get('access-points');
            $data = array();
            $current_ap_setting = clone $inap[$hostname];
            $new_ap_setting = array(
                'mac' => $mac,
                'access' => current_time('mysql')
            );
            $current_ap_setting->update_settings($new_ap_setting);
            $inap[$hostname] = $current_ap_setting;
            $settings->update(array(
                'access-points' => $inap
            ));
            $data['message'] = 'exists';
            $data = wp_json_encode($data);
            wp_die($data, '', 200);
        } else {
            $inap = $settings->get('access-points');
            if (array_search($mac, array_column($inap, 'mac'))) {
                $data['message'] = 'already exists';
                $data = wp_json_encode($data);
                wp_die($data, '', 400);
            }
            $new_ap_settings = new DigilanPortalModel('Borne Autonome',current_time('mysql'),$mac, 'FR', '{"0":[],"1":[],"2":[],"3":[],"4":[],"5":[],"6":[]}');
            $inap[$hostname] = $new_ap_settings;
            $settings->update(array(
                'access-points' => $inap
            ));
            $data = array(
                'message' => 'created'
            );
            $data = wp_json_encode($data);
            wp_die($data, '', 200);
        }
    }

    public static function remove_ap()
    {
        if (!DigilanTokenConnection::validate_wordpress_AP_secret()) {
            _default_wp_die_handler('Wrong secret.');
        }
        $hostname = DigilanTokenSanitize::sanitize_get('hostname');
        if (false === $hostname) {
            _default_wp_die_handler('Wrong hostname format.');
        }
        $data = array(
            'deleted' => true
        );
        $access_points = DigilanToken::$settings->get('access-points');
        if (!array_key_exists($hostname, $access_points)) {
            $data['deleted'] = false;
            $data = wp_json_encode($data);
            wp_die($data, '', 200);
        }
        unset($access_points[$hostname]);
        DigilanToken::$settings->update(array(
            'access-points' => $access_points
        ));
        $data = wp_json_encode($data);
        wp_die($data, '', 200);
    }

    public static function get_ap_settings()
    {
        if (!DigilanTokenConnection::validate_wordpress_AP_secret()) {
            _default_wp_die_handler('Wrong secret.');
        }
        $hostname = DigilanTokenSanitize::sanitize_get('hostname');
        if (false === $hostname) {
            _default_wp_die_handler('Wrong hostname format.');
        }
        $settings = DigilanToken::$settings;
        if (empty($settings->get('access-points')[$hostname])) {
            _default_wp_die_handler('No such hostname.');
        }
        $ap_setting_model = $settings->get('access-points')[$hostname];
        $ap_setting_array = $ap_setting_model->get_config();
        $intervals = $ap_setting_array['ap-settings']['schedule'];
        $schedule = array();
        $schedule['on'] = '';
        $schedule['off'] = '';
        $data = array(
            'timeout' => $ap_setting_array['global-settings']['timeout'],
            'landing_page' => $ap_setting_array['global-settings']['landing'],
            'country_code' => $ap_setting_array['ap-settings']['country_code'],
            'ssid' => $ap_setting_array['ap-settings']['ssid'],
            'portal_page' => $ap_setting_array['global-settings']['portal'],
            'error_page' => $ap_setting_array['global-settings']['error_page'],
            'schedule' => $schedule
        );
        $data = wp_json_encode($data);
        wp_die($data, '', 200);
    }
}
