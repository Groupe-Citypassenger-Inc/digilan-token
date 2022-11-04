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
    # user_id in _digilan_token_users_ is int(11)
    static $bigint = array("options" => array("min_range" => 0, "max_range" => 2147483648));

    public static function store_dns_logs()
    {
        global $wpdb;
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
            wp_send_json(array('message' => 'POST successful.'));
            die;
        }
        $timezone = get_option('gmt_offset');
        $inserts_logs = array();
        $groupstoinsertnum = 0; # number row to insert in db
        foreach ($logs as $log) {
            try {
                $date_time = new DateTime($log->date);
            } catch (Exception $e) {
                error_log('store_dns_logs/check_domain : Invalid date. Command `new DateTime(' .$log->date. ')`' . $e);
                continue;
            }
            $date_time->modify('+' . $timezone . 'hours');
            if (false === filter_var('http://' . $log->domain, FILTER_VALIDATE_URL)) {
                error_log('store_dns_logs/check_domain : Invalid domain ' . $log->domain);
                continue;
            }
            if (false === filter_var($log->user_id, FILTER_VALIDATE_INT, DigilanTokenLogs::$bigint)) {
                error_log('store_dns_logs/check_domain : Invalid user_id ' . $log->user_id);
                continue;
            }
            array_push($inserts_logs, $date_time->format('Y-m-d H:i:s'), $log->user_id, $log->domain);
            $groupstoinsertnum++;
        }
        if ($groupstoinsertnum < 1) {
            wp_send_json(array('message' => 'POST successful.'));
            die;
        }
        # Be carefull to respect number of element need to insert on each db row
        $query = "INSERT INTO " . $wpdb->prefix . 'digilan_token_logs'
            . " (`date`, `user_id`, `domain`) VALUES "
            . str_repeat("( %s, %s, %s),", $groupstoinsertnum - 1)
            . "( %s, %s, %s)";
        $sql = $wpdb->prepare("$query", $inserts_logs);
        if ($wpdb->query($sql)) {
            wp_send_json(array('message' => 'POST successful.'));
            die;
        } else {
            error_log('store_dns_logs/check_domain : catastrophic failure inserting log');
            wp_die('', '', 500);
        }
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
