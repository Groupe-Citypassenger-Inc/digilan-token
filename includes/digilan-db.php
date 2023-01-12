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
class DigilanTokenDB
{

  public static $installed_version = 1;

  public static function check_upgrade_digilan_token_plugin()
  {
    global $wpdb;
    $current_version = get_option("digilan_token_version");
    if (!isset($current_version)) {
      return false;
    }
    if (version_compare($current_version, self::$installed_version, '<')) {
      // Code to upgrade database on version update
      $result = false;
      if ($result !== false) {
        update_option("digilan_token_version", self::$installed_version);
        $old_version = self::$installed_version - 2;
        $drop_connection = "DROP TABLE IF EXISTS {$wpdb->prefix}digilan_token_connections_$old_version";
        $drop_users = "DROP TABLE IF EXISTS {$wpdb->prefix}digilan_token_users_$old_version";
        $drop_active = "DROP TABLE IF EXISTS {$wpdb->prefix}digilan_token_active_connections_$old_version";
        $wpdb->query($drop_connection);
        $wpdb->query($drop_users);
        $wpdb->query($drop_active);
      }
    }
  }

  static function wp_digilan_token_users() {
    global $wpdb;
    $sql_users = "CREATE TABLE IF NOT EXISTS %sdigilan_token_users_%d (
      id INT NOT NULL AUTO_INCREMENT,
      mac BIGINT,
      social_id CHAR(254),
      creation DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
      )";
    return sprintf($sql_users, $wpdb->prefix, self::$installed_version);
  }

  static function wp_digilan_token_connections_current() {
    global $wpdb;
    $sql_current_connections = "CREATE TABLE IF NOT EXISTS %sdigilan_token_active_sessions_%d (
      id INT NOT NULL AUTO_INCREMENT,
      user_ip BIGINT,
      ap_mac BIGINT,
      creation DATETIME DEFAULT CURRENT_TIMESTAMP,
      ap_validation DATETIME,
      wp_validation DATETIME,
      secret CHAR(32) NOT NULL,
      authentication_mode CHAR(254),
      sessionid CHAR(32) NOT NULL,
      user_id INT,
      PRIMARY KEY (id),
      FOREIGN KEY `fk_%sdigilan_token_curr_%d` (user_id) REFERENCES %sdigilan_token_users_%d(id)
      )";
    return sprintf(
      $sql_current_connections,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
    );
  }

  static function wp_digilan_token_connections() {
    global $wpdb;
    $sql_connections = "CREATE TABLE IF NOT EXISTS %sdigilan_token_connections_%d (
      id INT NOT NULL AUTO_INCREMENT,
      user_ip BIGINT,
      ap_mac BIGINT,
      creation DATETIME DEFAULT CURRENT_TIMESTAMP,
      ap_validation DATETIME,
      wp_validation DATETIME,
      secret CHAR(32) NOT NULL,
      authentication_mode CHAR(254),
      sessionid CHAR(32) NOT NULL,
      user_id INT,
      PRIMARY KEY (id),
      FOREIGN KEY `fk_%sdigilan_token_%d` (user_id) REFERENCES %sdigilan_token_users_%d(id)
      )";
    return sprintf(
      $sql_connections,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
    );
  }

  static function wp_digilan_token_version() {
    global $wpdb;
    return sprintf(
      "CREATE TABLE IF NOT EXISTS %sdigilan_token_version (
        version INT NOT NULL
      )",
      $wpdb->prefix,
    );
  }

  static function wp_digilan_token_logs() {
    global $wpdb;
    return sprintf(
      "CREATE TABLE IF NOT EXISTS %sdigilan_token_logs (
        `date` DATETIME,
        `user_id` INT,
        `domain` VARCHAR(253),
        FOREIGN KEY `fk_%sdigilan_token_logs_%d` (user_id) REFERENCES %sdigilan_token_users_%d(id)
      )",
      $wpdb->prefix,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
    );
  }

  static function wp_digilan_token_archive_logs() {
    global $wpdb;
    return sprintf(
      "CREATE TABLE IF NOT EXISTS %sdigilan_token_logs_archive (
        `date` DATETIME,
        `user_id` INT,
        `domain` VARCHAR(253),
        FOREIGN KEY `fk_%sdigilan_token_logs_archive_%d` (user_id) REFERENCES %sdigilan_token_users_%d(id)
      )",
      $wpdb->prefix,
      $wpdb->prefix,
      self::$installed_version,
      $wpdb->prefix,
      self::$installed_version,
    );
  }

  static function wp_digilan_social_users() {
    global $wpdb;
    return sprintf(
      "CREATE TABLE IF NOT EXISTS %sdigilan_token_social_users_%d (
        `ID` int(11) NOT NULL,
        `type` VARCHAR(20) NOT NULL,
        `identifier` VARCHAR(100) NOT NULL,
        KEY `ID` (`ID`,`type`)
      )",
      $wpdb->prefix,
      self::$installed_version,
    );
  }

  static function wp_digilan_token_meta_users() {
    global $wpdb;
    return sprintf(
      // user_info size unknown, depends on:
      // - number of information asked to the user
      // - length of answers
      "CREATE TABLE IF NOT EXISTS %sdigilan_token_meta_users_%d (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT,
        `gender` VARCHAR(15),
        `age` INT(3),
        `nationality` VARCHAR(2),
        `stay_length` INT(3),
        `user_info` JSON,
        PRIMARY KEY (id),
        FOREIGN KEY `fk_%sdigilan_token_meta_%d` (user_id) REFERENCES %sdigilan_token_users_%d(id)
      )",
      $wpdb->prefix, self::$installed_version, $wpdb->prefix, self::$installed_version, $wpdb->prefix, self::$installed_version,
    );
  }

  public static function install_plugin_tables()
  {
    global $wpdb;
    $installed_version = self::$installed_version;
    $sqls = array(
      "wp_digilan_token_users" => self::wp_digilan_token_users(),
      "wp_digilan_token_connections_current" => self::wp_digilan_token_connections_current(),
      "wp_digilan_token_connections" => self::wp_digilan_token_connections(),
      "wp_digilan_token_version" => self::wp_digilan_token_version(),
      "wp_digilan_token_logs" => self::wp_digilan_token_logs(),
      "wp_digilan_token_archive_logs" => self::wp_digilan_token_archive_logs(),
      "wp_digilan_social_users" => self::wp_digilan_social_users(),
      "wp_digilan_token_meta_users" => self::wp_digilan_token_meta_users(),
    );
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($sqls as $sql) {
      $sql .= "$charset_collate;";
      dbDelta($sql);
    }
    update_option("digilan_token_version", $installed_version);

    $query = "CREATE OR REPLACE INDEX {$wpdb->prefix}digilan_token_index_mac ON {$wpdb->prefix}digilan_token_users_" . self::$installed_version . " (mac)";
    $wpdb->query($query);
    $query = "INSERT INTO {$wpdb->prefix}digilan_token_version (`version`) VALUES (1)";
    $version_rows = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}digilan_token_version", ARRAY_A);
    if ($version_rows && count($version_rows) > 0) {
      return;
    }
    $wpdb->insert("{$wpdb->prefix}digilan_token_version", array(
      "version" => 1
    ), array(
      "%d"
    ));

    update_option('cityscope_backend', 'https://admin.citypassenger.com/2019/Portals');
    update_option('digilan_token_user_form_fields', DigilanTokenCustomPortalConstants::$user_form_fields);
    update_option('digilan_token_nationality_iso_code', DigilanTokenCustomPortalConstants::$nationality_iso_code);
    update_option('digilan_token_type_options_display_name', DigilanTokenCustomPortalConstants::$type_option_display_name);
    update_option('digilan_token_form_languages',  DigilanTokenCustomPortalConstants::$langs_available);
  }
}
