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
class DigilanTokenConnection
{
    public static function download_mails_csv($date_start, $date_end)
    {
        $filename = 'export_logs_' . $date_start . '_' . $date_end . '.csv';
        $filename = sanitize_file_name($filename);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
        header('Content-Transfer-Encoding: binary');
        $outstream = fopen('php://output', 'w');
        $header = array(
            'hostname',
            'connection date',
            'authentication date',
            'authentication mode',
            'social_id',
            'mac'
        );

        // Using mysqli here because there are no wpdb query function with unbuffering option.
        $host = DB_HOST;
        $username = DB_USER;
        $password = DB_PASSWORD;
        $db_name = DB_NAME;
        $mysqli = new mysqli($host, $username, $password, $db_name);
        $version = get_option('digilan_token_version');
        fputcsv($outstream, $header);
        global $wpdb;
        $aps = DigilanToken::$settings->get('access-points');
        /*
        *  FETCH RECORDS FROM ARCHIVE TABLE
        */
        $query_archive = "SELECT
               {$wpdb->prefix}digilan_token_connections_$version.ap_mac,
               {$wpdb->prefix}digilan_token_connections_$version.creation,
               {$wpdb->prefix}digilan_token_connections_$version.ap_validation,
               {$wpdb->prefix}digilan_token_connections_$version.authentication_mode,
               {$wpdb->prefix}digilan_token_users_$version.social_id,
               {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_connections_$version
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version ON {$wpdb->prefix}digilan_token_connections_$version.user_id = {$wpdb->prefix}digilan_token_users_$version.id
        WHERE {$wpdb->prefix}digilan_token_connections_$version.ap_validation <= '$date_end 23:59:59'
        AND {$wpdb->prefix}digilan_token_connections_$version.ap_validation >= '$date_start 00:00:00';
        ";
        $result = $mysqli->query($query_archive, MYSQLI_USE_RESULT);
        if ($result) {
            $c = 0;
            while ($row = $result->fetch_assoc()) {
                $c++;
                $row['ap_mac'] = DigilanTokenSanitize::int_to_mac($row['ap_mac']);
                $row['mac'] = DigilanTokenSanitize::int_to_mac($row['mac']);
                foreach ($aps as $hostname => $ap) {
                    if (in_array($row['ap_mac'], $ap)) {
                        $row['ap_mac'] = $hostname;
                        break;
                    }
                }
                $line = array(
                    $row['ap_mac'],
                    $row['creation'],
                    $row['ap_validation'],
                    $row['authentication_mode'],
                    $row['social_id'],
                    $row['mac']
                );
                fputcsv($outstream, $line);
                if ($c == 1000) {
                    $c = 0;
                    flush();
                }
            }
            $result->close();
        }

        /*
        *  FETCH RECORDS FROM LIVE TABLE
        */
        $query_active = "SELECT
               {$wpdb->prefix}digilan_token_active_sessions_$version.ap_mac,
               {$wpdb->prefix}digilan_token_active_sessions_$version.creation,
               {$wpdb->prefix}digilan_token_active_sessions_$version.ap_validation,
               {$wpdb->prefix}digilan_token_active_sessions_$version.authentication_mode,
               {$wpdb->prefix}digilan_token_users_$version.social_id,
               {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_active_sessions_$version
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version ON {$wpdb->prefix}digilan_token_active_sessions_$version.user_id = {$wpdb->prefix}digilan_token_users_$version.id
        WHERE {$wpdb->prefix}digilan_token_active_sessions_$version.ap_validation <= '$date_end 23:59:59'
        AND {$wpdb->prefix}digilan_token_active_sessions_$version.ap_validation >= '$date_start 00:00:00';
        ";
        $result = $mysqli->query($query_active, MYSQLI_USE_RESULT);
        if ($result) {
            $c = 0;
            while ($row = $result->fetch_assoc()) {
                $c++;
                $row['ap_mac'] = DigilanTokenSanitize::int_to_mac($row['ap_mac']);
                $row['mac'] = DigilanTokenSanitize::int_to_mac($row['mac']);
                foreach ($aps as $hostname => $ap) {
                    if (in_array($row['ap_mac'], $ap)) {
                        $row['ap_mac'] = $hostname;
                        break;
                    }
                }
                $line = array(
                    $row['ap_mac'],
                    $row['creation'],
                    $row['ap_validation'],
                    $row['authentication_mode'],
                    $row['social_id'],
                    $row['mac']
                );
                fputcsv($outstream, $line);
                if ($c == 1000) {
                    $c = 0;
                    flush();
                }
            }
            $result->close();
        }
        $s = ob_get_clean();
        exit($s);
    }

    private static function get_connections()
    {
        global $wpdb;
        $version = get_option('digilan_token_version');
        $query_archive = "SELECT
                {$wpdb->prefix}digilan_token_connections_$version.id,
                {$wpdb->prefix}digilan_token_connections_$version.ap_mac,
                {$wpdb->prefix}digilan_token_connections_$version.ap_validation,
                {$wpdb->prefix}digilan_token_connections_$version.creation,
                {$wpdb->prefix}digilan_token_connections_$version.authentication_mode,
                {$wpdb->prefix}digilan_token_connections_$version.user_id,
                {$wpdb->prefix}digilan_token_users_$version.social_id,
                {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_connections_$version
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version on {$wpdb->prefix}digilan_token_connections_$version.user_id = {$wpdb->prefix}digilan_token_users_$version.id
        WHERE {$wpdb->prefix}digilan_token_connections_$version.ap_validation IS NOT NULL;";
        $query_active = "SELECT
                {$wpdb->prefix}digilan_token_active_sessions_$version.id,
                {$wpdb->prefix}digilan_token_active_sessions_$version.ap_mac,
                {$wpdb->prefix}digilan_token_active_sessions_$version.ap_validation,
                {$wpdb->prefix}digilan_token_active_sessions_$version.creation,
                {$wpdb->prefix}digilan_token_active_sessions_$version.authentication_mode,
                {$wpdb->prefix}digilan_token_active_sessions_$version.user_id,
                {$wpdb->prefix}digilan_token_users_$version.social_id,
                {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_active_sessions_$version
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version on {$wpdb->prefix}digilan_token_active_sessions_$version.user_id = {$wpdb->prefix}digilan_token_users_$version.id
        WHERE {$wpdb->prefix}digilan_token_active_sessions_$version.ap_validation IS NOT NULL;";
        $current = $wpdb->get_results($query_active);
        $archives = $wpdb->get_results($query_archive);
        $connections = array_merge($current, $archives);
        $aps = DigilanToken::$settings->get('access-points');
        for ($i = 0; $i < count($connections); ++$i) {
            $connections[$i]->ap_mac = DigilanTokenSanitize::int_to_mac($connections[$i]->ap_mac);
            $connections[$i]->mac = DigilanTokenSanitize::int_to_mac($connections[$i]->mac);
            foreach ($aps as $hostname => $ap) {
                if (in_array($connections[$i]->ap_mac, $ap)) {
                    $connections[$i]->ap_mac = $hostname;
                    break;
                }
            }
        }
        return $connections;
    }

    public static function output_connections()
    {
        $connections = self::get_connections();
        $data = wp_json_encode($connections);
        return $data;
    }

    public static function get_connection_repartition()
    {
        global $wpdb;
        $version = get_option("digilan_token_version");
        $providers = DigilanToken::$providers;
        $providers = array(
            'facebook',
            'twitter',
            'google',
            'transparent',
            'mail'
        );
        $data = array();
        foreach ($providers as $provider) {
            $query_archive = "SELECT COUNT(*) FROM {$wpdb->prefix}digilan_token_connections_$version" . " WHERE authentication_mode='%s'";
            $query_archive = $wpdb->prepare($query_archive, array(
                $provider
            ));
            $res_archive = $wpdb->get_var($query_archive);
            $query_current = "SELECT COUNT(*) FROM {$wpdb->prefix}digilan_token_active_sessions_$version" . " WHERE authentication_mode='%s'";
            $query_current = $wpdb->prepare($query_current, array(
                $provider
            ));
            $res_current = $wpdb->get_var($query_current);
            $res = $res_archive + $res_current;
            array_push($data, $res);
        }
        return wp_json_encode($data);
    }
    
    public static function get_hostname_to_mac() {
        $aps = DigilanToken::$settings->get('access-points');
        $macs = array();
        foreach ($aps as $hostname => $ap) {
            $mac = str_replace(array(
                '-',
                ':'
            ), '', $ap['mac']);
            $mac = hexdec($mac);
            $macs[$hostname] = $mac;
        }
        return $macs;
    }

    public static function get_connection_count_from_previous_week()
    {
        global $wpdb;
        $query = "select 1 from visitor_dns_logs limit 1";
        $res = $wpdb->get_var($query);
        if ( $res === null ) {
            $macs = DigilanTokenConnection::get_hostname_to_mac();
            $data = array();
            $version = get_option('digilan_token_version');
            foreach ($macs as $hostname => $ap_mac) {
                $data[$hostname] = array();
                for ($day = 0; $day <= 6; $day++) {
                    $query = "SELECT DATE_ADD(NOW(), INTERVAL -$day DAY)";
                    $res = $wpdb->get_var($query);
                    $re = "/\s/";
                    $d = preg_split($re, $res)[0];
                    $timing = "AND ap_validation >= '$d 00:00:00' AND ap_validation <= '$d 23:59:59'";
                    $query = "SELECT COUNT(*) FROM {$wpdb->prefix}digilan_token_connections_$version "
                            . "WHERE ap_mac=$ap_mac " . $timing;
                    $res = $wpdb->get_var($query);
                    $query = "SELECT COUNT(*) FROM {$wpdb->prefix}digilan_token_active_sessions_$version "
                            . "WHERE ap_mac=$ap_mac " . $timing;
                    $r = $wpdb->get_var($query);
                    $res += $r;
                    $data[$hostname][$day] = $res;
                }
            }
            return wp_json_encode($data);
        } else {
            $data = array();
            // TODO
            return wp_json_encode($data);
        }
    }

    private static function new_user_connection($user_ip, $ap_mac, $secret, $sessionid)
    {
        $ap_mac = str_replace(array(
            '-',
            ':'
        ), '', $ap_mac);
        if (ip2long($user_ip) == false) {
            error_log("invalid client ip = " . $user_ip);
            return false;
        }
        $user_ip = ip2long($user_ip);
        if (hex2bin($secret) == false) {
            error_log("invalid secret = " . $secret);
            return false;
        }
        if (strlen($secret) != 32) {
            error_log("invalid secret length = " . $secret);
            return false;
        }
        if (hex2bin($sessionid) == false) {
            error_log("invalid session id = " . $sessionid);
            return false;
        }
        if (strlen($sessionid) != 32) {
            error_log("invalid secret length = " . $sessionid);
            return false;
        }
        if (strlen($ap_mac) != 12) {
            error_log("invalid ap mac length = " . $ap_mac);
            return false;
        }
        if (hex2bin($ap_mac) == false) {
            error_log("invalid ap mac = " . $ap_mac);
            return false;
        }
        $ap_mac = hexdec($ap_mac);
        return self::insert_in_connection($user_ip, $ap_mac, $secret, $sessionid) == 1;
    }

    private static function insert_in_connection($user_ip, $ap_mac, $secret, $sessionid)
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        $insert = $wpdb->insert("{$wpdb->prefix}digilan_token_active_sessions_$installed_version", array(
            'user_ip' => $user_ip,
            'ap_mac' => $ap_mac,
            'secret' => $secret,
            'creation' => current_time('mysql'),
            'sessionid' => $sessionid
        ), array(
            '%d',
            '%d',
            '%s',
            '%s',
            '%s'
        ));
        return $insert;
    }

    public static function archive_old_sessions()
    {
        if (!self::validate_wordpress_AP_secret()) {
            $data_array = array(
                'deactivated' => false,
                'message' => 'Invalid secret on AP.'
            );
            $response = wp_json_encode($data_array);
            wp_die($response, '', 500);
        }
        DigilanTokenLogs::archive_dns_records();
        $timeout = DigilanToken::$settings->get('timeout');
        global $wpdb;
        $version = get_option('digilan_token_version');
        $query = 'SELECT * FROM ' . $wpdb->prefix . 'digilan_token_active_sessions_' . $version;
        $active_sessions = $wpdb->get_results($query, ARRAY_A);
        foreach ($active_sessions as $connection) {
            $sessionid = $connection['sessionid'];
            $secret = $connection['secret'];
            $ap_validation = $connection['ap_validation'];
            // For authenticated users
            if (!empty($ap_validation)) {
                $is_active = self::is_user_session_active($ap_validation, $sessionid, $secret, $timeout);
                if (!$is_active) {
                    self::deauthenticate_AP_user($sessionid, $secret);
                }
            } else {
                // For anonymous connection
                $creation = $connection['creation'];
                $is_connection_active = self::is_user_session_active($creation, $sessionid, $secret, $timeout);
                if (!$is_connection_active) {
                    self::deauthenticate_AP_user($sessionid, $secret);
                }
            }
        }
        $data = array(
            'deactivated' => true
        );
        $message = wp_json_encode($data);
        wp_die($message, '', 200);
    }

    private static function is_user_session_active($ap_validation, $sessionid, $secret, $timeout)
    {
        $date_connection = new DateTime($ap_validation);
        $date_now = new DateTime(current_time('mysql'));
        $date_connection->modify('+' . $timeout . ' second');
        return $date_connection > $date_now;
    }

    private static function deauthenticate_AP_user($sessionid, $secret)
    {
        global $wpdb;
        $version = get_option('digilan_token_version');
        // Archive connection
        $query = 'INSERT INTO ' . $wpdb->prefix . 'digilan_token_connections_' . $version . '
                  SELECT * FROM ' . $wpdb->prefix . 'digilan_token_active_sessions_' . $version . '
                  WHERE sessionid=%s AND secret=%s';
        $query = $wpdb->prepare($query, array(
            $sessionid,
            $secret
        ));
        $r = $wpdb->query($query);
        if ($r === false) {
            return;
        }
        // Deleting active connection
        return $wpdb->delete($wpdb->prefix . 'digilan_token_active_sessions_' . $version, array(
            'sessionid' => $sessionid,
            'secret' => $secret
        ));
    }

    public static function validate_wordpress_AP_secret()
    {
        $digilan_token_secret = DigilanTokenSanitize::sanitize_get('digilan-token-secret');
        $digilan_token_wp_secret = get_option('digilan_token_secret');
        return $digilan_token_wp_secret == $digilan_token_secret;
    }

    private static function validate_wordpress_router_AP_secret()
    {
        $digilan_token_secret = DigilanTokenSanitize::sanitize_get('digilan-token-secret');
        $digilan_token_wp_secret = get_option('digilan_token_secret');
        if (DigilanToken::isFromCitybox()) {
            if ($digilan_token_wp_secret === $digilan_token_secret) {
                return true;
            }
            return false;
        }
        return $digilan_token_wp_secret == $digilan_token_secret;
    }

    public static function validate_user_connection()
    {
        $user_ip = DigilanTokenSanitize::sanitize_get('user_ip');
        $ap_mac = DigilanTokenSanitize::sanitize_get('ap_mac');
        $sessionid = DigilanTokenSanitize::sanitize_get('session_id');
        $secret = DigilanTokenSanitize::sanitize_get('secret');
        global $wpdb;
        $is_valid_secret = self::validate_wordpress_router_AP_secret();
        if (!$is_valid_secret) {
            $data_array = array(
                'validated' => false,
                'message' => 'Invalid secret on AP.'
            );
            $response = wp_json_encode($data_array);
            wp_die($response, '', 500);
        }

        if ($user_ip == false) {
            _default_wp_die_handler('Invalid user ip');
        }

        if ($ap_mac == false) {
            _default_wp_die_handler('Invalid ap mac');
        }

        if ($sessionid == false) {
            _default_wp_die_handler('Invalid session id');
        }

        if ($secret == false) {
            _default_wp_die_handler('Invalid secret');
        }

        $validated = self::validate_connection($user_ip, $ap_mac, $sessionid, $secret);
        $version = get_option('digilan_token_version');
        $q = 'SELECT authentication_mode, user_id FROM ' . $wpdb->prefix . 'digilan_token_active_sessions_' . $version . ' WHERE sessionid="%s" and secret="%s"';
        $safe_query = $wpdb->prepare($q, array(
            $sessionid,
            $secret
        ));
        $res = $wpdb->get_row($safe_query, ARRAY_A);
        $id = $res['user_id'];
        $auth_mode = $res['authentication_mode'];
        $q = 'SELECT social_id FROM ' . $wpdb->prefix . 'digilan_token_users_' . $version . ' WHERE id=%s';
        $safe_query = $wpdb->prepare($q, array(
            $id
        ));
        $social_id = $wpdb->get_var($safe_query);
        // When authenticated with wifi.lua script on AP.
        if (null === $id) {
            $id = 'auth';
        }
        $data_array = array(
            'authenticated' => false
        );
        $response = wp_json_encode($data_array);
        if ($validated) {
            $data_array = array(
                'authenticated' => true,
                'user_id' => $id,
                'social_id' => $social_id,
                'auth_type' => $auth_mode
            );
            if (DigilanToken::isFromCitybox()) {
                $settings = DigilanToken::$settings;
                $langing_page = $settings->get('landing-page');
                $data_array += array(
                    'landing_page' => $langing_page
                );
            }
            $response = wp_json_encode($data_array);
        }
        wp_die($response, '', 200);
    }

    private static function validate_connection($user_ip, $ap_mac, $sessionid, $secret)
    {
        $ap_mac = str_replace(array(
            '-',
            ':'
        ), '', $ap_mac);
        if (ip2long($user_ip) == false) {
            error_log("invalid client ip = " . $user_ip);
            return false;
        }
        $user_ip = ip2long($user_ip);
        if (hex2bin($secret) == false) {
            error_log("invalid secret = " . $secret);
            return false;
        }
        if (strlen($secret) != 32) {
            error_log("invalid secret length = " . $secret);
            return false;
        }
        if (hex2bin($sessionid) == false) {
            error_log("invalid session id = " . $sessionid);
            return false;
        }
        if (strlen($sessionid) != 32) {
            error_log("invalid secret length = " . $sessionid);
            return false;
        }
        if (strlen($ap_mac) != 12) {
            error_log("invalid ap mac length = " . $ap_mac);
            return false;
        }
        if (hex2bin($ap_mac) == false) {
            error_log("invalid ap mac = " . $ap_mac);
            return false;
        }
        $ap_mac = hexdec($ap_mac);
        $result = self::update_table_connection($user_ip, $ap_mac, $sessionid, $secret);
        return $result;
    }

    private static function update_table_connection($user_ip, $ap_mac, $sessionid, $secret)
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        $result = $wpdb->update("{$wpdb->prefix}digilan_token_active_sessions_$installed_version", array(
            'ap_validation' => current_time('mysql')
        ), array(
            'user_ip' => $user_ip,
            'ap_mac' => $ap_mac,
            'sessionid' => $sessionid,
            'secret' => $secret
        ));
        return $result > 0;
    }

    private static function select_secret_from_connection_row($sessionid)
    {
        $installed_version = DigilanTokenDB::$installed_version;
        if (hex2bin($sessionid) == false) {
            return false;
        }
        if (strlen($sessionid) != 32) {
            return false;
        }
        global $wpdb;
        $query = "SELECT secret FROM {$wpdb->prefix}digilan_token_active_sessions_$installed_version WHERE sessionid='$sessionid'";
        $safe_query = $wpdb->prepare($query, array(
            $sessionid
        ));
        return $wpdb->get_var($safe_query);
    }

    public static function redirect_to_access_point($sid)
    {
        $secret = self::select_secret_from_connection_row($sid);
        $tokens = array(
            "session_id" => $sid,
            "secret" => $secret,
            "type" => "digilantoken"
        );
        $ap_url = esc_url_raw(add_query_arg($tokens, "cloudgate.citypassenger.com/ws/wifi/public_wifi/auth.cgi"));
        wp_redirect($ap_url);
        exit();
    }

    public static function update_connection($sessionid, $authentication, $id)
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        $result = $wpdb->update("{$wpdb->prefix}digilan_token_active_sessions_$installed_version", array(
            'user_id' => $id,
            'authentication_mode' => $authentication,
            'wp_validation' => current_time('mysql')
        ), array(
            'sessionid' => $sessionid
        ));
        return $result > 0;
    }

    public static function initialize_new_connection()
    {
        $secret = bin2hex(random_bytes(16));
        $sessionid = bin2hex(random_bytes(16));
        $user_ip = DigilanTokenSanitize::sanitize_get('user_ip');
        $ap_mac = DigilanTokenSanitize::sanitize_get('ap_mac');

        $is_valid_secret = self::validate_wordpress_router_AP_secret();
        if (!$is_valid_secret) {
            $data_array = array(
                'validated' => false,
                'message' => 'Invalid secret on AP.'
            );
            $response = wp_json_encode($data_array);
            wp_die($response, '', 500);
        }
        if ($user_ip == false) {
            _default_wp_die_handler("Invalid user ip");
        }
        if ($ap_mac == false) {
            _default_wp_die_handler("Invalid ap mac");
        }
        $connection = self::new_user_connection($user_ip, $ap_mac, $secret, $sessionid);
        if ($connection == true) {
            $data_array = array(
                'validated' => true,
                'session_id' => "$sessionid",
                'secret' => "$secret"
            );
            $response = wp_json_encode($data_array);
        } else {
            $data_array = array(
                'validated' => false,
                'message' => 'wordpress database insert failed'
            );
            $response = wp_json_encode($data_array);
        }
        wp_die($response, '', 200);
    }

    public static function reauthenticate_user()
    {
        $mac = DigilanTokenSanitize::sanitize_get('mac');
        $is_valid_secret = self::validate_wordpress_AP_secret();
        if (!$is_valid_secret) {
            $data_array = array(
                'validated' => false,
                'message' => 'Invalid secret on AP.'
            );
            $response = wp_json_encode($data_array);
            wp_die($response, '', 500);
        }
        if ($mac == false) {
            _default_wp_die_handler("Invalid ap mac");
        }
        $connection = self::get_connection_with_mac($mac);
        if (!is_array($connection)) {
            $connection = array(
                'authenticated' => false
            );
        } else {
            $date = new DateTime($connection['ap_validation']);
            $timezone = get_option('gmt_offset');
            $date->modify('-' . $timezone . 'hours');
            $offset_date = $date->format('Y-m-d H:i:s');
            $offset_date = strtotime($offset_date);
            $connection['ap_validation'] = $offset_date;
            $connection = array_merge($connection, array(
                'authenticated' => true
            ));
        }
        $response = wp_json_encode($connection);
        wp_die($response, '', 200);
    }

    private static function get_connection_with_mac($mac)
    {
        $mac = str_replace(array(
            '-',
            ':'
        ), '', $mac);
        $mac = hexdec($mac);
        global $wpdb;
        $version = get_option('digilan_token_version');
        $ids = self::get_ids_from_mac($mac);
        $timeout = DigilanToken::$settings->get('timeout');
        $auth_date = new DateTime();
        $curr_date = new DateTime(current_time('mysql'));
        foreach ($ids as $id) {
            $query = 'SELECT sessionid, secret, ap_validation, user_id FROM ' . $wpdb->prefix . 'digilan_token_active_sessions_' . $version . ' WHERE user_id="%s"';
            $query = $wpdb->prepare($query, $id);
            $rows = $wpdb->get_results($query, ARRAY_A);
            foreach ($rows as $row) {
                if ($row) {
                    $ap_validation = strtotime($row['ap_validation']);
                    $auth_date->setTimestamp($ap_validation);
                    $auth_date->modify('+' . $timeout . 'second');
                    if ($curr_date < $auth_date) {
                        return $row;
                    }
                }
            }
        }
        return false;
    }

    private static function get_ids_from_mac($mac_int)
    {
        global $wpdb;
        $version = get_option('digilan_token_version');
        $query = 'SELECT id FROM ' . $wpdb->prefix . 'digilan_token_users_' . $version . ' WHERE mac=%s';
        $query = $wpdb->prepare($query, array(
            $mac_int
        ));
        return $wpdb->get_results($query, ARRAY_A);
    }

    public static function get_ap_from_sid($session_id)
    {
        global $wpdb;
        $version = get_option('digilan_token_version');
        $query = 'SELECT ap_mac FROM ' . $wpdb->prefix . 'digilan_token_active_sessions_' . $version . ' WHERE sessionid="%s"';
        $query = $wpdb->prepare($query, array(
            $session_id
        ));
        return $wpdb->get_var($query);
    }
}
