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
require_once dirname(__FILE__) . '/digilan-provider-admin.php';
require_once dirname(__FILE__) . '/digilan-provider-dummy.php';
require_once dirname(__FILE__) . '/digilan-social-user.php';

abstract class DigilanTokenSocialProvider extends DigilanTokenSocialProviderDummy
{

    protected $dbID;

    protected $optionKey;

    protected $enabled = false;

    /** @var DigilanTokenSocialAuth */
    protected $client;

    protected $authUserData = array();

    protected $requiredFields = array();

    protected $svg = '';

    protected $sync_fields = array();

    /**
     * DigilanTokenSocialProvider constructor.
     *
     * @param
     *            $defaultSettings
     */
    public function __construct($defaultSettings)
    {
        if (empty($this->dbID)) {
            $this->dbID = $this->id;
        }
        $this->optionKey = 'dlt_' . $this->id;

        do_action('dlt_provider_init', $this);

        $this->sync_fields = apply_filters('dlt_' . $this->getId() . '_sync_fields', $this->sync_fields);

        $extraSettings = apply_filters('dlt_' . $this->getId() . '_extra_settings', array(
            'ask_email' => 'when-empty',
            'ask_user' => 'never',
            'ask_password' => 'never',
            'auto_link' => 'email',
            'disabled_roles' => array(),
            'register_roles' => array(
                'default'
            )
        ));

        $field_names = array_keys($this->getSyncFields());
        foreach ($field_names as $field_name) {

            $extraSettings['sync_fields/fields/' . $field_name . '/enabled'] = 0;
            $extraSettings['sync_fields/fields/' . $field_name . '/meta_key'] = $this->id . '_' . $field_name;
        }

        $this->settings = new DigilanTokenSettings($this->optionKey, array_merge(array(
            'settings_saved' => '0',
            'tested' => '0',
            'custom_default_button' => '',
            'custom_icon_button' => '',
            'login_label' => '',
            'link_label' => '',
            'unlink_label' => '',
            'user_prefix' => '',
            'user_fallback' => '',
            'oauth_redirect_url' => '',
            'terms' => '',

            'sync_fields/link' => 0,
            'sync_fields/login' => 0
        ), $extraSettings, $defaultSettings));

        $this->admin = new DigilanTokenSocialProviderAdmin($this);
    }

    public function getOptionKey()
    {
        return $this->optionKey;
    }

    public function getRawDefaultButton()
    {
        return '<span id="' . $this->id . '-button" class="dlt-button dlt-button-default dlt-button-' . $this->id . '" style="background-color:' . $this->color . ';"><span class="dlt-button-svg-container">' . $this->svg . '</span><span class="dlt-button-label-container">{{label}}</span></span>';
    }

    public function getRawIconButton()
    {
        return '<span class="dlt-button dlt-button-icon dlt-button-' . $this->id . '" style="background-color:' . $this->color . ';"><span class="dlt-button-svg-container">' . $this->svg . '</span></span>';
    }

    public function getDefaultButton($label)
    {
        $button = $this->settings->get('custom_default_button');
        if (!empty($button)) {
            return str_replace('{{label}}', __($label, 'digilan-token'), $button);
        }
        return str_replace('{{label}}', __($label, 'digilan-token'), $this->getRawDefaultButton());
    }

    public function getIconButton()
    {
        $button = $this->settings->get('custom_icon_button');
        if (!empty($button)) {
            return $button;
        }

        return $this->getRawIconButton();
    }

    public function getLoginUrl()
    {
        $args = array(
            'loginSocial' => $this->getId()
        );

        return add_query_arg($args, DigilanToken::getLoginUrl());
    }

    public function getRedirectUri()
    {
        $args = array(
            'loginSocial' => $this->getId()
        );

        return add_query_arg($args, DigilanToken::getLoginUrl());
    }

    public function getRedirectUriForApp()
    {
        return $this->getRedirectUri();
    }

    /**
     * Enable the selected provider.
     *
     * @return bool
     */
    public function enable()
    {
        $this->enabled = true;

        do_action('dlt_' . $this->getId() . '_enabled');

        return true;
    }

    /**
     * Check if provider is verified.
     *
     * @return bool
     */
    public function isTested()
    {
        return !!$this->settings->get('tested');
    }

    public function isConfigured()
    {
        return $this->getState() == 'configured';
    }

    /**
     * Check if login url was changed by another plugin.
     * If it was changed returns true, else false.
     *
     * @return bool
     */
    public function checkOauthRedirectUrl()
    {
        $oauth_redirect_url = $this->settings->get('oauth_redirect_url');
        return empty($oauth_redirect_url) || $oauth_redirect_url == $this->getRedirectUri();
    }

    public function updateOauthRedirectUrl()
    {
        $this->settings->update(array(
            'oauth_redirect_url' => $this->getRedirectUri()
        ));
    }

    /**
     *
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    /**
     * Get the current state of a Provider.
     *
     * @return string
     */
    public function getState()
    {
        $names = array_keys($this->requiredFields);
        foreach ($names as $name) {
            $value = $this->settings->get($name);
            if (empty($value)) {
                return 'not-configured';
            }
        }
        if (!$this->isTested()) {
            return 'not-tested';
        }
        return 'configured';
    }

    /**
     * Authenticate and connect with the provider.
     */
    public function connect()
    {
        try {
            $this->doAuthenticate();
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     *
     * @return DigilanTokenSocialAuth
     */
    protected abstract function getClient();

    public function getTestUrl()
    {
        return $this->getClient()->getTestUrl();
    }

    /**
     *
     * @throws DLTContinuePageRenderException
     */
    protected function doAuthenticate()
    {
        if (!headers_sent()) {
            // All In One WP Security sets a LOCATION header, so we need to remove it to do a successful test.
            if (function_exists('header_remove')) {
                header_remove("LOCATION");
            } else {
                header('LOCATION:', true); // Under PHP 5.3
            }
        }

        if (!$this->isTest()) {
            add_action($this->id . '_login_action_before', array(
                $this,
                'liveConnectBefore'
            ));
            add_action($this->id . '_login_action_redirect', array(
                $this,
                'liveConnectRedirect'
            ));
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'liveConnectGetUserProfile'
            ));

            /**
             * Store the settings for the provider login.
             */
            $display = DigilanTokenSanitize::sanitize_request('display');
            if ($display == 'popup') {
                \DLT\Persistent\Persistent::set($this->id . '_display', 'popup');
            }
        } else { // This is just to verify the settings.
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'testConnectGetUserProfile'
            ));
        }

        do_action($this->id . '_login_action_before', $this);

        $client = $this->getClient();

        $accessTokenData = $this->getAnonymousAccessToken();

        $client->checkError();

        do_action($this->id . '_login_action_redirect', $this);

        /**
         * Check if we have an accessToken and a code.
         * If there is no access token and code it redirects to the Authorization Url.
         */
        if (!$accessTokenData && !$client->hasAuthenticateData()) {

            header('LOCATION: ' . $client->createAuthUrl());
            exit();
        } else {
            /**
             * If the code is OK but there is no access token, authentication is necessary.
             */
            if (!$accessTokenData) {
                $accessTokenData = $client->authenticate();
                $accessTokenData = $this->requestLongLivedToken($accessTokenData);
                /**
                 * store the access token
                 */
                $this->setAnonymousAccessToken($accessTokenData);
            } else {
                $client->setAccessTokenData($accessTokenData);
            }
            /**
             * Retrieves the userinfo trough the REST API and connect with the provider.
             * Redirects to the last location.
             */
            $this->authUserData = $this->getCurrentUserInfo();

            do_action($this->id . '_login_action_get_user_profile', $accessTokenData);
        }
    }

    /**
     * Connect with the selected provider.
     * After a successful login, we no longer need the previous persistent data.
     *
     * @param
     *            $access_token
     *            
     */
    public function liveConnectGetUserProfile($access_token)
    {
        $socialUser = new DigilanTokenSocialUser($this, $access_token);
        $socialUser->liveConnectGetUserProfile();

        $this->deleteLoginPersistentData();
        $this->redirectToLastLocationOther();
    }

    /**
     *
     * Insert the userid into the wp_social_users table,
     * in this way a link is created between user accounts and the providers.
     *
     * @param
     *            $user_id
     * @param
     *            $providerIdentifier
     *            
     *            
     * @return bool
     */
    public function linkUserToProviderIdentifier($user_id, $providerIdentifier)
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        $connectedProviderID = $this->getProviderIdentifierByUserID($user_id);

        if ($connectedProviderID !== null) {
            return $connectedProviderID == $providerIdentifier;
        }

        $wpdb->insert($wpdb->prefix . 'digilan_token_social_users_' . $version, array(
            'ID' => $user_id,
            'type' => $this->dbID,
            'identifier' => $providerIdentifier
        ), array(
            '%d',
            '%s',
            '%s'
        ));

        do_action('dlt_' . $this->getId() . '_link_user', $user_id, $this->getId());

        return true;
    }

    public function getUserIDByProviderIdentifier($identifier)
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        return $wpdb->get_var($wpdb->prepare('SELECT ID FROM `' . $wpdb->prefix . 'digilan_token_social_users_' . $version . '` WHERE type = %s AND identifier = %s', array(
            $this->dbID,
            $identifier
        )));
    }

    protected function getProviderIdentifierByUserID($user_id)
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        return $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'digilan_token_social_users_' . $version . '` WHERE type = %s AND ID = %s', array(
            $this->dbID,
            $user_id
        )));
    }

    /**
     * Delete the link between the user account and the provider.
     *
     * @param
     *            $user_id
     *            
     */
    public function removeConnectionByUserID($user_id)
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        $wpdb->query($wpdb->prepare('DELETE FROM `' . $wpdb->prefix . 'digilan_token_social_users_' . $version . '` WHERE type = %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
    }

    protected function unlinkUser()
    {
        $user_info = wp_get_current_user();
        if ($user_info->ID) {
            $this->removeConnectionByUserID($user_info->ID);

            return true;
        }

        return false;
    }

    /**
     * If the current user has linked the account with a provider return the user identifier else false.
     *
     * @return bool|null|string
     */
    public function isCurrentUserConnected()
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        $current_user = wp_get_current_user();
        $ID = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'digilan_token_social_users_' . $version . '` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $current_user->ID
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    /**
     * If a user has linked the account with a provider return the user identifier else false.
     *
     * @param
     *            $user_id
     *            
     *            
     * @return bool|null|string
     */
    public function isUserConnected($user_id)
    {
        /** @var $wpdb WPDB */
        global $wpdb;
        $version = get_option('digilan_token_version');
        $ID = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'digilan_token_social_users_' . $version . '` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    public function findUserByAccessToken($access_token)
    {
        return $this->getUserIDByProviderIdentifier($this->findSocialIDByAccessToken($access_token));
    }

    public function findSocialIDByAccessToken($access_token)
    {
        $client = $this->getClient();
        $client->setAccessTokenData($access_token);
        $this->authUserData = $this->getCurrentUserInfo();

        return $this->getAuthUserData('id');
    }

    public function getConnectButton($buttonStyle = 'default', $redirectTo = null)
    {
        $arg = array();
        $redirect_to = DigilanTokenSanitize::sanitize_get('redirect_to');
        if (!empty($redirectTo)) {
            $arg['redirect'] = urlencode($redirectTo);
        } else if ($redirect_to) {
            $arg['redirect'] = urlencode($redirect_to);
        }

        switch ($buttonStyle) {
            case 'icon':

                $button = $this->getIconButton();
                break;
            default:

                $button = $this->getDefaultButton($this->settings->get('login_label'));
                break;
        }

        $button = '<a href="' . esc_url(add_query_arg($arg, $this->getLoginUrl())) . '" class="dlt-auth" rel="nofollow" aria-label="' . esc_attr__($this->settings->get('login_label')) . '" data-plugin="dlt" data-action="connect" data-popupwidth="' . $this->getPopupWidth() . '" data-popupheight="' . $this->getPopupHeight() . '">' . $button . '</a>';
        return $button;
    }

    public function redirectToLoginForm()
    {
        self::redirect(__('Authentication error', 'digilan-token'), DigilanToken::getLoginUrl());
    }

    /**
     * -Allows for logged in users to unlink their account from a provider, if it was linked, and
     * redirects to the last location.
     * -During linking process, store the action as link. After the linking process is finished,
     * delete this stored info and redirects to the last location.
     */
    public function liveConnectBefore()
    {
        if (is_user_logged_in() && $this->isCurrentUserConnected()) {

            $action = DigilanTokenSanitize::sanitize_get('action');
            if ($action == 'unlink') {
                if ($this->unlinkUser()) {
                    \DLT\Notices::addSuccess(__('Unlink successful.', 'digilan-token'));
                }
            }

            $this->redirectToLastLocationOther();
            exit();
        }

        if ($action == 'link') {
            \DLT\Persistent\Persistent::set($this->id . '_action', 'link');
        }

        if (is_user_logged_in() && \DLT\Persistent\Persistent::get($this->id . '_action') != 'link') {
            $this->deleteLoginPersistentData();

            $this->redirectToLastLocationOther();
            exit();
        }
    }

    /**
     * Store where the user logged in.
     */
    public function liveConnectRedirect()
    {
        $redirect = DigilanTokenSanitize::sanitize_get('redirect');
        if (!empty($redirect)) {
            \DLT\Persistent\Persistent::set('redirect', $redirect);
        }
    }

    public function redirectToLastLocation()
    {
        self::redirect(__('Authentication successful', 'digilan-token'), $this->getLastLocationRedirectTo());
    }

    protected function redirectToLastLocationOther()
    {
        $this->redirectToLastLocation();
    }

    protected function validateRedirect($location)
    {
        $location = wp_sanitize_redirect($location);

        return wp_validate_redirect($location, apply_filters('wp_safe_redirect_fallback', admin_url(), 302));
    }

    /**
     * If fixed redirect url is set, redirect to fixed redirect url.
     * If fixed redirect url is not set, but redirect is in the url redirect to the $_GET['redirect'].
     * If fixed redirect url is not set and there is no redirect in the url, redirects to the default redirect url if it
     * is set.
     * Else redirect to the site url.
     *
     * @return mixed|void
     */
    protected function getLastLocationRedirectTo()
    {
        $redirect_to = '';
        $requested_redirect_to = '';
        $fixedRedirect = '';

        if (DigilanToken::$WPLoginCurrentFlow == 'register') {

            $fixedRedirect = DigilanToken::$settings->get('redirect_reg');
            $fixedRedirect = apply_filters($this->id . '_register_redirect_url', $fixedRedirect, $this);
        } else if (DigilanToken::$WPLoginCurrentFlow == 'login') {

            $fixedRedirect = DigilanToken::$settings->get('redirect');
            $fixedRedirect = apply_filters($this->id . '_login_redirect_url', $fixedRedirect, $this);
        }

        if (!empty($fixedRedirect)) {
            $redirect_to = $fixedRedirect;
        } else {
            $requested_redirect_to = \DLT\Persistent\Persistent::get('redirect');

            if (!empty($requested_redirect_to)) {
                if (empty($requested_redirect_to) || !DigilanToken::isAllowedRedirectUrl($requested_redirect_to)) {
                    $redirect = DigilanTokenSanitize::sanitize_get('redirect');
                    if (!empty($redirect) && DigilanToken::isAllowedRedirectUrl($redirect)) {
                        $requested_redirect_to = $redirect;
                    } else {
                        $requested_redirect_to = '';
                    }
                }

                if (empty($requested_redirect_to)) {
                    $redirect_to = site_url();
                } else {
                    $redirect_to = $requested_redirect_to;
                }
                $redirect_to = wp_sanitize_redirect($redirect_to);
                $redirect_to = wp_validate_redirect($redirect_to, site_url());

                $redirect_to = $this->validateRedirect($redirect_to);
            }

            $redirect_to = apply_filters('dlt_' . $this->getId() . 'default_last_location_redirect', $redirect_to, $requested_redirect_to);
        }

        if ($redirect_to == '' || $redirect_to == $this->getLoginUrl()) {
            $redirect_to = site_url();
        }

        \DLT\Persistent\Persistent::delete('redirect');

        return apply_filters('dlt_' . $this->getId() . 'last_location_redirect', $redirect_to, $requested_redirect_to);
    }

    /**
     *
     * @param
     *            $user_id
     * @param $provider DigilanTokenSocialProvider
     * @param $access_token string
     */
    public function syncProfile($user_id, $provider, $access_token)
    {
    }

    /**
     * Check if a logged in user with manage_options capability, want to verify their provider settings.
     *
     * @return bool
     */
    public function isTest()
    {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            if (isset($_REQUEST['test'])) {
                \DLT\Persistent\Persistent::set('test', 1);

                return true;
            } else if (\DLT\Persistent\Persistent::get('test') == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Make the current provider in verified mode, and update the oauth_redirect_url.
     */
    public function testConnectGetUserProfile()
    {
        $this->deleteLoginPersistentData();

        $this->settings->update(array(
            'tested' => 1,
            'oauth_redirect_url' => $this->getRedirectUri()
        ));
        wp_redirect($this->admin->getUrl('settings'));
        \DLT\Notices::addSuccess(__('The test was successful', 'digilan-token'));
        exit();
    }

    /**
     * Store the accessToken data.
     *
     * @param
     *            $accessToken
     *            
     */
    protected function setAnonymousAccessToken($accessToken)
    {
        \DLT\Persistent\Persistent::set($this->id . '_at', $accessToken);
    }

    protected function getAnonymousAccessToken()
    {
        return \DLT\Persistent\Persistent::get($this->id . '_at');
    }

    public function deleteLoginPersistentData()
    {
        \DLT\Persistent\Persistent::delete($this->id . '_at');
        \DLT\Persistent\Persistent::delete($this->id . '_display');
        \DLT\Persistent\Persistent::delete($this->id . '_action');
        \DLT\Persistent\Persistent::delete('test');
    }

    /**
     *
     * @param $e Exception
     */
    protected function onError($e)
    {
        if (DigilanToken::$settings->get('debug') == 1 || $this->isTest()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $e->getMessage() . "\n";
        }
        $this->deleteLoginPersistentData();
        exit();
    }

    protected function saveUserData($user_id, $key, $data)
    {
        update_user_meta($user_id, $this->id . '_' . $key, $data);
    }

    protected function getUserData($user_id, $key)
    {
        return get_user_meta($user_id, $this->id . '_' . $key, true);
    }

    public function getAccessToken($user_id)
    {
        return $this->getUserData($user_id, 'access_token');
    }

    /**
     *
     * @return array
     */
    protected function getCurrentUserInfo()
    {
        return array();
    }

    protected function requestLongLivedToken($accessTokenData)
    {
        return $accessTokenData;
    }

    /**
     *
     * @param
     *            $key
     *            
     * @return string
     */
    public function getAuthUserData($key)
    {
        return '';
    }

    /**
     * Redirect the source of the popup window to a specified url.
     *
     * @param
     *            $title
     * @param
     *            $url
     *            
     */
    public static function redirect($title, $url)
    {
        wp_redirect($url);
        exit();
    }

    public function getSyncFields()
    {
        return $this->sync_fields;
    }

    public function hasSyncFields()
    {
        return !empty($this->sync_fields);
    }

    public function validateSettings($newData, $postedData)
    {
        return $newData;
    }

    protected function needUpdateAvatar($user_id)
    {
        return apply_filters('dlt_avatar_store', 1, $user_id, $this);
    }

    protected function updateAvatar($user_id, $url)
    {
        do_action('dlt_update_avatar', $this, $user_id, $url);
    }

    public function exportPersonalData($userID)
    {
        $data = array();

        $socialID = $this->isUserConnected($userID);
        if ($socialID !== false) {
            $data[] = array(
                'name' => $this->getLabel() . ' ' . __('Identifier'),
                'value' => $socialID
            );
        }

        $accessToken = $this->getAccessToken($userID);
        if (!empty($accessToken)) {
            $data[] = array(
                'name' => $this->getLabel() . ' ' . __('Access token'),
                'value' => $accessToken
            );
        }

        $profilePicture = $this->getUserData($userID, 'profile_picture');
        if (!empty($profilePicture)) {
            $data[] = array(
                'name' => $this->getLabel() . ' ' . __('Profile picture'),
                'value' => $profilePicture
            );
        }

        foreach ($this->getSyncFields() as $fieldName => $fieldData) {
            $meta_key = $this->settings->get('sync_fields/fields/' . $fieldName . '/meta_key');
            if (!empty($meta_key)) {
                $value = get_user_meta($userID, $meta_key, true);
                if (!empty($value)) {
                    $data[] = array(
                        'name' => $this->getLabel() . ' ' . $fieldData['label'],
                        'value' => $value
                    );
                }
            }
        }

        return $data;
    }

    protected function storeAccessToken($userID, $accessToken)
    {
        $this->saveUserData($userID, 'access_token', $accessToken);
    }

    public function getSyncDataFieldDescription($fieldName)
    {
        return '';
    }
}
