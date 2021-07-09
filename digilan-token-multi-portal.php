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
    
    public static function link_client_ap($hostname, $portal_settings, $user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id '.$user_id.', '.$hostname.' could not be linked - from link_client_ap function');
            return;
        }
        $new_ap = array(
            $hostname => $portal_settings->getConfig();
        );
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list !== '') {
            $ap_list = (array) maybe_unserialize($ap_list);
        } else {
            $ap_list = array();
        }
        if ($ap_list[$hostname]) {
            error_log($hostname.' is already linked - from link_client_ap function');
            return;
        }
        $ap_list = array_merge($ap_list,$new_ap);
        if (!update_user_meta($user_id,'digilan-token-ap-list',$ap_list)) {
            error_log('Fail to update ap list of user '.$user_id.', '.$hostname.' could not be linked - from link_client_ap function');
            return;
        }
    }
    public static function unlink_client_ap($hostname, $user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id '.$user_id.', '.$hostname.' could not be unlinked - from unlink_client_ap function');
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
            error_log('This user '.$user_id.'doesn t have '.$hostname.' as ap. - from unlink_client_ap function');
            return;
        }
        if (!update_user_meta($user_id,'digilan-token-ap-list',$ap_list)) {
            error_log('Fail to update ap list of a user '.$user_id.' wanted ap list: '.var_dump($ap_list).' - from unlink_client_ap function');
            return;
        }
    }

    public static function update_client_ap_setting($hostname,$portal_settings)
    {
        $access_points = DigilanToken::$settings->get('access-points');
        $new_setting = $portal_settings->getConfig();
        $ap_list = self::get_client_ap_list_from_hostname($hostname);
        if (!$ap_list) {
            error_log('There is no ap linked to a client and associated with '.$hostname.', ap may exist but it is not linked to a client.  - from update_client_ap_setting function');
            return;
        }
        foreach ($ap_list as $key=>$value) {
            if ($access_points[$key]) {
                $access_points[$key] = array_merge($access_points[$key], $new_setting);
            } else {
                error_log($key.' is not registered as ap - from update_client_ap_setting function');
            }
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
            error_log('There are no Access points which is linked to a client. - from get_client_ap_list_from_hostname function');
            return false;
        }
        foreach ($rows as $row) {
            $row = (array) maybe_unserialize($row);
            $aps = $row['meta_value'];
            if ($aps[$hostname]) {
                $ap_list = $aps;
                break;
            }
        }
        if (empty_array($ap_list)) {
            error_log($hostname.' is not linked to an ap. - from get_client_ap_list_from_hostname function');
            return false;
        }
        return $ap_list;
        

    }
    
    public static function remove_all_ap_from_client($user_id)
    {
        if (!$user_id) {
            error_log('Invalid user id '.$user_id.' - from remove_all_ap_from_client function');
            return;
        }
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list !== '') {
            $ap_list = (array) maybe_unserialize($ap_list);
        } else {
            $ap_list = array();
        }
        $access_points = DigilanToken::$settings->get('access-points');

        foreach ($ap_list as $key => $value) {
            unset($access_points[$key]);
        }
        DigilanToken::$settings->update(array(
            'access-points' => $access_points
        ))
        if (!update_user_meta($user_id,'digilan-token-ap-list',array())) {
            error_log('Fail to update ap list of a user '.$user_id.' - from remove_all_ap_from_client function');
            return;
        }
    }
}