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

class DigilanTokenMultiPortal {
    
    public static function link_client_ap($hostname, $mac, $user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id');
            return;
        }
        $new_ap = array(
            $hostname => array(
                'mac' => $mac
            )
        );
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list !== '') {
            $ap_list = (array) maybe_unserialize($ap_list);
        } else {
            $ap_list = array();
        }
        
        $ap_list = array_merge($ap_list,$new_ap);
        if (!update_user_meta($user_id,'digilan-token-ap-list',$ap_list)) {
            error_log('Fail to update ap list of a user ');
            return;
        }
    }
    public static function unlink_client_ap($hostname, $user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id');
            return;
        }
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list !== '') {
            $ap_list = (array) maybe_unserialize($ap_list);
        } else {
            $ap_list = array();
        }
        if ($ap_list[$hostname]) {
            unset($ap_list[$hostname]);
        } else {
            error_log('This user doesn t have this ap.');
            return;
        }
        if (!update_user_meta($user_id,'digilan-token-ap-list',$ap_list)) {
            error_log('Fail to update ap list of a user ');
            return;
        }
    }

    public static function update_client_ap_setting($hostname, $portal, $landing, $timeout)
    {
        $access_points = DigilanToken::$settings->get('access-points');
        $new_setting = array(
            'portal' => $portal,
            'landing' => $landing,
            'timeout' => $timeout
        );
        $ap_list = self::get_client_ap_list_from_hostname($hostname);
        if (!$ap_list) {
            error_log('There is no ap associated with this hostname');
            return;
        }
        foreach ($ap_list as $key=>$value) {
            $access_points[$key] = array_merge($access_points[$key],$new_setting);
        }
        $updated_access_points = array(
            'access-points' => $access_points
        );
        DigilanToken::$settings->update($updated_access_points);
    }

    public static function get_client_ap_list_from_hostname($hostname)
    {
        global $wpdb;
        $ap_list = array();
        $query = "SELECT user_id,meta_value FROM {$wpdb->prefix}usermeta AS meta WHERE meta_key = '%s'";
        $query = $wpdb->prepare($query, 'digilan-token-ap-list');
        $rows = $wpdb->get_results($query);
        if (null === $rows) {
            error_log('Access points are not available.');
            return false;
        } else {
            foreach ($rows as $row) {
                $row = (array) maybe_unserialize($row);
                $aps = (array) maybe_unserialize($row['meta_value']);
                if ($aps[$hostname]) {
                    $ap_list = $aps;
                    break 1;
                }
            }
        }
        if (count($ap_list)>0) {
            return $ap_list;
        }
        return false;
    }
    
    public static function remove_all_ap_from_client($hostname, $user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id');
            return;
        }
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list !== '') {
            $ap_list = (array) maybe_unserialize($ap_list);
        } else {
            $ap_list = array();
        }
        $access_points = DigilanToken::$settings->get('access-points');

        foreach ($ap_list as $ap) {
            unset($access_points[$ap]);
        }
        DigilanToken::$settings->update(array(
            'access-points' => $access_points
        ))
        if (!update_user_meta($user_id,'digilan-token-ap-list',array())) {
            error_log('Fail to update ap list of a user ');
            return;
        }
    }
}