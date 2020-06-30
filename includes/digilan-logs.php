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
class DigilanTokenLogs
{

    public static function store_dns_logs()
    {
        $is_valid_secret = DigilanTokenConnection::validate_wordpress_AP_secret();
        if (!$is_valid_secret) {
            $data_array = array(
                'validated' => false,
                'message' => 'Invalid secret on AP.'
            );
            $response = wp_json_encode($data_array);
            wp_die($response, '', 500);
        }
        $logs = file_get_contents("php://input");
        $logs = json_decode($logs);
        if (empty($logs)) {
            $data = array(
                'message' => 'Empty or invalid data sent.'
            );
            $data = wp_json_encode($data);
            wp_die($data, '', 500);
        }
        $timezone = get_option('gmt_offset');
        foreach ($logs as $log) {
            $date = $log->date;
            $user_id = $log->user_id;
            $domain = $log->domain;
            $date_time = new DateTime($date);
            $date_time->modify('+' . $timezone . 'hours');
            $log_date = $date_time->format('Y-m-d H:i:s');
            $result = self::insert_dns_log($log_date, $user_id, $domain);
            if (!$result) {
                error_log('Failed to insert row in ' . $wpdb->prefix . 'digilan_token_log table.');
            }
        }
        $data = array(
            'message' => 'POST successful.'
        );
        $data = wp_json_encode($data);
        wp_die($data, '', 200);
    }

    private static function insert_dns_log($date, $user_id, $domain)
    {
        global $wpdb;
        if (!strtotime($date)) {
            error_log('insert_dns_log: Invalid date.');
            return false;
        }
        $re = '/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i';
        if (preg_match($re, $domain) != 1) {
            error_log('Invalid chars in domain.');
            return false;
        }
        $re = '/^.{1,253}$/';
        if (preg_match($re, $domain) != 1) {
            error_log('Invalid overall domain length.');
            return false;
        }
        $re = '/^[^\.]{1,63}(\.[^\.]{1,63})*$/';
        if (preg_match($re, $domain) != 1) {
            error_log('Invalid length in >=1 domain labels.');
            return false;
        }
        $installed_version = get_option('digilan_token_version');
        $query = "SELECT id FROM {$wpdb->prefix}digilan_token_users_$installed_version WHERE id=%s";
        $query = $wpdb->prepare($query, $user_id);
        $id = $wpdb->get_var($query);
        if (null === $id) {
            error_log('Client with this id does not exist in the table.');
            return false;
        }
        $data = array(
            'date' => $date,
            'user_id' => $user_id,
            'domain' => $domain
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $re = $wpdb->insert($wpdb->prefix . 'digilan_token_logs', $data, $format);
        return $re > 0;
    }

    public static function generate_csv($datetime_start, $datetime_end)
    {
        if (!strtotime($datetime_start)) {
            error_log($datetime_start . ' is an invalid date time format.');
            return false;
        }
        if (!strtotime($datetime_end)) {
            error_log($datetime_start . ' is an invalid date time format.');
            return false;
        }
        $start = preg_replace('/\s+/', '_', $datetime_start);
        $end = preg_replace('/\s+/', '_', $datetime_end);
        $filename = 'export_logs_dns_' . $start . '_' . $end;
        $filename = sanitize_file_name($filename);
        global $wpdb;
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
        header('Content-Transfer-Encoding: binary');

        $outstream = fopen('php://output', 'w');

        // Using mysqli here because there are no wpdb query function with unbuffering option.
        $host = DB_HOST;
        $username = DB_USER;
        $password = DB_PASSWORD;
        $db_name = DB_NAME;
        $mysqli = new mysqli($host, $username, $password, $db_name);
        $version = get_option('digilan_token_version');
        /*
         *  FETCH RECORDS FROM ARCHIVE TABLE
         */
        $query = "SELECT
               {$wpdb->prefix}digilan_token_logs_archive.date,
               {$wpdb->prefix}digilan_token_logs_archive.domain,
               {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_logs_archive
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version ON {$wpdb->prefix}digilan_token_users_$version.id = {$wpdb->prefix}digilan_token_logs_archive.user_id
        WHERE date >= '%s' AND date <= '%s'";
        $safe_query = $wpdb->prepare($query, array(
            $datetime_start . ' 00:00:00',
            $datetime_end . ' 23:59:59'
        ));
        $header = array(
            'date',
            'mac',
            'domain'
        );
        fputcsv($outstream, $header);
        $result = $mysqli->query($safe_query, MYSQLI_USE_RESULT);
        if ($result) {
            $c = 0;
            while ($row = $result->fetch_assoc()) {
                $c++;
                $mac = DigilanTokenSanitize::int_to_mac($row['mac']);
                $line = array(
                    $row['date'],
                    $mac,
                    $row['domain']
                );
                fputcsv($outstream, $line);
                if ($c == 1000) {
                    $c = 0;
                    flush();
                }
            }
        }
        $result->close();
        /*
         *  FETCH RECORDS FROM LIVE TABLE
         */
        $query = "SELECT
               {$wpdb->prefix}digilan_token_logs.date,
               {$wpdb->prefix}digilan_token_logs.domain,
               {$wpdb->prefix}digilan_token_users_$version.mac
        FROM {$wpdb->prefix}digilan_token_logs
        LEFT JOIN {$wpdb->prefix}digilan_token_users_$version ON {$wpdb->prefix}digilan_token_users_$version.id = {$wpdb->prefix}digilan_token_logs.user_id
        WHERE date >= '%s' AND date <= '%s'";
        global $wpdb;
        $safe_query = $wpdb->prepare($query, array(
            $datetime_start . ' 00:00:00',
            $datetime_end . ' 23:59:59'
        ));
        $result = $mysqli->query($safe_query, MYSQLI_USE_RESULT);
        if ($result) {
            $c = 0;
            while ($row = $result->fetch_assoc()) {
                $c++;
                $mac = DigilanTokenSanitize::int_to_mac($row['mac']);
                $line = array(
                    $row['date'],
                    $mac,
                    $row['domain']
                );
                fputcsv($outstream, $line);
                if ($c == 1000) {
                    $c = 0;
                    flush();
                }
            }
        }
        $result->close();
        $s = ob_get_clean();
        exit($s);
    }

    public static function archive_dns_records()
    {
        global $wpdb;
        $query = 'INSERT INTO ' . $wpdb->prefix . 'digilan_token_logs_archive SELECT * FROM ' . $wpdb->prefix . 'digilan_token_logs';
        $res = $wpdb->query($query);
        $query = 'TRUNCATE TABLE ' . $wpdb->prefix . 'digilan_token_logs';
        $wpdb->query($query);
        return $res;
    }
}
