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
    static $bigint = array("options" => array("min_range"=>$min, "max_range"=>$max))) ;

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
        # https://github.com/salsify/jsonstreamingparser are not basics stuff
        # anyway the most important is to not screw sql
        $logs = json_decode($logs);
        if (empty($logs)) {
            wp_die('', '', 500);
        }
        $timezone = get_option('gmt_offset');
        $inserts_logs = array();
        foreach ($logs as $log) {
            $date = $log->date;
            $user_id = $log->user_id;
            $domain = $log->domain;
            $date_time = new DateTime($date);
            #unchecked result here, what if $data invalid
            $date_time->modify('+' . $timezone . 'hours');
            $log_date = $date_time->format('Y-m-d H:i:s');
            if ( false == check_domain($domain) } {
                continue;
            }
            # not check user facepalm => need check format of it it will be selected 
            # ok that s just a list of number... a limited one => get it first or ju
            $subreq = "SELECT id FROM {$wpdb->prefix}digilan_token_users_$installed_version WHERE id=$user_id";
            # but we have CONSTRAINT `fk_digilan_token_logs_1` FOREIGN KEY (`user_id`) REFERENCES `wp_digilan_token_users_1` (`id
            # so lets just drop all the stuff we cdont care that much ( https and device should be ok )
            if (false === filter_var($user_id, FILTER_VALIDATE_INT, $bigint)) {
                continue;
            }
            array_push($inserts_logs, array(
                'date'   => $log_date,
                'user_id' => $subreq,
                'domain' => $domain
            ));
        }
        if (insert_dns_logs($inserts_logs)) {
            wp_send_json( array( 'message' => 'POST successful.') );
        }
        error_log('store_dns_logs/check_domain : catastrophic failure inserting log');
        wp_die('', '', 500);
    }

    
    private static function check_domain($domain) {
        if (filter_var('http://'.$domain, FILTER_VALIDATE_URL)) {
           return true;
        }
        error_log('store_dns_logs/check_domain : Invalid domain '.$domain);
        return true;
    }
    
    private static function insert_dns_logs($arr_values) {
        global $wpdb;
        $query = "INSERT INTO ".$wpdb->prefix . 'digilan_token_logs';
                ." (`date`, `user_id`, `domain`) VALUES ";
                .implode( ', ', $arr_values );
        $sql = $wpdb->prepare( "$query", $values );
        return true $wpdb->query( $sql );
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
