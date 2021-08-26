<?php
/*
 * Plugin Name: Digilan Token
 * Plugin URI: https://www.citypassenger.com
 * Description: This plugin helps transform a WordPress into a third party authenticator services.
 * Version: 2.8.4
 * Author: Citypassenger
 * Text Domain: digilan
 * Domain Path: /languages
 * License: GPL2
 */
/*
 * License:
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
if (!defined('DLT_PATH_FILE')) {
    define('DLT_PATH_FILE', __FILE__);
}

if (!defined('DLT_PATH')) {
    define('DLT_PATH', dirname(DLT_PATH_FILE));
}
if (!defined('DLT_PLUGIN_BASENAME')) {
    define('DLT_PLUGIN_BASENAME', plugin_basename(DLT_PATH_FILE));
}

require_once(DLT_PATH . '/includes/digilan-exceptions.php');
require_once dirname(__FILE__) . '/DLT/Persistent/Persistent.php';
require_once dirname(__FILE__) . '/DLT/Notices.php';
require_once dirname(__FILE__) . '/DLT/REST.php';

require_once(DLT_PATH . '/includes/digilan-db.php');
require_once(DLT_PATH . '/includes/digilan-sanitize.php');
require_once(DLT_PATH . '/includes/digilan-logs.php');
require_once(DLT_PATH . '/includes/digilan-connection.php');
require_once(DLT_PATH . '/includes/digilan-user.php');
require_once(DLT_PATH . '/includes/digilan-activator.php');

require_once(DLT_PATH . '/digilan-class-settings.php');
require_once(DLT_PATH . '/includes/digilan-provider.php');
require_once(DLT_PATH . '/admin/digilan-admin.php');

require_once(DLT_PATH . '/digilan-token-multi-portal.php');
require_once(DLT_PATH . '/digilan-portal-model.php');

if (!version_compare(PHP_VERSION, '5.4', '>=')) {
    add_action('admin_notices', 'dlt_fail_php_version');
} elseif (!version_compare(get_bloginfo('version'), '4.6', '>=')) {
    add_action('admin_notices', 'dlt_fail_wp_version');
}

function html_code_for_speedtest($buffer) {
    return $buffer;
}

function dlt_fail_php_version()
{
    $message = sprintf(esc_html__('%1$s requires PHP version %2$s+, plugin is currently NOT ACTIVE.', 'digilan-token'), 'Digilan Token', '5.4');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

function dlt_fail_wp_version()
{
    $message = sprintf(esc_html__('%1$s requires WordPress version %2$s+. Because you are using an earlier version, the plugin is currently NOT ACTIVE.', 'digilan-token'), 'Digilan Token', '4.6');
    $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
    echo wp_kses_post($html_message);
}

class DigilanToken
{

    public static $digilan_version = 2.8;

    /** @var DigilanTokenSettings */
    public static $settings;

    private static $styles = array(
        'default' => array(
            'container' => 'dlt-container-block'
        ),
        'icon' => array(
            'container' => 'dlt-container-inline'
        )
    );

    public static $providersPath;

    /**
     *
     * @var DigilanTokenSocialProviderDummy[]
     */
    public static $providers = array();

    /**
     *
     * @var DigilanTokenSocialProvider[]
     */
    public static $allowedProviders = array();

    /**
     *
     * @var DigilanTokenSocialProvider[]
     */
    private static $ordering = array();

    private static $loginHeadAdded = false;

    private static $loginMainButtonsAdded = false;

    public static $counter = 1;

    public static $WPLoginCurrentView = '';

    public static $WPLoginCurrentFlow = 'login';

    public static function init()
    {
        add_action('plugins_loaded', 'DigilanToken::plugins_loaded');
        add_action('activated_plugin', 'DigilanToken::generate_keys');
        add_action('plugins_loaded', 'DigilanTokenDB::check_upgrade_digilan_token_plugin');
        register_activation_hook(DLT_PATH_FILE, 'DigilanTokenDB::install_plugin_tables');
        register_activation_hook(DLT_PATH_FILE, 'DigilanTokenActivator::cityscope_bonjour');
        register_activation_hook(DLT_PATH_FILE, 'DigilanToken::create_error_page');
        register_activation_hook(DLT_PATH_FILE, 'DigilanToken::create_default_portal_page');

        add_action('delete_user', 'DigilanToken::delete_user');

        self::$settings = new DigilanTokenSettings('digilan-token_social_login', array(
            'access-points' => array(),
            'ordering' => array(
                'facebook',
                'google',
                'twitter',
                'transparent',
                'mail'
            ),
            'portal-page' => '',
            'timeout' => 43200,
            'landing-page' => get_site_url(),
            'pre-activation' => 0,
            'redirect' => '',
            'redirect_reg' => '',
            'schedule_router' => '{"0":[],"1":[],"2":[],"3":[],"4":[],"5":[],"6":[]}',
            'debug' => '0'
        ));
        add_option('cityscope_backend', 'https://admin.citypassenger.com/2019/Portals');
    }
    public static function generate_keys() {
        $config = array(
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA);
        $priv_key = openssl_pkey_new($config);
        if (false == $priv_key) {
            throw new Exception("Fail to generate private keys");
        }
        if (false == openssl_pkey_export($priv_key,$str_priv_key)) {
            throw new Exception("Fail to prepare private key");
        }
        $detail_key = openssl_pkey_get_details($priv_key);
        if(false == $detail_key){
            throw new Exception(openssl_error_string());
        }
        $pub_key = openssl_pkey_get_details($priv_key)['key'];
        $b64_private_key = base64_encode("$str_priv_key");
        $b64_public_key = base64_encode("$pub_key");

        if (get_option('digilan_token_mail_private_key',false) && get_option('digilan_token_mail_public_key',false)) {
            update_option('digilan_token_mail_private_key', $b64_private_key);
            update_option('digilan_token_mail_public_key', $b64_public_key);
        }else{
            if (false == add_option('digilan_token_mail_private_key',$b64_private_key)) {
                throw new Exception("Fail to store encoded private key in wp option");
            }
            if (false == add_option('digilan_token_mail_public_key',$b64_public_key)) {
                throw new Exception("Fail to store encoded plubic key in wp option");
            }
        }
    }
    public static function plugins_loaded()
    {
        do_action('dlt_start');
        // Change name in languages directory.
        load_plugin_textdomain('digilan-token', false, basename(dirname(__FILE__)) . '/languages/');

        DigilanTokenAdmin::init();

        \DLT\Notices::init();

        self::$providersPath = DLT_PATH . '/providers/';

        $providers = array_diff(scandir(self::$providersPath), array(
            '..',
            '.'
        ));

        foreach ($providers as $provider) {
            if (file_exists(self::$providersPath . $provider . '/' . $provider . '.php')) {
                require_once(self::$providersPath . $provider . '/' . $provider . '.php');
            }
        }

        do_action('dlt_add_providers');

        self::$ordering = array_flip(self::$settings->get('ordering'));
        uksort(self::$providers, 'DigilanToken::sortProviders');
        uksort(self::$allowedProviders, 'DigilanToken::sortProviders');

        do_action('dlt_providers_loaded');

        add_action('login_form_login', 'DigilanToken::login_form_login');
        add_action('login_form_register', 'DigilanToken::login_form_register');
        add_action('login_form_link', 'DigilanToken::login_form_link');
        add_action('login_form_unlink', 'DigilanToken::login_form_unlink');
        add_action('parse_request', 'DigilanToken::editProfileRedirect');
        add_action('wp_head', 'DigilanToken::styles', 100);
        add_shortcode('digilan_speedtest', 'DigilanToken::speedtest_shortcode');
        add_shortcode('digilan_token', 'DigilanToken::widgetShortcode');
        add_shortcode('digilan_token_schedule', 'DigilanToken::widgetNextOpeningDate');
        add_shortcode('wifi4eu_img', 'DigilanToken::wifi4euShortcode');
        add_action('admin_enqueue_scripts', 'DigilanToken::enqueue_scripts');
        require_once(DLT_PATH . '/digilan-widget.php');
        require_once(DLT_PATH . '/digilan-noaccess-widget.php');
        require_once(DLT_PATH . '/digilan-wifi4eu-widget.php');
        do_action('dlt_init');
    }

    public static function isFromCitybox()
    {
        if (!defined('ABSPATH')) {
            error_log('ABSPATH is not defined.');
            return false;
        }
        $filename = ABSPATH . 'citynet.token';
        if (!file_exists($filename)) {
            return false;
        }
        if (!isset($_SERVER['CITYNET_TOKEN'])) {
            return false;
        }
        $local_token = file_get_contents($filename);
        if (!$local_token) {
            error_log('Failed to read file: ' . $filename);
            return false;
        }
        return $local_token == $_SERVER['CITYNET_TOKEN'];
    }

    public static function removeLoginFormAssets()
    {
        remove_action('login_head', 'DigilanToken::loginHead', 100);
        remove_action('login_footer', 'DigilanToken::scripts', 100);
    }

    public static function enqueue_scripts($hook)
    {
        if ($hook != "toplevel_page_digilan-token-plugin") {
            return;
        }

        $view = DigilanTokenSanitize::sanitize_get('view');
        if ($view === 'connections') {
            wp_enqueue_script('Chart', plugins_url('/js/lib/Chart.js', __FILE__));
            wp_enqueue_script('datatables', plugins_url('/js/lib/datatables.min.js', __FILE__));
            wp_enqueue_style('datatables_css', plugins_url('/css/datatables.min.css', __FILE__));
            wp_register_script('dlt-connections', plugins_url('/js/admin/connections.js', __FILE__), array(
                'jquery'
            ), false, false);
            wp_enqueue_script('dlt-connections');
            $days = array(
                __('Sunday', 'digilan-token'),
                __('Monday', 'digilan-token'),
                __('Tuesday', 'digilan-token'),
                __('Wednesday', 'digilan-token'),
                __('Thursday', 'digilan-token'),
                __('Friday', 'digilan-token'),
                __('Saturday', 'digilan-token')
            );
            $labels = array(
                'pie_chart' => array(
                    'title' => __('Authentication modes repartition', 'digilan-token')
                ),
                'line_chart' => array(
                    'title' => __('Number of connections to Access Point for the past week', 'digilan-token'),
                    'xLabel' => __('Current week', 'digilan-token'),
                    'yLabel' => __('Number of users connected', 'digilan-token')
                )
            );
            $datatables_opt = array(
                'url' => plugins_url('/languages/digilan-token-fr_FR.json', DLT_PLUGIN_BASENAME),
                'locale' => get_locale()
            );
            $data = array(
                'pie_chart' => DigilanTokenConnection::get_connection_repartition(),
                'line_chart' => DigilanTokenConnection::get_connection_count_from_previous_week(),
                'datatable' => DigilanTokenConnection::output_connections()
            );
            wp_localize_script('dlt-connections', 'dlt_data', $data);
            wp_localize_script('dlt-connections', 'dlt_datatables', $datatables_opt);
            wp_localize_script('dlt-connections', 'dlt_charts_labels', $labels);
            wp_localize_script('dlt-connections', 'dlt_days', $days);
        }

        if ($view == 'providers') {
            $data = array(
                '_ajax_nonce' => wp_create_nonce('digilan-token-plugin'),
                'savingMessage' => __('Saving...', 'digilan-token'),
                'errorMessage' => __('Saving failed', 'digilan-token'),
                'successMessage' => __('Order Saved', 'digilan-token')
            );
            wp_enqueue_script('jquery-ui-sortable');
            wp_register_script('dlt-providers-frontend-ajax', plugins_url('/js/admin/providers.js', __FILE__), array(
                'jquery'
            ), false, false);
            wp_enqueue_script('dlt-providers-frontend-ajax');
            wp_localize_script('dlt-providers-frontend-ajax', 'provider_ajax', $data);
        }

        if ($view == 'settings') {
            wp_register_script('dlt-settings', plugins_url('/js/admin/settings.js', __FILE__), array(
                'jquery'
            ), false, false);
            wp_enqueue_script('dlt-settings');
            $data = array(
                   '_ajax_nonce' => wp_create_nonce('digilan-token-cityscope'),
                   'successMessage' => __('Success', 'digilan-token'),
                   'errorMessage' => __('Failed', 'digilan-token')
            );
            wp_localize_script('dlt-settings', 'settings_data', $data);
        }

        if ($view == 'mailing') {
            wp_enqueue_script('dlt-mailing', plugins_url('/js/admin/mailing.js', __FILE__), array(
                'jquery'
            ), false, false);
        }

        $page = DigilanTokenSanitize::sanitize_get('page');
        if ($page == 'digilan-token-plugin') {
            if ($view === false || $view === 'access-point') {
                wp_enqueue_script('dlt-schedule', plugins_url('/js/lib/schedule.js', __FILE__));
                $days = array(
                    __('Mon', 'digilan-token'),
                    __('Tue', 'digilan-token'),
                    __('Wed', 'digilan-token'),
                    __('Thu', 'digilan-token'),
                    __('Fri', 'digilan-token'),
                    __('Sat', 'digilan-token'),
                    __('Sun', 'digilan-token')
                );
                if (self::isFromCitybox()) {
                    wp_register_script('dlt-access-point-router', plugins_url('/js/admin/access-point-router.js', __FILE__), array(
                        'jquery'
                    ), false, false);
                    wp_enqueue_script('dlt-access-point-router');
                    $data = array(
                        'schedule' => self::$settings->get('schedule_router')
                    );
                    wp_localize_script('dlt-access-point-router', 'dlt', $data);
                    wp_localize_script('dlt-access-point-router', 'dlt_days', $days);
                }
                wp_register_script('dlt-access-point', plugins_url('/js/admin/access-point.js', __FILE__), array(
                    'jquery'
                ), false, false);
                wp_enqueue_script('dlt-access-point');
                $hostnames = self::$settings->get('access-points');
                $data = array();
                foreach ($hostnames as $hostname => $config) {
                    $data[$hostname] = array();
                    $data[$hostname]['ssid'] = $config['ssid'];
                    $data[$hostname]['schedule'] = $config['schedule'];
                    $data[$hostname]['country_code'] = $config['country_code'];
                    $data[$hostname]['mac'] = $config['mac'];
                }
                $data['url'] = self::$settings->get('portal-page');
                wp_localize_script('dlt-access-point', 'dlt_ap', $data);
                wp_localize_script('dlt-access-point', 'dlt_days', $days);
            }
        }
    }

    public static function styles()
    {
        wp_enqueue_style('digilan_token_button_style', plugins_url('/template-parts/style.css', __FILE__));
    }

    public static function loginHead()
    {
        self::styles();
        self::$loginHeadAdded = true;
    }

    public static function sortProviders($a, $b)
    {
        if (isset(self::$ordering[$a]) && isset(self::$ordering[$b])) {
            if (self::$ordering[$a] < self::$ordering[$b]) {
                return -1;
            }

            return 1;
        }
        if (isset(self::$ordering[$a])) {
            return -1;
        }

        return 1;
    }

    /**
     *
     * @param $provider DigilanTokenSocialProviderDummy
     */
    public static function addProvider($provider)
    {
        self::$providers[$provider->getId()] = $provider;

        if ($provider instanceof DigilanTokenSocialProvider) {
            self::$allowedProviders[$provider->getId()] = $provider;
        }
    }

    public static function login_form_login()
    {
        self::$WPLoginCurrentView = 'login';
        self::login_init();
    }

    public static function login_form_register()
    {
        self::$WPLoginCurrentView = 'register';
        self::login_init();
    }

    public static function login_form_link()
    {
        self::$WPLoginCurrentView = 'link';
        self::login_init();
    }

    public static function login_form_unlink()
    {
        self::$WPLoginCurrentView = 'unlink';
        self::login_init();
    }

    public static function login_init()
    {
        add_filter('wp_login_errors', 'DigilanToken::wp_login_errors');
        $login_social = DigilanTokenSanitize::sanitize_request('loginSocial');
        if ($login_social && isset(self::$providers[$login_social]) && (self::$providers[$login_social]->isConfigured() || self::$providers[$login_social]->isTest())) {
            nocache_headers();

            self::$providers[$login_social]->connect();
        }
    }

    public static function wp_login_errors($errors)
    {
        if (empty($errors)) {
            $errors = new WP_Error();
        }

        $errorMessages = \DLT\Notices::getErrors();
        if ($errorMessages !== false) {
            foreach ($errorMessages as $errorMessage) {
                $errors->add('error', $errorMessage);
            }
        }

        return $errors;
    }

    public static function editProfileRedirect()
    {
        global $wp;

        if (isset($wp->query_vars['editProfileRedirect'])) {
            header('LOCATION: ' . self_admin_url('profile.php'));
            exit();
        }
    }

    public static function create_error_page()
    {
        global $wpdb;
        $query = "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'";
        $query = $wpdb->prepare($query, 'digilan-token-error');
        if (null === $wpdb->get_row($query, ARRAY_A)) {
            $current_user = wp_get_current_user();
            $page = array(
                'post_title' => 'Digilan Token Error',
                'post_status' => 'publish',
                'post_author' => $current_user->ID,
                'post_type' => 'page'
            );
            wp_insert_post($page);
        } else {
            error_log('Error page already exists.');
            return;
        }
    }

    public static function create_default_portal_page()
    {
        global $wpdb;
        $query = "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = '%s'";
        $query = $wpdb->prepare($query, 'captive-portal');
        if (null === $wpdb->get_row($query, ARRAY_A)) {
            $current_user = wp_get_current_user();
            $page = array(
                'post_title' => 'captive-portal',
                'post_status' => 'publish',
                'post_author' => $current_user->ID,
                'post_type' => 'page'
            );
            wp_insert_post($page);
        } else {
            error_log('Captive portal page already exists.');
            return;
        }
    }

    public static function wifi4euShortcode()
    {
        $wifi4eu_img = '<img id="wifi4eubanner">';
        if (method_exists('\Elementor\Editor', 'is_edit_mode')) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $wifi4eu_img = '<img id="wifi4eu-placeholder" src="https://collection.wifi4eu.ec.europa.eu/media/banner/Wifi4EU-FR.svg">';
            }
        }
        $wifi4eu_id = get_option('digilan_token_wifi4eu');
        if ($wifi4eu_id) {
			wp_register_script('wifi4eu_info', '');
			wp_enqueue_script('wifi4eu_info');
			$wifi4eu_script  = 'var wifi4euTimerStart = Date.now();';
			$wifi4eu_script .= 'var wifi4euNetworkIdentifier = '. json_encode($wifi4eu_id).';';
			$wifi4eu_script .= 'var wifi4euLanguage = '. json_encode(substr(get_locale(), 0, 2)) .';';
			wp_add_inline_script('wifi4eu_info', $wifi4eu_script);
        } else {
            error_log("WIFI4EU snippet issue: no wifi4eu key defined (key provided : ' . $wifi4eu_id . ')");
        }
        wp_enqueue_script('wifi4eu_script', 'https://collection.wifi4eu.ec.europa.eu/wifi4eu.min.js'); # need for banner auto load
        return $wifi4eu_img;
    }

    public static function widgetNextOpeningDate($atts)
    {
        $hostname = DigilanTokenSanitize::sanitize_get('hostname');
        if (false === $hostname) {
            return;
        }
        $now = current_time('mysql');
        $res = self::getNextOpeningDate($now, $hostname);
        if ($res === false) {
            return false;
        }
        $res = sprintf(__('The WiFi will open at %s', 'digilan-token'), $res);
        $res = '<p>' . $res . '</p>';
        return $res;
    }

    static function nextDays($today_day)
    {
        $days_ordered = array(0, 1, 2, 3, 4, 5, 6);
        $a = array_slice($days_ordered, $today_day);
        if (empty($a)) return false;
        $b = array_slice($days_ordered, 0, $today_day);
        return array_merge($a, $b);
    }

    static function nextClosingDay($closed_time_period,$x)
    {
        for ($index = 0; $index < 6; $index++)
        {
            $id = self::getNextDay(self::nextDays($x)[$index]);
            if ($closed_time_period[$id][0][0] !== '00:00' || $closed_time_period[$id][0][1] != '24:00'){
                 return $id;
            }
        }
        return -1;
    }

    static function getNextDay($day)
    {
        $day++;
        if ($day == 7) return 0;
        return $day;
    }

    public static function getNextOpeningDate($closed_time_period, $next)
    {
        $today = $next[0];
        $current_range = $next[1];
        // basic case
        if ($closed_time_period[$today][$current_range][1] !== '24:00'){
            $now = new DateTime(current_time('mysql'));
            $closed_date = new DateTime(current_time('mysql'));
            $closed_time = preg_split('/:/', $closed_time_period[$today][$current_range][1]);
            $closed_time_hour = $closed_time[0];
            $closed_time_minute = $closed_time[1];
            $closed_date->setTime($closed_time_hour,$closed_time_minute);
            $duration = $closed_date->diff($now);
            if (false === $duration) {
                wp_die('failed to get difference between 2 dates','fatal');
            }
            return $duration->format('0 %H:%i');
        }
        // closed all day but is it openned all day?
        $next_day = self::getNextDay($today);
        if (false == $closed_time_period[$next_day][0][0]){
            return 'tomorrow';
        }
        $found = self::nextClosingDay($closed_time_period, $today);
        if (-1 == $found){
            return 'closed';
        }
        $days_difference = array_search($found, self::nextDays($today));
        if ($closed_time_period[$found][0][0] == '00:00'){
            $closed_time = $closed_time_period[$found][0][1];
            return sprintf('%d %s', $days_difference, $closed_time);
        } else {
            return sprintf('%d 00:00', $days_difference);
        }
    }

    static function verifySchedule($schedule)
    {
        $now = new DateTime(current_time('mysql'));
        $day_str = $now->format('l');
        $day = date('N', strtotime($day_str)) - 1;
        $start = new DateTime(current_time('mysql'));
        $end = new DateTime(current_time('mysql'));
        foreach ($schedule[$day] as $range_index=>$range) {
            $start_time = preg_split('/:/', $range[0]);
            $end_time = preg_split('/:/', $range[1]);
            $start_h = $start_time[0];
            $start_m = $start_time[1];
            $end_h = $end_time[0];
            $end_m = $end_time[1];
            $start->setTime($start_h, $start_m);
            $end->setTime($end_h, $end_m);
            if ($now > $start && $now < $end) {
                return array($day, $range_index);
            }
        }
        return false;
    }

    public static function isWifiClosed($session_id)
    {
        if (self::isFromCitybox()) { #TODO local diff here
            return false;
        }
        $aps = self::$settings->get('access-points');
        $keys = array_keys($aps);
        if (empty($session_id)) {
            # direct portal access : can be used for testing
            if (isset($_GET['mac']) && preg_match('/[0-9a-f:]{17}/', $_GET['mac'])) {
                $query_source_access_point = array_search($_GET['mac'], array_column($aps, 'mac'));
                if ($query_source_access_point === FALSE) {
                    error_log($_GET['mac'] . ' is not a AP');
                    return false;
                }
                $idx = $keys[$query_source_access_point];
                $query_source_access_point = $aps[$idx];
                $schedule = $query_source_access_point['schedule'];
                $schedule = json_decode($schedule, true);
                return self::verifySchedule($schedule);
            }
            if (isset($_GET['hotspot'])) {
                $idx = intval($_GET['hotspot']);
                asort($keys);
                $idx = $keys[$idx];
                $query_source_access_point = $aps[$idx];
                error_log($idx . ' chosen as ' . $query_source_access_point['mac']);
                $schedule = $query_source_access_point['schedule'];
                $schedule = json_decode($schedule, true);
                return self::verifySchedule($schedule);
            }
            return isset($_GET['close']);
        }
        $mac = DigilanTokenConnection::get_ap_from_sid($session_id);
        if ($mac === false) {
            wp_die('<center style="color: red;">156941</center>', 'fatal');
        }
        $mac = DigilanTokenSanitize::int_to_mac($mac);
        if ($mac === false) {
            wp_die('<center style="color: red;">156942</center>', 'fatal');
        }

        $query_source_access_point = array_search($mac, array_column($aps, 'mac'));
        if ($query_source_access_point === FALSE) {
            wp_die('<center style="color: red;">13259</center>', 'fatal');
        } else {
            $idx = $keys[$query_source_access_point];
            $query_source_access_point = $aps[$idx];
        }
        $schedule = $query_source_access_point['schedule'];
        $schedule = json_decode($schedule, true);

        if ($schedule == NULL) {
            wp_die('<center style="color: red;">13258</center>', 'fatal');
        }
        return self::verifySchedule($schedule);
    }

    public static function speedtest_shortcode()
    {
        wp_enqueue_script('dlt-speedtest-script', 'https://unpkg.com/city_speed/speedtest.js', array('jquery'));
        wp_enqueue_style('dlt-speedtest-style', 'https://unpkg.com/city_speed/speedtest.css');
        ob_start("html_code_for_speedtest");
        ?>
        <div id="speedtest">
            <h1><?php _e('Test the speed of your internet line', 'digilan-token') ?></h1>
            <div class="testGroup">
                <div class="testArea">
                    <div class="testName"><?php _e('Download', 'digilan-token'); ?></div>
                    <div id="download" class="meterText"></div>
                    <div class="unit"><?php _e('Mbps', 'digilan-token'); ?></div>
                </div>
                <div class="testArea">
                    <div class="testName"><?php _e('Upload', 'digilan-token');?></div>
                    <div id="upload" class="meterText"></div>
                    <div class="unit"><?php _e('Mbps', 'digilan-token'); ?></div>
                </div>
            </div>
            <div class="testGroup">
                <div class="testArea">
                    <div class="testName"><?php _e('Ping', 'digilan-token'); ?></div>
                    <div id="ping" class="meterText"></div>
                    <div class="unit"><?php _e('ms', 'digilan-token'); ?></div>
                </div>
                <div class="testArea">
                    <div class="testName"><?php _e('Jitter', 'digilan-token'); ?></div>
                    <div id="jitter" class="meterText"></div>
                    <div class="unit"><?php _e('ms', 'digilan-token'); ?></div>
                </div>
            </div>
            <p><?php _e('IP Address', 'digilan-token'); ?>: <span id="ip"></span></p>
            <div id="launch_test" type="submit">
            </div>
            <div id="result">
                <h2><?php _e('Your connection is', 'digilan-token'); ?>: </h2>
                <h3 id="good_connection"><?php _e('good', 'digilan-token'); ?> !</h3>
                <h3 id="medium_connection"><?php _e('medium', 'digilan-token'); ?> !</h3>
                <h3 id="bad_connection"><?php _e('bad', 'digilan-token'); ?> !</h3>
                <p><?php _e('We suggest you an offer adapted to your needs', 'digilan-token'); ?>: <a id="offer_link"><?php _e('on this link', 'digilan-token'); ?>.</a></p>
            </div>
        </div>
        <?php
        ob_end_flush();
    }

    public static function widgetShortcode($atts)
    {
        if (!is_array($atts)) {
            $atts = array();
        }
        $atts = array_merge(array(
            'google' => '',
            'twitter' => '',
            'facebook' => '',
            'heading' => false,
            'style' => 'default',
            'redirect' => false,
            'color' => '#000000',
            'fontsize' => 16
        ), $atts);

        $providersIn = array();
        foreach (self::$providers as $provider) {
            if ($provider->getState() == 'configured') {
                $provider_id = $provider->getId();
                if ($atts[$provider_id] == 1) {
                    $providersIn[$provider_id] = self::$providers[$provider_id];
                }
            }
        }

        if (count($providersIn) == 0) {
            return _e('No authentication provider activated.', 'digilan-token');
        }
        $now = current_time('mysql');
        $sid = DigilanTokenSanitize::sanitize_get('session_id');
        $mac = DigilanTokenConnection::get_ap_from_sid($sid);
        $mac = DigilanTokenSanitize::int_to_mac($mac);
        $access_points = self::$settings->get('access-points');
        $keys = array_keys($access_points);
        $query_source_access_point = array_search($mac, array_column($access_points, 'mac'));
        $idx = $keys[$query_source_access_point];
        $access_point = $access_points[$idx];
        if (self::isFromCitybox()) {
            if ($mac) {
                $next = self::isWifiClosed($sid);
            } else {
                $router_schedule = self::$settings->get('schedule_router');
                $router_schedule = json_decode($router_schedule, true);
                $next = self::verifySchedule($router_schedule);
            }
        } else {
            $next = self::isWifiClosed($sid);
        }
        if ($next) {
            $closed_time_period = $access_point['schedule'];
            if (empty($sid)) {
                if (isset($_GET['mac']) && preg_match('/[0-9a-f:]{17}/', $_GET['mac'])) {
                    $query_source_access_point = array_search($_GET['mac'], array_column($access_points, 'mac'));
                    if ($query_source_access_point === FALSE) {
                        error_log($_GET['mac'] . ' is not a AP');
                        return false;
                    }
                    $idx = $keys[$query_source_access_point];
                    $query_source_access_point = $access_points[$idx];
                    $closed_time_period = $query_source_access_point['schedule'];
                }
            }
            if (self::isFromCitybox()) {
                if ($mac) {
                    $closed_time_period = json_decode($closed_time_period, true);
                    $next_opening_date = self::getNextOpeningDate($closed_time_period, $next);
                } else {
                    $next_opening_date = self::getNextOpeningDate($router_schedule, $next);
                }
            } else {
                $closed_time_period = json_decode($closed_time_period, true);
                $next_opening_date = self::getNextOpeningDate($closed_time_period, $next);
            }
            $msg = __('Wifi will be available ', 'digilan-token');
            if ($next_opening_date === 'closed') {
                $msg = __('Wifi is currently closed for an undefined period of time', 'digilan-token');
            } elseif ($next_opening_date === 'tomorrow') {
                $msg = __('Wifi will be opened tomorrow', 'digilan-token');
            } else {
                $digilan_duration_data = array(
                   'duration' => $next_opening_date,
                   'locale' => substr(get_locale(), 0, 2)
                );
                wp_enqueue_script('moment', plugins_url('/js/lib/moment.js', DLT_PLUGIN_BASENAME));
                wp_enqueue_script('moment-with-locales', plugins_url('/js/lib/moment-with-locales.js', DLT_PLUGIN_BASENAME));
                wp_register_script('digilan-duration', plugins_url('/js/digilan-duration.js', __FILE__), array('jquery'));
                wp_enqueue_script('digilan-duration');
                wp_localize_script('digilan-duration', 'digilan_duration', $digilan_duration_data);
            }
            return '<center><div class="dlt-container"><p id="digilan-token-closed-message">' . $msg . '</p></div></center>';
        }
        return self::renderContainerAndTitleWithButtons($atts['heading'], $atts['style'], $providersIn, $atts['redirect'], $atts['color'], $atts['fontsize']);
    }

    private static function renderContainerAndTitleWithButtons($heading = false, $style = 'default', $providersIn, $redirect_to = false, $textcolor = null, $textsize = null)
    {
        if (!isset(self::$styles[$style])) {
            $style = 'default';
        }

        if (!count($providersIn)) {
            return '';
        }
        $buttons = '';
        foreach ($providersIn as $provider) {
            if ($provider == null) {
                $buttons .= '';
                continue;
            }
            $buttons .= $provider->getConnectButton($style, $redirect_to);
        }

        if (!empty($heading)) {
            $heading = '<h2>' . $heading . '</h2>';
        } else {
            $heading = '';
        }

        $gtu_link = esc_url(get_permalink(get_option('wp_page_for_privacy_policy')));
        $text_below = __('I accept the ', 'digilan-token') . '<a style="color:' . $textcolor . '" href="' . $gtu_link . '">' . __('terms and conditions.', 'digilan-token') . '</a>';
        $ret = '<center><div class="dlt-container ' . self::$styles[$style]['container'] . '">' . $heading . $buttons . '</div>';
        $ret .= '<div id="dlt-gtu" style="color:' . $textcolor . ';font-size: ' . $textsize . 'px; text-shadow: 1px 1px #000000;"><input type="checkbox" id="dlt-tos" unchecked>' . $text_below . '</div></center>';
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script('dlt-terms', plugins_url('/js/terms-and-conditions.js', DLT_PLUGIN_BASENAME), array('jquery'));
        return $ret;
    }

    public static function getPortalData()
    {
        if (!current_user_can('administrator') && !is_admin()) {
            $response = array('response' => 401);
            _default_wp_die_handler('Unauthorized', '', $response);
        }
        $access_points = self::$settings->get('access-points');
        $portal_page = self::$settings->get('portal-page');
        $timeout = self::$settings->get('timeout');
        $landing_page = self::$settings->get('landing-page');
        $data = array(
            'portal-page' => $portal_page,
            'timeout' => $timeout,
            'landing-page' => $landing_page,
            'access-points' => $access_points
        );
        if (self::isFromCitybox()) {
            $data['schedule-router'] = self::$settings->get('schedule_router');
        }
        wp_send_json($data, 200);
    }

    public static function getLoginUrl($scheme = null)
    {
        return site_url('wp-login.php', $scheme);
    }

    public static function getRegisterUrl()
    {
        return wp_registration_url();
    }

    public static function isAllowedRedirectUrl($url)
    {
        $loginUrl = self::getLoginUrl();

        // If the currentUrl is the loginUrl, then we should not return it for redirects
        if (strpos($url, $loginUrl) === 0) {
            return false;
        }

        $loginUrl2 = site_url('wp-login.php');

        // If the currentUrl is the loginUrl, then we should not return it for redirects
        if ($loginUrl2 !== $loginUrl && strpos($url, $loginUrl2) === 0) {
            return false;
        }

        $registerUrl = wp_registration_url();
        // If the currentUrl is the registerUrl, then we should not return it for redirects
        if (strpos($url, $registerUrl) === 0) {
            return false;
        }

        return true;
    }

    public static function delete_user($user_id)
    {
        /** @var $wpdb WPDB */
        global $wpdb, $blog_id;
        $version = get_option('digilan_token_version');
        $wpdb->delete($wpdb->prefix . 'digilan_token_social_users_' . $version, array(
            'ID' => $user_id
        ), array(
            '%d'
        ));
        $query = 'SELECT user_email FROM ' . $wpdb->prefix . 'users WHERE ID=%s';
        $query = $wpdb->prepare($query, $user_id);
        $social_id = $wpdb->get_var($query);
        if (empty($social_id)) {
            $query = 'SELECT user_login FROM ' . $wpdb->prefix . 'users WHERE ID=%s';
            $query = $wpdb->prepare($query, $user_id);
            $social_id = $wpdb->get_var($query);
        }
        if (empty($social_id)) {
            $query = 'SELECT display_name FROM ' . $wpdb->prefix . 'users WHERE ID=%s';
            $query = $wpdb->prepare($query, $user_id);
            $social_id = $wpdb->get_var($query);
        }
        DigilanTokenUser::forget_me($social_id);

        $attachment_id = get_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
        if (wp_attachment_is_image($attachment_id)) {
            wp_delete_attachment($attachment_id, true);
        }
    }

    public static function remove_admin_bar()
    {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    /**
     * Logs who logged in and how.
     *
     * @param
     *            $username
     */
    public static function authenticate_ap_user_on_wp($username = '')
    {
        $queries = array();
        if (!empty($username)) {
            $user = get_user_by('login', $username);
            $social_id = $user->user_email;
        }
        $parsed_URL = parse_url(wp_get_referer(), PHP_URL_QUERY);
        parse_str($parsed_URL, $queries);
        $provider = 'login and password';
        if (!empty($queries['loginSocial'])) {
            $provider = $queries['loginSocial'];
        }
        if (!empty(DigilanTokenSanitize::sanitize_get('loginSocial'))) {
            $provider = DigilanTokenSanitize::sanitize_get('loginSocial');
        }

        switch ($provider) {
            case 'twitter':
                if (!empty($queries['oauth_token'])) {
                    $oauth_token = $queries['oauth_token'];
                }
                if (!empty(DigilanTokenSanitize::sanitize_get('oauth_token'))) {
                    $oauth_token = DigilanTokenSanitize::sanitize_get('oauth_token');
                }
                $transient_name = 'digilan_token_twitter_oauth_' . $oauth_token;
                $state = get_transient($transient_name);
                $sid = substr($state, 0, 32);
                $mac = substr($state, 32, 48);
                $social_id = $user->user_login;
                break;
            case 'google':
                if (!empty($queries['state'])) {
                    $state = $queries['state'];
                }
                if (!empty(DigilanTokenSanitize::sanitize_get('state'))) {
                    $state = DigilanTokenSanitize::sanitize_get('state');
                }
                $sid = substr($state, 0, 32);
                $mac = substr($state, 32, 48);
                break;
            case 'facebook':
                if (!empty($queries['state'])) {
                    $state = $queries['state'];
                }
                if (!empty(DigilanTokenSanitize::sanitize_get('state'))) {
                    $state = DigilanTokenSanitize::sanitize_get('state');
                }
                $sid = substr($state, 0, 32);
                $mac = substr($state, 32, 48);
                $social_id = $user->first_name . ' ' . $user->last_name;
                break;
            case 'transparent':
                if (isset($queries['session_id']) || DigilanTokenSanitize::sanitize_get('session_id') != null) {
                    if (isset($queries['session_id'])) {
                        $sid = $queries['session_id'];
                    } elseif (DigilanTokenSanitize::sanitize_get('session_id') != null) {
                        $sid = DigilanTokenSanitize::sanitize_get('session_id');
                        if ($sid == false) {
                            _default_wp_die_handler('Invalid session_id');
                        }
                    } else {
                        $sid = '';
                    }
                    if (isset($queries['mac'])) {
                        $mac = $queries['mac'];
                    } elseif (DigilanTokenSanitize::sanitize_get('mac') != null) {
                        $mac = DigilanTokenSanitize::sanitize_get('mac');
                        if ($mac == false) {
                            _default_wp_die_handler('Invalid user mac');
                        }
                    } else {
                        $mac = '';
                    }
                }
                $social_id = 'N/A';
                break;
            default:
                if (isset($queries['session_id']) || DigilanTokenSanitize::sanitize_get('session_id') != null) {
                    if (isset($queries['session_id'])) {
                        $sid = $queries['session_id'];
                    } elseif (DigilanTokenSanitize::sanitize_get('session_id') != null) {
                        $sid = DigilanTokenSanitize::sanitize_get('session_id');
                        if ($sid == false) {
                            _default_wp_die_handler('Invalid session_id');
                        }
                    } else {
                        $sid = '';
                    }
                    if (isset($queries['mac'])) {
                        $mac = $queries['mac'];
                    } elseif (DigilanTokenSanitize::sanitize_get('mac') != null) {
                        $mac = DigilanTokenSanitize::sanitize_get('mac');
                        if ($mac == false) {
                            _default_wp_die_handler('Invalid user mac');
                        }
                    } else {
                        $mac = '';
                    }
                }
                break;
        }
        $re = '/^[a-f0-9]{32}$/';
        if (preg_match($re, $sid) != 1) {
            error_log('Invalid session id = ' . $sid);
            return false;
        }
        $re = '/^[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}$/';
        if (preg_match($re, $mac) != 1) {
            error_log('Invalid user mac = ' . $mac);
            return false;
        }
        error_log($social_id . ' has logged in with ' . $provider);
        $user_id = DigilanTokenUser::select_user_id($mac, $social_id);
        if ($user_id == false) {
            DigilanTokenUser::create_ap_user($mac, $social_id);
            $user_id = DigilanTokenUser::select_user_id($mac, $social_id);
        }
        $update = DigilanTokenUser::validate_user_on_wp($sid, $provider, $user_id);
        if ($update) {
            DigilanTokenConnection::redirect_to_access_point($sid);
        }
    }

    public static function getDigilanVersion()
    {
        status_header( 200 );
        echo self::$digilan_version;
        die;
    }

    /*
     * A function is required in 'wp_die_handler' hook.
     */
    public static function change_wp_die()
    {
        return 'DigilanToken::remove_default_css_on_wp_die';
    }

    public static function remove_default_css_on_wp_die($message, $title = '', $args = array())
    {
        echo $title;
        echo $message;
        exit();
    }

    public static function create_session($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'create'))
            return;
        DigilanTokenConnection::initialize_new_connection();
    }

    public static function validate_connection($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'validate'))
            return;
        DigilanTokenConnection::validate_user_connection();
    }

    public static function get_configuration($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'configure'))
            return;
        DigilanTokenActivator::get_ap_settings();
    }

    public static function store_dns($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'write'))
            return;
        DigilanTokenLogs::store_dns_logs();
    }

    public static function store_ssid($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'add'))
            return;
        DigilanTokenActivator::register_ap();
    }

    public static function reauth_user($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'reauth'))
            return;
        DigilanTokenConnection::reauthenticate_user();
    }

    public static function archive_sessions($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'archive'))
            return;
        DigilanTokenConnection::archive_old_sessions();
    }

    public static function get_digilan_version($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'version'))
            return;
        self::getDigilanVersion();
    }

    public static function hide_admin_bar_preview($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'hide_bar'))
            return;
    }

    public static function get_digilan_data($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'data'))
            return;
        self::getPortalData();
    }

    public static function delete_access_point($wp)
    {
        if (false == array_key_exists('digilan-token-action', $wp->query_vars))
            return;
        if (false == ($wp->query_vars['digilan-token-action'] == 'del'))
            return;
        DigilanTokenActivator::remove_ap();
    }

    public static function digilantoken_add_query_vars($vars)
    {
        $vars[] = 'digilan-token-action';
        return $vars;
    }


    public static function init_token_action()
    {
        add_filter('query_vars', 'DigilanToken::digilantoken_add_query_vars');
        if (DigilanTokenSanitize::sanitize_get('digilan-token-action') != null) {
            $action = DigilanTokenSanitize::sanitize_get('digilan-token-action');
            $re = '/^(create|validate|configure|write|add|del|reauth|archive|version|data)$/';
            if (preg_match($re, $action)) {
                add_filter('wp_die_handler', 'DigilanToken::change_wp_die');
            }
            if ($action == 'hide_bar') {
                add_filter('show_admin_bar', '__return_false');
            }
        }
        add_action('parse_request', 'DigilanToken::validate_connection');
        add_action('parse_request', 'DigilanToken::create_session');
        add_action('parse_request', 'DigilanToken::get_configuration');
        add_action('parse_request', 'DigilanToken::store_dns');
        add_action('parse_request', 'DigilanToken::store_ssid');
        add_action('parse_request', 'DigilanToken::reauth_user');
        add_action('parse_request', 'DigilanToken::archive_sessions');
        add_action('parse_request', 'DigilanToken::get_digilan_version');
        add_action('parse_request', 'DigilanToken::get_digilan_data');
        add_action('parse_request', 'DigilanToken::delete_access_point');
    }

    public static function set_login_hook()
    {
        add_action('wp_login', 'DigilanToken::authenticate_ap_user_on_wp');
        add_action('after_setup_theme', 'DigilanToken::remove_admin_bar');
    }
}

DigilanToken::init();
DigilanToken::init_token_action();
DigilanToken::set_login_hook();
