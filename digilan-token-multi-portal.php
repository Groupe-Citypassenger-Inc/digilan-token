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
    
    public static function link_client_ap($hostname, $user_id)
    {
        $ap_list = self::get_valid_ap_list($user_id);
        $access_points = DigilanToken::$settings->get('access-points');
        if (empty($access_points[$hostname])) {
            error_log($hostname.' is not linked to an AP - from link_client_ap function');
            return false;
        }
        if (false === empty($ap_list[$hostname])) {
            error_log($hostname.' is already linked - from link_client_ap function');
            return false;
        }
        $ap_list[$hostname] = $access_points[$hostname]['specific_ap_settings'];
        $update_result = self::update_client_ap_list($user_id,$ap_list);
        return $update_result;
    }

    public static function unlink_client_ap($hostname, $user_id)
    {
        $ap_list = self::get_valid_ap_list($user_id);
        if (empty($ap_list[$hostname])) {
            error_log('This user '.$user_id.'doesn t have '.$hostname.' as ap. - from unlink_client_ap function');
            return false;
        }
        unset($ap_list[$hostname]);
        $update_result = self::update_client_ap_list($user_id,$ap_list);
        return $update_result;
    }

    public static function update_client_ap_list_setting($hostname,$new_shared_settings)
    {
        $settings = clone DigilanToken::$settings;
        $access_points = $settings->get('access-points');
        $result_get_metauser_row = self::get_client_ap_list_from_hostname($hostname);
        $ap_list = $result_get_metauser_row['ap_list'];
        $user_id = $result_get_metauser_row['user_id'];
        foreach ($ap_list as $key=>$value) {
            if (empty($access_points[$key])) {
                error_log($key.' is not registered as ap or remove it from'.var_dump($ap_list).' - from update_client_ap_setting function');
                die();
            }
            $ap_list[$key]->update_settings($new_shared_settings);
            $access_points[$key]['specific_ap_settings']->update_settings($new_shared_settings);
        }
        $update_result = self::update_client_ap_list($user_id,$ap_list);
        if (false === $update_result) {
            error_log('Fail to update ap list of a user '.$user_id.' - from update_client_ap_setting function');
            die();
        }
        $updated_access_points = array(
            'access-points' => $access_points
        );
        DigilanToken::$settings->update($updated_access_points);
    }

    public static function update_client_ap_setting($hostname,$new_settings)
    {
        $settings = clone DigilanToken::$settings;
        $access_points = $settings->get('access-points');
        
        if (is_object($access_points[$hostname]['specific_ap_settings'])) {
            $specific_ap_settings = clone $access_points[$hostname]['specific_ap_settings'];
        }
        if (empty($specific_ap_settings)) {
            $mac_setting = array(
                'mac' => $access_points[$hostname]['mac'],
                'access' => $access_points[$hostname]['access']
            );
            $new_settings = array_merge($new_settings,$mac_setting);
            $access_points[$hostname] = $new_settings;
            //save only in global setting
            DigilanToken::$settings->update(array(
                'access-points' => $access_points
            ));
            return true;
        }

        $specific_ap_settings->update_settings($new_settings);
        $access_points[$hostname]['specific_ap_settings'] = $specific_ap_settings;  
        //save specific ap settings
        DigilanToken::$settings->update(array(
            'access-points' => $access_points
        ));
        //update in user ap list
        $result_get_metauser_row = self::get_client_ap_list_from_hostname($hostname);
        if (false == $result_get_metauser_row) {
            error_log('could not get client ap list - from update_client_ap_setting function');
            return false;
        }
        $ap_list = $result_get_metauser_row['ap_list'];
        $user_id = $result_get_metauser_row['user_id'];
        if (array_key_exists($hostname,$ap_list)) {
            $ap_list[$hostname]->update_settings($new_settings);
        }
        $update_result = self::update_client_ap_list($user_id,$ap_list);
        if (false === $update_result) {
            error_log('Fail to update ap list of a user '.$user_id.' - from update_client_ap_setting function');
            die();
        }
        return true;
    }

    public static function get_client_ap_list_from_hostname($hostname)
    {
        global $wpdb;
        $ap_list = array();
        $query = "SELECT user_id,meta_value FROM {$wpdb->prefix}usermeta AS meta WHERE meta_key = '%s'";
        $query = $wpdb->prepare($query, 'digilan-token-ap-list');
        $rows = $wpdb->get_results($query);
        if (is_null($rows)) {
            error_log('There are no Access points which is linked to a client,'.$hostname.'could not be be found. - from get_client_ap_list_from_hostname function');
            return false;
        }
        $last_error = $wpdb->last_error;
        if (!empty($last_error)) {
            error_log('Database error occured during db request, '.$last_error.' - from get_client_ap_list_from_hostname function');
            die();
        }
        foreach ($rows as $row) {
            $row = (array) maybe_unserialize($row);
            $current_id = (int) maybe_unserialize($row['user_id']);
            $aps = (array) maybe_unserialize($row['meta_value']);
            if (false === empty($aps[$hostname])) {
                $user_id = $current_id;
                $ap_list = $aps;
                break;
            }
        }
        if (empty($ap_list)) {
            error_log($hostname.' is not linked to a client. - from get_client_ap_list_from_hostname function');
            return false;
        }
        $result = array(
            'ap_list' => $ap_list,
            'user_id' => $user_id
        );
        return $result;
    }
    
    public static function remove_all_ap_from_client($user_id)
    {
        $ap_list = self::get_valid_ap_list($user_id);
        $settings = clone DigilanToken::$settings;
        $access_points = $settings->get('access-points');

        foreach ($ap_list as $key => $value) {
            if (empty($access_points[$key])) {
                error_log($key.' is linked to user '.$user_id.' but it is not registered. - from remove_all_ap_from_client function');
                die();
            }
            unset($access_points[$key]);
        }
        DigilanToken::$settings->update(array(
            'access-points' => $access_points
        ));
        $update_result = self::update_client_ap_list($user_id,array());
        return $update_result;
    }

    /**
     * @param int $user_id
     * @param array $ap_list
     */
    public static function update_client_ap_list($user_id,$ap_list)
    {
        $update_result = update_user_meta($user_id,'digilan-token-ap-list',$ap_list);
        if (false === $update_result) {
            error_log('Fail to update ap list of a user '.$user_id.' - from update_client_ap_list function');
            return false;
        }
        return true;
    }

    public static function get_valid_ap_list($user_id)
    {
        if (false === self::is_user_id_exist($user_id)) {
            error_log('Invalid user id '.$user_id.' - from get_ap_list function');
            die();
        }
        $ap_list = get_user_meta($user_id,'digilan-token-ap-list',true);
        if ($ap_list === false) {
            error_log('Could not get user ap list, user id '.$user_id.'invalid - from get_ap_list function');
            die();
        }
        return (array) maybe_unserialize($ap_list);
    }

    public static function is_user_id_exist($user_id)
    {
        $user = get_userdata($user_id);
        return (bool)$user;
    }
}