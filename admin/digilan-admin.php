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
define('DLT_ADMIN_PATH', __FILE__);

class DigilanTokenAdmin
{

    public static function init()
    {
        add_action('admin_menu', 'DigilanTokenAdmin::admin_menu', 1);
        add_action('admin_init', 'DigilanTokenAdmin::admin_init');

        add_filter('plugin_action_links', 'DigilanTokenAdmin::plugin_action_links', 10, 2);

        add_filter('dlt_update_settings_validate_digilan-token_social_login', 'DigilanTokenAdmin::validateSettings', 10, 2);
    }

    public static function getAdminBaseUrl()
    {
        $url_query = array('page' => 'digilan-token-plugin');
        $admin_url = admin_url('admin.php');
        return add_query_arg($url_query, $admin_url);
    }

    public static function getAdminUrl($view)
    {
        $url_query = array('page' => 'digilan-token-plugin');
        if ($view) {
            $url_query['view'] = $view;
        }
        $base_admin_url = self::getAdminBaseUrl();
        return add_query_arg($url_query, $base_admin_url);
    }

    public static function admin_menu()
    {
        $menu = add_menu_page('Monsieur WiFi', 'Monsieur WiFi', 'manage_options', 'digilan-token-plugin', array(
            'DigilanTokenAdmin',
            'display_admin'
        ), '', 2);
        add_action('admin_print_styles-' . $menu, 'DigilanTokenAdmin::admin_css');
    }

    public static function admin_css()
    {
        wp_enqueue_style('dlt-admin-stylesheet', plugins_url('/style.css', DLT_ADMIN_PATH));
    }

    public static function display_admin()
    {
        $view = DigilanTokenSanitize::sanitize_request('view');
        if (!$view)
            $view = '';

        if (substr($view, 0, 9) == 'provider-') {
            $providerID = substr($view, 9);
            if (isset(DigilanToken::$providers[$providerID])) {
                self::display_admin_area('provider', $providerID);

                return;
            }
        }
        switch ($view) {
            case 'fix-redirect-uri':
                self::display_admin_area('fix-redirect-uri');
                break;
            case 'assistant':
                self::display_admin_area('assistant');
                break;
            case 'test-connection':
                self::display_admin_area('test-connection');
                break;
            case 'providers':
                self::display_admin_area('providers');
                break;
            case 'logs':
                self::display_admin_area('logs');
                break;
            case 'connections':
                self::display_admin_area('connections');
                break;
            case 'settings':
                self::display_admin_area('settings');
                break;
            default:
                self::display_admin_area('access-point');
                break;
        }
    }

    /**
     *
     * @param string $view
     * @param string $currentProvider
     */
    private static function display_admin_area($view, $currentProvider = '')
    {
        if (empty($currentProvider)) {
            include(dirname(__FILE__) . '/templates/header.php');
            include(dirname(__FILE__) . '/templates/menu.php');

            \DLT\Notices::displayNotices();

            wp_enqueue_script('city_qrcode', 'https://unpkg.com/city_qrcode@1.1.1/qr_code.min.js', null, null, true);
            /** @var string $view */
            include(dirname(__FILE__) . '/templates/' . $view . '.php');
            include(dirname(__FILE__) . '/templates/footer.php');
        } else {
            include(dirname(__FILE__) . '/templates/' . $view . '.php');
        }
    }

    public static function get_timeout($timeout)
    {
        $timeout = (int) $timeout;
        return $timeout / 60;
    }

    public static function admin_init()
    {
        $page = DigilanTokenSanitize::sanitize_get('page');
        $view = DigilanTokenSanitize::sanitize_get('view');
        if (current_user_can('manage_options')) {
            if (!$page || $page != 'digilan-token-plugin' || !$view || $view != 'fix-redirect-uri') {
                add_action('admin_notices', 'DigilanTokenAdmin::show_oauth_uri_notice');
            }
        }

        if (!$page && $page == 'digilan-token-plugin') {
            if (!$view && $view == 'update_oauth_redirect_url') {
                if (check_admin_referer('digilan-token-plugin_update_oauth_redirect_url')) {
                    foreach (DigilanToken::$allowedProviders as $provider) {
                        if ($provider->getState() == 'configured') {
                            $provider->updateOauthRedirectUrl();
                        }
                    }
                }
                wp_redirect(self::getAdminBaseUrl());
                exit();
            }
        }
        add_action('admin_post_digilan-token-plugin', 'DigilanTokenAdmin::save_form_data');
        add_action('wp_ajax_digilan-token-plugin', 'DigilanTokenAdmin::ajax_save_form_data');
        add_action('wp_ajax_digilan-token-cityscope', 'DigilanTokenAdmin::test_url_backend');

        add_action('admin_enqueue_scripts', 'DigilanTokenAdmin::admin_enqueue_scripts');

        if (!function_exists('json_decode')) {
            add_settings_error('digilan-token-social', 'settings_updated', printf(__('%s needs json_decode function.', 'digilan-token'), 'Digilan Token') . ' ' . __('Please contact your server administrator and ask for solution!', 'digilan-token'), 'error');
        }
    }

    public static function save_form_data()
    {
        if (current_user_can('manage_options') && check_admin_referer('digilan-token-plugin')) {
            foreach ($_POST as $k => $v) {
                $k = sanitize_key($k);
                if (is_string($v)) {
                    $_POST[$k] = stripslashes($v);
                }
            }

            $view = DigilanTokenSanitize::sanitize_request('view');
            if (substr($view, 0, 9) == 'provider-') {
                $providerID = substr($view, 9);
                if (isset(DigilanToken::$providers[$providerID])) {
                    DigilanToken::$providers[$providerID]->settings->update($_POST);
                    \DLT\Notices::addSuccess(__('Settings saved.', 'digilan-token'));
                    $subview = DigilanTokenSanitize::sanitize_post('subview');
                    if (!$subview)
                        $subview = '';
                    $page = DigilanToken::$providers[$providerID]->getAdmin()->getUrl($subview);
                    wp_redirect($page);
                    exit();
                }
            } else if ($view == 'access-point') {
                $dlt_code = DigilanTokenSanitize::sanitize_post('digilan-token-code');
                if ($dlt_code) {
                    self::activate_plugin_api($dlt_code);
                }
                if (isset($_POST['digilan-token-activator'])) {
                    self::resend_code();
                }
                // Save settings
                if (isset($_POST['digilan-token-global'])) {
                    $portal_page = DigilanTokenSanitize::sanitize_post('digilan-token-page');
                    $timeout = DigilanTokenSanitize::sanitize_post('digilan-token-timeout');
                    $landing_page = DigilanTokenSanitize::sanitize_post('digilan-token-lpage');
                    $schedule = DigilanTokenSanitize::sanitize_post('digilan-token-schedule-router');
                    if (false === $portal_page) {
                        \DLT\Notices::addError(__('Please select a page for your portal.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    if (false === $timeout) {
                        \DLT\Notices::addError(__('Please set a timeout.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    $timeout = (int) $timeout;
                    $timeout *= 60;
                    if (false === $landing_page) {
                        \DLT\Notices::addError(__('Please set a landing page.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    self::save_global_settings($portal_page, $timeout, $landing_page, $schedule);
                    if (method_exists('\Elementor\Compatibility','clear_3rd_party_cache')) {
                        \Elementor\Compatibility::clear_3rd_party_cache();
                    }
                    \DLT\Notices::addSuccess(__('Settings saved. Please wait about an hour to see your changes applied on your access point', 'digilan-token'));
                    wp_redirect(self::getAdminUrl('access-point'));
                    exit();
                }
                if (isset($_POST['digilan-token-access-point-settings'])) {
                    $hostname = DigilanTokenSanitize::sanitize_post('digilan-token-hostname');
                    $ssid = DigilanTokenSanitize::sanitize_post('digilan-token-ssid');
                    $country_code = DigilanTokenSanitize::sanitize_post('digilan-token-country-code');
                    $intervals = DigilanTokenSanitize::sanitize_post('digilan-token-schedule');
                    if (false === $hostname) {
                        \DLT\Notices::addError(__('Please choose a hostname.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    if (false === $ssid) {
                        \DLT\Notices::addError(__('Please set your SSID.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    if (false == $country_code) {
                        \DLT\Notices::addError(__('Please set a country code.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    $update_all_access_point = isset($_POST['digilan-token-select-all']);
                    if ($update_all_access_point) {
                        $access_points = DigilanToken::$settings->get('access-points');
                        foreach ($access_points as $h => $access_point) {
                            self::save_ap_settings($h, $ssid, $country_code, $intervals);
                        }
                        if (method_exists('\Elementor\Compatibility','clear_3rd_party_cache')) {
                            \Elementor\Compatibility::clear_3rd_party_cache();
                        }
                        \DLT\Notices::addSuccess(__('Settings saved. All access points have been updated', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    self::validate_ap_settings($hostname, $ssid, $country_code, $intervals);
                }
                if (isset($_POST['digilan-token-single-access-point-settings'])) {
                    $hostname_array = DigilanTokenSanitize::sanitize_post_array('digilan-token-hostname');
                    $portal_page = DigilanTokenSanitize::sanitize_post('digilan-token-page');
                    $landing_page = DigilanTokenSanitize::sanitize_post('digilan-token-lpage');

                    if (false === $portal_page) {
                        \DLT\Notices::addError(__('Please select a page for your portal.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    if (false === $landing_page) {
                        \DLT\Notices::addError(__('Please set a landing page.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('access-point'));
                        exit();
                    }
                    foreach ($hostname_array as $hostname) {
                        if (false === $hostname) {
                            \DLT\Notices::addError(__('Please choose a hostname.', 'digilan-token'));
                            wp_redirect(self::getAdminUrl('access-point'));
                            exit();
                        }
                    }

                    self::save_single_ap_settings($hostname_array,$portal_page,$landing_page);
                    \DLT\Notices::addSuccess(__('Settings saved. Access point have been updated', 'digilan-token'));
                    wp_redirect(self::getAdminUrl('access-point'));
                    exit();
                }

            } else if ($view == 'logs') {
                if (isset($_POST['digilan-download'])) {
                    self::download_csv_logs();
                }
            } else if ($view == 'connections') {
                if (isset($_POST['digilan-mail-download'])) {
                    $start = DigilanTokenSanitize::sanitize_post('dlt-start-date');
                    $end = DigilanTokenSanitize::sanitize_post('dlt-end-date');
                    if (!$start) {
                        \DLT\Notices::addError(__('Invalid start date.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('connections'));
                        exit();
                    }
                    if (!$end) {
                        \DLT\Notices::addError(__('Invalid end date.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('connections'));
                        exit();
                    }
                    $sd = new DateTime($start);
                    $ed = new DateTime($end);
                    if ($sd > $ed) {
                        \DLT\Notices::addError(__('Start date must be before end date.', 'digilan-token'));
                        wp_redirect(self::getAdminUrl('connections'));
                        exit();
                    }
                    DigilanTokenConnection::download_mails_csv($start, $end);
                }
            } else if ($view == 'settings') {
                $cityscope_cloud = DigilanTokenSanitize::sanitize_post('cityscope-backend');
                if (false === $cityscope_cloud) {
                    \DLT\Notices::addError(__('Invalid endpoint', 'digilan-token'));
                    wp_redirect(self::getAdminUrl('settings'));
                    exit();
                }
                self::updateCityscopeCloud($cityscope_cloud);
            }
            wp_redirect(self::getAdminBaseUrl());
            exit();
        }
    }

    private static function resend_code()
    {
        $y = DigilanTokenActivator::cityscope_bonjour();
        if ($y) {
            \DLT\Notices::addSuccess(__('Code request sent.', 'digilan-token'));
        } else {
            \DLT\Notices::addError(__('Request for code failed.', 'digilan-token'));
        }
        wp_redirect(self::getAdminUrl('access-point'));
        exit();
    }

    private static function activate_plugin_api($code)
    {
        $re = '/^[A-Z0-9]{4}$/';
        if (preg_match($re, $code) != 1) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid code format.', 'digilan-token'), $code));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        $x = self::get_wp_secret($code);
        if ($x) {
            \DLT\Notices::addSuccess(__('Code sent successfully. This plugin is activated.', 'digilan-token'));
        } else {
            \DLT\Notices::addError(__('Invalid code.', 'digilan-token'));
        }
        wp_redirect(self::getAdminUrl('access-point'));
        exit();
    }

    private static function download_csv_logs()
    {
        $start = DigilanTokenSanitize::sanitize_post('digilan-token-start');
        $end = DigilanTokenSanitize::sanitize_post('digilan-token-end');
        if (!$start) {
            \DLT\Notices::addError(__('Invalid start date.', 'digilan-token'));
            wp_redirect(self::getAdminUrl('logs'));
            exit();
        }
        if (!$end) {
            \DLT\Notices::addError(__('Invalid end date.', 'digilan-token'));
            wp_redirect(self::getAdminUrl('logs'));
            exit();
        }
        $start_unix = strtotime($start);
        $end_unix = strtotime($end);
        if (!strtotime($start)) {
            \DLT\Notices::addError(__('Invalid start date.', 'digilan-token'));
            wp_redirect(self::getAdminUrl('logs'));
            exit();
        }
        if (!strtotime($end)) {
            \DLT\Notices::addError(__('Invalid end date.', 'digilan-token'));
            wp_redirect(self::getAdminUrl('logs'));
            exit();
        }
        if ($end_unix < $start_unix) {
            \DLT\Notices::addError(__('Start date must be before end date.', 'digilan-token'));
            wp_redirect(self::getAdminUrl('logs'));
            exit();
        }
        DigilanTokenLogs::generate_csv($start, $end);
        \DLT\Notices::addSuccess(__('Log file successfully generated.', 'digilan-token'));
        wp_redirect(self::getAdminUrl('logs'));
        exit();
    }

    private static function updateCityscopeCloud($cityscopeCloud)
    {
        if (get_option('cityscope_backend') !== $cityscopeCloud) {
            $update = update_option('cityscope_backend', $cityscopeCloud);
            if (false === $update) {
                error_log('updateCityscopeCloud: failed to update cityscope_backend');
                \DLT\Notices::addError(__('Failed to update Cityscope Cloud.', 'digilan-token'));
                wp_redirect(self::getAdminUrl('settings'));
                exit();
            }
        }
        \DLT\Notices::addSuccess(__('Settings saved.', 'digilan-token'));
        wp_redirect(self::getAdminUrl('settings'));
        exit();
    }

    private static function save_router_schedule($schedule)
    {
        if (null == json_decode($schedule) || false == json_decode($schedule)) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid timetable data.'), $schedule));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        $settings = DigilanToken::$settings;
        $data = array(
            'schedule_router' => $schedule
        );
        $settings->update($data);
        \DLT\Notices::addSuccess(__('Settings saved.', 'digilan-token'));
        wp_redirect(self::getAdminUrl('access-point'));
        exit();
    }

    private static function save_global_settings($portal_page, $timeout, $landing_page, $schedule)
    {
        if (esc_url_raw($landing_page) != $landing_page) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid landing page URL.'), $landing_page));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        if (!is_int($timeout)) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid timeout.'), $timeout));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        $settings = DigilanToken::$settings;
        $data = array(
            'timeout' => $timeout,
            'landing-page' => $landing_page,
            'portal-page' => $portal_page
        );
        if (DigilanToken::isFromCitybox()) {
            if (null == json_decode($schedule) || false == json_decode($schedule)) {
                \DLT\Notices::addError(sprintf(__('%s is an invalid timetable data.'), $schedule));
                wp_redirect(self::getAdminUrl('access-point'));
                exit();
            }
            $data = array_merge($data, array(
                'schedule_router' => $schedule
            ));
        }
        $settings->update($data);
    }

    private static function save_single_ap_settings($hostname_array,$portal_page,$landing_page)
    {
        if (esc_url_raw($landing_page) != $landing_page) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid landing page URL.'), $landing_page));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        $settings = DigilanToken::$settings;
        $updated_data = $settings->get('access-points');
        foreach ($hostname_array as $hostname) {
            $inap = array(
                'ssid' => $settings->get('access-points')[$hostname]['ssid'],
                'access' => $settings->get('access-points')[$hostname]['access'],
                'schedule' => $settings->get('access-points')[$hostname]['schedule'],
                'mac' => $settings->get('access-points')[$hostname]['mac'],
                'country_code' => $settings->get('access-points')[$hostname]['country_code'],
                'portal' => $portal_page,
                'landing' => $landing_page, 
            );
            $updated_data[$hostname] = $inap;
        }
        $data = array(
            'access-points' => $updated_data
        );
        DigilanToken::$settings->update($data);
    }

    private static function save_ap_settings($hostname, $ssid, $country_code, $intervals)
    {
        $re = '/^[0-9a-zA-Z][\w\W]{1,32}$/';
        if (preg_match($re, $ssid) != 1) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid SSID.'), $ssid));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        if (null == json_decode($intervals) || false == json_decode($intervals)) {
            \DLT\Notices::addError(sprintf(__('%s is an invalid timetable data.'), $intervals));
            wp_redirect(self::getAdminUrl('access-point'));
            exit();
        }
        $settings = DigilanToken::$settings;
        $inap = array(
            'ssid' => $ssid,
            'access' => $settings->get('access-points')[$hostname]['access'],
            'schedule' => $intervals,
            'mac' => $settings->get('access-points')[$hostname]['mac'],
            'country_code' => $country_code,
            'portal' => $settings->get('access-points')[$hostname]['portal'],
            'landing' => $settings->get('access-points')[$hostname]['landing']
        );
        $updated_data = $settings->get('access-points');
        $updated_data[$hostname] = $inap;
        $data = array(
            'access-points' => $updated_data
        );
        DigilanToken::$settings->update($data);
    }

    private static function validate_ap_settings($hostname, $ssid, $country_code, $intervals)
    {
        self::save_ap_settings($hostname, $ssid, $country_code, $intervals);
        if (method_exists('\Elementor\Compatibility','clear_3rd_party_cache')) {
            \Elementor\Compatibility::clear_3rd_party_cache();
        }
        \DLT\Notices::addSuccess(__('Settings saved. Please wait about an hour to see your changes applied on your access point', 'digilan-token'));
        wp_redirect(self::getAdminUrl('access-point'));
        exit();
    }

    private static function get_wp_secret($code)
    {
        $wp_site_url = urlencode_deep(get_site_url());
        $args = array(
            "code" => $code
        );
        $endpoint = get_option('cityscope_backend');
        // If using /DAP/Portal controller
        if (preg_match('/^http(s):\/\/.*\/2019\/DAP$/', $endpoint) === 1) {
            $endpoint .= "/Portal/Secret";
        } else {
            $endpoint .= "/Secret";
        }
        $url = esc_url_raw(add_query_arg($args, $endpoint . '/' . $wp_site_url));
        $r = wp_remote_get($url);
        if (is_wp_error($r)) {
            throw new Exception($r->get_error_message());
        } else if (wp_remote_retrieve_response_code($r) !== 200) {
            error_log('Could not get secret. Response: ' . wp_remote_retrieve_response_code($r));
            return false;
        }
        $response = json_decode($r["body"]);
        $secret = $response->secret;
        $wifi4eu = $response->wifi4eu;
        if (!get_option("digilan_token_secret")) {
            add_option("digilan_token_secret", $secret);
        } else {
            update_option("digilan_token_secret", $secret);
        }
        if (!get_option("digilan_token_mode")) {
            add_option("digilan_token_mode", 0);
        } else {
            update_option("digilan_token_mode", 0);
        }
        if (!$wifi4eu) {
            return true;
        }
        if (!get_option("digilan_token_wifi4eu")) {
            add_option("digilan_token_wifi4eu", $wifi4eu);
        } else {
            update_option("digilan_token_wifi4eu", $wifi4eu);
        }
        return true;
    }

    public static function ajax_save_form_data()
    {
        check_ajax_referer('digilan-token-plugin');
        if (!current_user_can('manage_options')) {
            return;
        }
        $view = DigilanTokenSanitize::sanitize_post('view');
        if ($view == 'orderProviders') {
            $ordering = DigilanTokenSanitize::sanitize_post('ordering');
            if ($ordering) {
                DigilanToken::$settings->update(array(
                    'ordering' => $ordering
                ));
            }
        }
    }

    public static function test_url_backend()
    {
        check_ajax_referer('digilan-token-cityscope');
        $endpoint = DigilanTokenSanitize::sanitize_post('cityscope-backend');
        $endpoint .= '/version';
        $args = array (
            'timeout' => 3
        );
        $request = wp_remote_get($endpoint, $args);
        if (is_wp_error($request)) {
            error_log('test_url_backend: '.$request->get_error_message());
            wp_die('error', '', 500);
        }
        $code = wp_remote_retrieve_response_code($request);
        if ($code === 200) {
            wp_die();
        } else {
            wp_die('error', '', $code);
        }
    }

    public static function validateSettings($newData, $postedData)
    {
        if (isset($postedData['redirect'])) {
            if (isset($postedData['custom_redirect_enabled']) && $postedData['custom_redirect_enabled'] == '1') {
                $newData['redirect'] = trim(sanitize_text_field($postedData['redirect']));
            } else {
                $newData['redirect'] = '';
            }
        }

        if (isset($postedData['redirect_reg'])) {
            if (isset($postedData['custom_redirect_reg_enabled']) && $postedData['custom_redirect_reg_enabled'] == '1') {
                $newData['redirect_reg'] = trim(sanitize_text_field($postedData['redirect_reg']));
            } else {
                $newData['redirect_reg'] = '';
            }
        }

        foreach ($postedData as $key => $value) {
            switch ($key) {
                case 'debug':
                case 'access-points':
                    $newData[$key] = $value;
                    break;
                case 'portal-page':
                    $newData[$key] = $value;
                    break;
                case 'terms_show':
                case 'terms':
                    $newData[$key] = wp_kses_post($value);
                    break;
                case 'ordering':
                    if (is_array($value)) {
                        $newData[$key] = $value;
                    }
                    break;
                case 'pre-activation':
                case 'schedule_router':
                    $newData[$key] = $value;
                    break;
                case 'landing-page':
                    $newData[$key] = $value;
                    break;
                case 'timeout':
                    $value = (int) $value;
                    if (is_int($value)) {
                        $newData[$key] = $value;
                    } else {
                        $newData[$key] = '';
                    }
                    break;
            }
        }

        return $newData;
    }

    public static function plugin_action_links($links, $file)
    {
        if ($file != DLT_PLUGIN_BASENAME) {
            return $links;
        }
        $settings_link = '<a href="' . esc_url(menu_page_url('digilan-token-plugin', false)) . '">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    public static function admin_enqueue_scripts()
    {
        if ('settings_page_digilan-token-plugin' === get_current_screen()->id) {

            // Since WordPress 4.9
            if (function_exists('wp_enqueue_code_editor')) {
                // Enqueue code editor and settings for manipulating HTML.
                $settings = wp_enqueue_code_editor(array(
                    'type' => 'text/html'
                ));

                // Bail if user disabled CodeMirror.
                if (false === $settings) {
                    return;
                }

                wp_add_inline_script('code-editor', sprintf('jQuery( function() { var settings = %s; jQuery(".digilan-token-html-editor").each(function(i, el){wp.codeEditor.initialize( el, settings);}); } );', wp_json_encode($settings)));

                $settings['codemirror']['readOnly'] = 'nocursor';

                wp_add_inline_script('code-editor', sprintf('jQuery( function() { var settings = %s; jQuery(".digilan-token-html-editor-readonly").each(function(i, el){wp.codeEditor.initialize( el, settings);}); } );', wp_json_encode($settings)));
            }
        }
    }

    public static function show_oauth_uri_notice()
    {
        foreach (DigilanToken::$allowedProviders as $provider) {
            if ($provider->getState() == 'configured') {
                if (!$provider->checkOauthRedirectUrl()) {
                    echo '<div class="error">
                        <p>' . sprintf(__('%s detected that your login url changed. You must update the Oauth redirect URIs in the related social applications.', 'digilan-token'), '<b>Digilan Token</b>') . '</p>
                        <p class="submit"><a href="' . DigilanTokenAdmin::getAdminUrl('fix-redirect-uri') . '" class="button button-primary">' . __('Fix Error', 'digilan-token') . ' - ' . __('Oauth Redirect URI', 'digilan-token') . '</a></p>
                    </div>';
                    break;
                }
            }
        }
    }
}
