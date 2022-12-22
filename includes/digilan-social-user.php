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
require_once(DLT_PATH . '/includes/digilan-userData.php');

class DigilanTokenSocialUser
{

  /** @var DigilanTokenSocialProvider */
  protected $provider;

  protected $access_token;

  private $userExtraData;

  protected $user_id;

  /**
   * DigilanTokenSocialUser constructor.
   *
   * @param DigilanTokenSocialProvider $provider
   * @param
   *            $access_token
   */
  public function __construct($provider, $access_token)
  {
    $this->provider = $provider;
    $this->access_token = $access_token;
  }

  /**
   *
   * @param $key $key
   *            is like id, email, name, first_name, last_name
   *            Returns a single userdata of the current provider or empty sting if $key is invalid.
   *            
   * @return string
   */
  public function getAuthUserData($key)
  {
    return $this->provider->getAuthUserData($key);
  }

  /**
   * Connect with a Provider
   * If user is not logged in
   * - and has no linked social data (in wp_digilan_social_users table), prepare them for register.
   * - but if has linked social data, log them in.
   * If the user is logged in, retrieve the user data,
   * - if the user has no linked social data with the selected provider and there is no other user who linked that id , link them and sync the access_token.
   */
  public function liveConnectGetUserProfile()
  {
    $user_id = $this->provider->getUserIDByProviderIdentifier($this->getAuthUserData('id'));
    if ($user_id !== null && !get_user_by('id', $user_id)) {
      $this->provider->removeConnectionByUserID($user_id);
      $user_id = null;
    }

    if (!is_user_logged_in()) {

      if ($user_id == null) {
        $this->prepareRegister();
      } else {
        $this->login($user_id);
      }
    } else {
      $current_user = wp_get_current_user();
      if ($user_id === null) {
        // Let's connect the account to the current user!

        if ($this->provider->linkUserToProviderIdentifier($current_user->ID, $this->getAuthUserData('id'))) {

          $this->provider->syncProfile($current_user->ID, $this->provider, $this->access_token);

          \DLT\Notices::addSuccess(
            sprintf(
              __('Your %1$s account is successfully linked with your account. Now you can sign in with %2$s easily.', 'digilan-token'),
              $this->provider->getLabel(),
              $this->provider->getLabel()
            )
          );
        } else {

          \DLT\Notices::addError(
            sprintf(
              __('You have already linked a(n) %s account. Please unlink the current and then you can link other %s account.', 'digilan-token'),
              $this->provider->getLabel(),
              $this->provider->getLabel()
            )
          );
        }
      } else if ($current_user->ID != $user_id) {

        \DLT\Notices::addError(sprintf(__('This %s account is already linked to other user.', 'digilan-token'), $this->provider->getLabel()));
      }
    }
  }

  /**
   * Prepares the registration and registers the user.
   * If the email is not registered yet, checks if register is enabled call register() function.
   * If the email is already registered, checks if autolink is enabled, if it is, log the user in.
   * Autolink enabled: links the current provider account with the existing social account and attempts to login.
   * Autolink disabled: Add error with already registered email message.
   */
  protected function prepareRegister()
  {
    $user_id = false;

    $providerUserID = $this->getAuthUserData('id');

    $email = '';
    $email = $this->getAuthUserData('email');

    if (empty($email)) {
      $email = '';
    } else {
      $user_id = email_exists($email);
    }
    if ($user_id === false) { // Real register
      $this->register($providerUserID, $email);
    } else if ($this->autoLink($user_id, $providerUserID)) {
      $this->login($user_id);
    }

    $this->provider->redirectToLoginForm();
  }

  /**
   * Makes the username in an appropriate format.
   * Removes white space and some special characters.
   * Also turns it into lowercase. And put a prefix before the username if user_prefix is set.
   * If this formated username is valid returns it, else return false.
   *
   * @param
   *            $username
   *            
   *            
   * @return bool|string
   */
  protected function sanitizeUserName($username)
  {
    if (empty($username)) {
      return false;
    }

    $username = strtolower($username);

    $username = preg_replace('/\s+/', '', $username);

    $sanitized_user_login = sanitize_user($this->provider->settings->get('user_prefix') . $username, true);

    if (empty($sanitized_user_login)) {
      return false;
    }

    if (!validate_username($sanitized_user_login)) {
      return false;
    }

    return $sanitized_user_login;
  }

  /**
   * Registers the user.
   *
   * @param
   *            $providerID
   * @param
   *            $email
   *            
   *            
   * @return bool
   */
  protected function register($providerID, $email)
  {
    DigilanToken::$WPLoginCurrentFlow = 'register';

    $sanitized_user_login = false;

    /**
     * First checks provided first_name & last_name if it is not available checks name if it is neither available checks secondary_name.
     */
    $sanitized_user_login = $this->sanitizeUserName($this->getAuthUserData('first_name') . $this->getAuthUserData('last_name'));
    if ($sanitized_user_login === false) {
      $sanitized_user_login = $this->sanitizeUserName($this->getAuthUserData('username'));
      if ($sanitized_user_login === false) {
        $sanitized_user_login = $this->sanitizeUserName($this->getAuthUserData('name'));
      }
    }

    $email = '';
    $email = $this->getAuthUserData('email');
    $userData = array(
      'email' => $email,
      'username' => $sanitized_user_login
    );

    do_action('dlt_before_register', $this->provider);

    do_action('dlt_' . $this->provider->getId() . '_before_register');

    /** @var array $userData Validated user data */
    $userData = $this->finalizeUserData($userData);

    /**
     * -If neither of the usernames ( first_name & last_name, secondary_name) are appropriate, the fallback username will be combined with and id that was sent by the provider.
     * -In this way we can generate an appropriate username.
     */
    if (empty($userData['username'])) {
      $userData['username'] = sanitize_user($this->provider->settings->get('user_fallback') . md5(uniqid(rand())), true);
    }

    /**
     * If the username is already in use, it will get a number suffix, that is not registered yet.
     */
    $default_user_name = $userData['username'];
    $i = 1;
    while (username_exists($userData['username'])) {
      $userData['username'] = $default_user_name . $i;
      $i++;
    }

    /**
     * Generates a random password.
     * And set the default_password_nag to true. So the user get notify about randomly generated password.
     */
    if (empty($userData['password'])) {
      $userData['password'] = wp_generate_password(12, false);

      add_action('user_register', array(
        $this,
        'registerCompleteDefaultPasswordNag'
      ));
    }
    /**
     * Preregister, checks what roles shall be informed about the registration and sends a notification to them.
     */
    do_action('dlt_pre_register_new_user', $this);

    /**
     * If there was no error during the registration process,
     * -links the user to the providerIdentifier ( wp_digilan_token_social_users table in database store this link ).
     * -set the roles for the user.
     * -login the user.
     */
    add_action('user_register', array(
      $this,
      'registerComplete'
    ), 31);

    $this->userExtraData = $userData;

    $user_data = array(
      'user_login' => wp_slash($userData['username']),
      'user_email' => wp_slash($userData['email']),
      'user_pass' => $userData['password']
    );

    $name = $this->getAuthUserData('name');
    if (!empty($name)) {
      $user_data['display_name'] = $name;
    }

    $first_name = $this->getAuthUserData('first_name');
    if (!empty($first_name)) {
      $user_data['first_name'] = $first_name;
    }

    $last_name = $this->getAuthUserData('last_name');
    if (!empty($last_name)) {
      $user_data['last_name'] = $last_name;
    }

    $error = wp_insert_user($user_data);

    if (is_wp_error($error)) {

      \DLT\Notices::addError($error);
      $this->redirectToLastLocationLogin();
    } else if ($error === 0) {
      $this->registerError();
      exit();
    }

    // registerComplete will log in user and redirects. If we reach here, the user creation failed.
    return false;
  }

  /**
   * By setting the default_password_nag to true, will inform the user about random password usage.
   */
  public function registerCompleteDefaultPasswordNag($user_id)
  {
    update_user_option($user_id, 'default_password_nag', true, true);
  }

  /**
   * Retrieves the name, first_name, last_name and update the user data.
   * Also set a reminder to change the generated password.
   * Links the user with the provider. Set their roles. Send notification about the registration to the selected roles.
   * Logs the user in.
   *
   * @param
   *            $user_id
   *            
   *            
   * @return bool
   */
  public function registerComplete($user_id)
  {
    if (is_wp_error($user_id) || $user_id === 0) {
      /**
       * Registration failed
       */
      $this->registerError();

      return false;
    }

    update_user_option($user_id, 'default_password_nag', true, true);

    $this->provider->linkUserToProviderIdentifier($user_id, $this->getAuthUserData('id'));

    do_action('dlt_registration_store_extra_input', $user_id, $this->userExtraData);

    do_action('dlt_register_new_user', $user_id, $this->provider);
    do_action('dlt_' . $this->provider->getId() . '_register_new_user', $user_id, $this->provider);

    $this->provider->deleteLoginPersistentData();

    do_action('register_new_user', $user_id);

    $this->login($user_id);

    return true;
  }

  private function registerError()
  {
    global $wpdb;

    $isDebug = DigilanToken::$settings->get('debug') == 1;
    if ($isDebug) {
      if ($wpdb->last_error !== '') {
        echo "<div id='error'><p class='wpdberror'><strong>WordPress database error:</strong> [" . esc_html($wpdb->last_error) . "]<br /><code>" . esc_html($wpdb->last_query) . "</code></p></div>";
      }
    }

    $this->provider->deleteLoginPersistentData();

    if ($isDebug) {
      exit();
    }
  }

  protected function login($user_id)
  {
    $this->user_id = $user_id;

    add_action('dlt_' . $this->provider->getId() . '_login', array(
      $this->provider,
      'syncProfile'
    ), 10, 3);

    wp_set_current_user($user_id);

    $secure_cookie = is_ssl();
    $secure_cookie = apply_filters('secure_signon_cookie', $secure_cookie, array());
    global $auth_secure_cookie; // XXX ugly hack to pass this to wp_authenticate_cookie

    $auth_secure_cookie = $secure_cookie;
    $user_info = get_userdata($user_id);

    do_action('wp_login', $user_info->user_login, $user_info);

    $this->finishLogin();

    $this->provider->redirectToLoginForm();
  }

  protected function finishLogin()
  {
    do_action('dlt_login', $this->user_id, $this->provider);
    do_action('dlt_' . $this->provider->getId() . '_login', $this->user_id, $this->provider, $this->access_token);

    $this->redirectToLastLocationLogin();
  }

  /**
   * Redirect the user to
   * -the Fixed redirect url if it is set
   * -where the login happened if redirect is specified in the url
   * -the Default redirect url if it is set, and if redirect was not specified in the url
   */
  public function redirectToLastLocationLogin()
  {
    add_filter('dlt_' . $this->provider->getId() . 'default_last_location_redirect', array(
      $this,
      'loginLastLocationRedirect'
    ), 9, 2);

    $this->provider->redirectToLastLocation();
  }

  /**
   * Modifies where the user shall be redirected, after successful login.
   *
   * @param
   *            $redirect_to
   * @param
   *            $requested_redirect_to
   *            
   *            
   * @return mixed|void
   */
  public function loginLastLocationRedirect($redirect_to, $requested_redirect_to)
  {
    return apply_filters('login_redirect', $redirect_to, $requested_redirect_to, wp_get_current_user());
  }

  /**
   * If autoLink is enabled, it links the current account with the provider.
   *
   * @param
   *            $user_id
   * @param
   *            $providerUserID
   *            
   *            
   * @return bool
   */
  public function autoLink($user_id, $providerUserID)
  {
    $isAutoLinkAllowed = true;
    $isAutoLinkAllowed = apply_filters('dlt_' . $this->provider->getId() . '_auto_link_allowed', $isAutoLinkAllowed, $this->provider, $user_id);
    if ($isAutoLinkAllowed) {
      return $this->provider->linkUserToProviderIdentifier($user_id, $providerUserID);
    }

    return false;
  }

  /**
   *
   * @return DigilanTokenSocialProvider
   */
  public function getProvider()
  {
    return $this->provider;
  }

  /**
   *
   * @param
   *            $userData
   *            
   * @return array
   * @throws DLTContinuePageRenderException
   */
  public function finalizeUserData($userData)
  {
    $data = new DigilanTokenSocialUserData($userData, $this, $this->provider);

    return $data->toArray();
  }
}
