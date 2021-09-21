<?php

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
class DigilanTokenProviderMail extends DigilanTokenSocialProviderDummy
{

    protected $color = '#f35e24';

    protected $svg = '<svg with="28px" height="28.5px" data-icon="envelope" role="img" viewBox="0 0 512 512"><path fill="currentColor" d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z"></path></svg>';

    public function __construct($defaultSettings)
    {
        $this->id = 'mail';
        $this->label = 'Mail';
        $this->login_label = 'Continue with <b>Mail</b>';
        $this->optionKey = 'dlt_' . $this->id;

        do_action('dlt_provider_init', $this);

        add_action('admin_post_nopriv_dlt_mail_auth', 'DigilanTokenProviderMail::connect');
        $this->admin = new DigilanTokenSocialProviderAdmin($this);
        $this->settings = new DigilanTokenSettings($this->optionKey, $defaultSettings);
    }

    public function getOptionKey()
    {
        return $this->optionKey;
    }

    public function connect()
    {
        $mail = DigilanTokenSanitize::sanitize_post('dlt-mail');
        if ($mail) {
            $queries = array();
            $parsed_URL = parse_url(wp_get_referer(), PHP_URL_QUERY);
            parse_str($parsed_URL, $queries);
            $sid = $queries['session_id'];
            $mac = $queries['mac'];
            self::authenticateWithMail($sid, $mac);
        } else {
            error_log("Failed to authenticate with mail.");
        }
    }

    private function authenticateWithMail($sid, $mac)
    {
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
        $social_id = DigilanTokenSanitize::sanitize_post('dlt-mail');
        if (! $social_id) {
            return false;
        }
        $provider = 'mail';
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

    public function getState()
    {
        return 'configured';
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
        return str_replace('{{label}}', __($label, 'digilan-token'), $this->getRawDefaultButton());
    }

    public function getConnectButton($buttonStyle = 'default', $redirectTo = null, $mac = false)
    {
        $disabled = '';
        if ('00:00:00:00:00:00' == $mac || false == $mac) {
            $disabled = 'disabled';
        }
        switch ($buttonStyle) {
            case 'icon':

                $button = $this->getIconButton();
                break;
            default:

                $button = $this->getDefaultButton($this->settings->get('login_label'));
                break;
        }
        $admin_url = esc_url(admin_url('admin-post.php'));
        $form = '<form action="' . $admin_url . '" method="post">';
        $mail_input = '<input type="email" pattern="([+\w-]+(?:\.[+\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)" title="Incorrect" placeholder="Email address" required class="regular-text" name="dlt-mail" style="padding: 0.24rem 3.1rem;"'.$disabled.'/>';
        $action_input = '<input type="hidden" name="action" value="dlt_mail_auth"'.$disabled.'>';
        $submit_button = '<input type="submit" style="display: none;" class="dlt-auth" rel="nofollow" aria-label="' . esc_attr__($this->settings->get('login_label')) . '" data-plugin="dlt" data-action="connect" '.$disabled.'>';

        $button = $form . $mail_input . $action_input . '<label>' . $submit_button . $button . '</label></form>';

        return $button;
    }

    public function validateSettings($newData, $postedData)
    {
        return $newData;
    }

    public function isConfigured()
    {
        return self::getState() == 'configured';
    }
}
$defaultSettings = array(
    'login_label' => 'Continue with <b>Mail</b>'
);
DigilanToken::addProvider(new DigilanTokenProviderMail($defaultSettings));
