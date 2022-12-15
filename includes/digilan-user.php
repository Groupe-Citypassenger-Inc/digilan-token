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
class DigilanTokenUser
{

    /*
     * Apparently correctly political to be allowed the right to be forgotten
     */
    public static function forget_me($social_id)
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        $update = $wpdb->update("{$wpdb->prefix}digilan_token_users_$installed_version", array(
            'mac' => 0,
            'social_id' => 'deletedUser'
        ), array(
            'social_id' => $social_id
        ), array(
            "%d",
            "%s"
        ), array(
            "%s"
        ));
        return $update;
    }

    public static function validate_user_on_wp($sessionid, $authentication, $user_id)
    {
        if ($authentication == '' || $user_id == '') {
            return false;
        }
        if (hex2bin($sessionid) == false) {
            error_log("invalid sid = " . $sessionid);
            return false;
        }
        if (strlen($sessionid) != 32) {
            error_log("sid length not 128 bits");
            return false;
        }
        $user_id = (int) $user_id;
        if (is_int($user_id) == false) {
            error_log("user_id not integer");
            return false;
        }
        return DigilanTokenConnection::update_connection($sessionid, $authentication, $user_id);
    }

    public static function select_user_id($mac, $social_id)
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        if ($social_id == '') {
            return false;
        }
        $mac = str_replace(array(
            '-',
            ':'
        ), '', $mac);
        $mac = hexdec($mac);
        $query = "SELECT id FROM {$wpdb->prefix}digilan_token_users_$installed_version WHERE mac=%s AND social_id='%s'";
        $safe_query = $wpdb->prepare($query, array(
            $mac,
            $social_id
        ));
        $id = $wpdb->get_var($safe_query);
        if ($id == null) {
            return false;
        }
        return $id;
    }

    public static function create_ap_user($mac, $social_id, $customized_user_info = array())
    {
        global $wpdb;
        $installed_version = DigilanTokenDB::$installed_version;
        $mac = str_replace(array(
            '-',
            ':'
        ), '', $mac);
        if (strlen($mac) != 12) {
            return false;
        }
        if (hex2bin($mac) == false) {
            return false;
        }
        $mac = hexdec($mac);
        $insert = $wpdb->insert("{$wpdb->prefix}digilan_token_users_$installed_version", array(
            "social_id" => $social_id,
            "mac" => $mac
        ), array(
            "%s",
            "%d"
        ));

        if ($insert === false) {
            return $insert;
        }

        global $wpdb;
        $last_id = $wpdb->insert_id;
        $json_customized_user_info = wp_json_encode($customized_user_info);

        $insert_data = $wpdb->insert("{$wpdb->prefix}digilan_token_meta_users_$installed_version", array(
            "user_id" => $last_id,
            "gender" => $json_customized_user_info->gender,
            "age" => $json_customized_user_info->age,
            "nationality" => $json_customized_user_info->nationality,
            "stay_length" => $json_customized_user_info->stay_length,
            "user_info" => $json_customized_user_info,
        ), array(
            "%s",
            "%s",
            "%d",
            "%s",
            "%d",
            "%s",
        ));
        return $insert_data;
    }
}
