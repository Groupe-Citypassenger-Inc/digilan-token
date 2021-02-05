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
            $inap[$hostname] = array(
                'ssid' => $settings->get('access-points')[$hostname]['ssid'],
                'access' => current_time('mysql'),
                'mac' => $mac,
                'schedule' => $settings->get('access-points')[$hostname]['schedule'],
                'country_code' => $settings->get('access-points')[$hostname]['country_code']
            );
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
            $inap[$hostname] = array(
                'ssid' => 'Borne Autonome',
                'access' => current_time('mysql'),
                'mac' => $mac,
                'country_code' => 'FR',
                'schedule' => '{"0":[],"1":[],"2":[],"3":[],"4":[],"5":[],"6":[]}'
            );
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
        $intervals = $settings->get('access-points')[$hostname]['schedule'];
        $schedule = array();
        $schedule['on'] = '';
        $schedule['off'] = '';
        $data = array(
            'timeout' => $settings->get('timeout'),
            'landing_page' => $settings->get('landing-page'),
            'country_code' => $settings->get('access-points')[$hostname]['country_code'],
            'ssid' => $settings->get('access-points')[$hostname]['ssid'],
            'portal_page' => $settings->get('portal-page'),
            'error_page' => get_site_url() . '/digilan-token-error',
            'schedule' => $schedule
        );
        $data = wp_json_encode($data);
        wp_die($data, '', 200);
    }
}
