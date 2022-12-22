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
class DigilanTokenSocialUserData
{

  /** @var array */
  private $userData;

  /** @var DigilanTokenSocialUser */
  private $socialUser;

  /** @var DigilanTokenSocialProvider */
  private $provider;

  /** @var WP_Error */
  private $errors;

  private $isCustomRegisterFlow = false;

  /**
   * DigilanTokenSocialUserData constructor.
   *
   * @param
   *            $userData
   * @param
   *            $socialUser
   * @param
   *            $provider
   *            
   * @throws DLTContinuePageRenderException
   */
  public function __construct($userData, $socialUser, $provider)
  {
    $this->userData = $userData;
    $this->socialUser = $socialUser;
    $this->provider = $provider;

    $askExtraData = apply_filters('dlt_registration_require_extra_input', false, $this->userData);

    if ($askExtraData) {
      if (DigilanToken::$WPLoginCurrentView == 'login' && get_option('users_can_register')) {
        wp_redirect(add_query_arg(
          array('loginSocial' => $this->provider->getId()),
          DigilanToken::getRegisterUrl())
        );
        exit();
      }

      $this->errors = new WP_Error();

      $this->userData = apply_filters('dlt_registration_validate_extra_input', $this->userData, $this->errors);

      /**
       * It is not a submit or there is an error
       */
      if (!$this->isPost() || $this->errors->get_error_code() != '') {
        $this->displayForm();
      }
    }
  }

  public function toArray()
  {
    return $this->userData;
  }

  public function isPost()
  {
    return isset($_POST['submit']);
  }

  /**
   *
   * @throws DLTContinuePageRenderException
   */
  public function displayForm()
  {
    DigilanToken::removeLoginFormAssets();

    login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site!') . '</p>', $this->errors);

    $this->render_registration_form();

    login_footer('user_login');
    exit();
  }

  public function render_registration_form()
  {
    if (strpos(DigilanToken::$WPLoginCurrentView, 'register') === 0) {
      $postUrl = add_query_arg(
        array('loginSocial' => $this->provider->getId()),
        DigilanToken::getRegisterUrl()
      );
    } else {
      $postUrl = add_query_arg('loginSocial', $this->provider->getId(), DigilanToken::getLoginUrl('login_post'));
    }
?>
    <form name="registerform" id="registerform" action="<?php echo esc_url($postUrl); ?>" method="post">
      <input type="hidden" name="submit" value="1" />

      <?php do_action('dlt_registration_form_start', $this->userData); ?>

      <?php do_action('dlt_registration_form_end', $this->userData); ?>

      <br class="clear" />
      <p class="submit">
        <input
          type="submit"
          name="wp-submit"
          id="wp-submit"
          class="button button-primary button-large"
          value="<?php esc_attr_e('Register'); ?>"
        />
      </p>
    </form>
<?php
  }
}
