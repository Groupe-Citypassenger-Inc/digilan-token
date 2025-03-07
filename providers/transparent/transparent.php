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
class DigilanTokenProviderTransparent extends DigilanTokenSocialProviderDummy
{

    protected $color = '#272f6b';
    protected $svg = '<svg with="28px" height="28.5px" aria-hidden="true" data-icon="globe-europe" role="img" viewBox="0 0 496 512"><path fill="currentColor" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm200 248c0 22.5-3.9 44.2-10.8 64.4h-20.3c-4.3 0-8.4-1.7-11.4-4.8l-32-32.6c-4.5-4.6-4.5-12.1.1-16.7l12.5-12.5v-8.7c0-3-1.2-5.9-3.3-8l-9.4-9.4c-2.1-2.1-5-3.3-8-3.3h-16c-6.2 0-11.3-5.1-11.3-11.3 0-3 1.2-5.9 3.3-8l9.4-9.4c2.1-2.1 5-3.3 8-3.3h32c6.2 0 11.3-5.1 11.3-11.3v-9.4c0-6.2-5.1-11.3-11.3-11.3h-36.7c-8.8 0-16 7.2-16 16v4.5c0 6.9-4.4 13-10.9 15.2l-31.6 10.5c-3.3 1.1-5.5 4.1-5.5 7.6v2.2c0 4.4-3.6 8-8 8h-16c-4.4 0-8-3.6-8-8s-3.6-8-8-8H247c-3 0-5.8 1.7-7.2 4.4l-9.4 18.7c-2.7 5.4-8.2 8.8-14.3 8.8H194c-8.8 0-16-7.2-16-16V199c0-4.2 1.7-8.3 4.7-11.3l20.1-20.1c4.6-4.6 7.2-10.9 7.2-17.5 0-3.4 2.2-6.5 5.5-7.6l40-13.3c1.7-.6 3.2-1.5 4.4-2.7l26.8-26.8c2.1-2.1 3.3-5 3.3-8 0-6.2-5.1-11.3-11.3-11.3H258l-16 16v8c0 4.4-3.6 8-8 8h-16c-4.4 0-8-3.6-8-8v-20c0-2.5 1.2-4.9 3.2-6.4l28.9-21.7c1.9-.1 3.8-.3 5.7-.3C358.3 56 448 145.7 448 256zM130.1 149.1c0-3 1.2-5.9 3.3-8l25.4-25.4c2.1-2.1 5-3.3 8-3.3 6.2 0 11.3 5.1 11.3 11.3v16c0 3-1.2 5.9-3.3 8l-9.4 9.4c-2.1 2.1-5 3.3-8 3.3h-16c-6.2 0-11.3-5.1-11.3-11.3zm128 306.4v-7.1c0-8.8-7.2-16-16-16h-20.2c-10.8 0-26.7-5.3-35.4-11.8l-22.2-16.7c-11.5-8.6-18.2-22.1-18.2-36.4v-23.9c0-16 8.4-30.8 22.1-39l42.9-25.7c7.1-4.2 15.2-6.5 23.4-6.5h31.2c10.9 0 21.4 3.9 29.6 10.9l43.2 37.1h18.3c8.5 0 16.6 3.4 22.6 9.4l17.3 17.3c3.4 3.4 8.1 5.3 12.9 5.3H423c-32.4 58.9-93.8 99.5-164.9 103.1z"></path></svg>';
    public $login_label;
    public $optionKey;

    public function __construct($defaultSettings)
    {
        $this->id = 'transparent';
        $this->label = 'Transparent';
        $this->login_label = 'Accéder à <b>Internet</b>';
        $this->optionKey = 'dlt_' . $this->id;

        do_action('dlt_provider_init', $this);

        $this->admin = new DigilanTokenSocialProviderAdmin($this);
        $this->settings = new DigilanTokenSettings($this->optionKey, $defaultSettings);
    }

    public function getOptionKey()
    {
        return $this->optionKey;
    }

    public function getLoginUrl()
    {
        $args = array(
            'loginSocial' => $this->getId()
        );

        return add_query_arg($args, DigilanToken::getLoginUrl());
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

    public function getConnectButton($buttonStyle = 'default', $redirectTo = null)
    {
        $arg = array();
        $redirect_to = DigilanTokenSanitize::sanitize_get('redirect_to');
        if (! empty($redirectTo)) {
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

        $button = '<a href="' . esc_url(add_query_arg($arg, $this->getLoginUrl())) . '" class="dlt-auth" rel="nofollow" aria-label="' . esc_attr__($this->settings->get('login_label')) . '" data-plugin="dlt" data-action="connect">' . $button . '</a>';
        return $button;
    }

    public function isConfigured()
    {
        return self::getState() == 'configured';
    }

    public function connect()
    {
        try {
            $this->doAuthenticate();
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    public function onError()
    {
        error_log('Failed to transparently authenticate.');
    }

    protected function doAuthenticate()
    {
        if (! headers_sent()) {
            if (function_exists('header_remove')) {
                header_remove("LOCATION");
            } else {
                header('LOCATION:', true);
            }
        }
        // Handle transparent login
        DigilanToken::authenticate_ap_user_on_wp();
    }

    public function validateSettings($newData, $postedData)
    {
        return $newData;
    }
}
$defaultSettings = array(
    'login_label' => 'Accéder à <b>Internet</b>'
);
DigilanToken::addProvider(new DigilanTokenProviderTransparent($defaultSettings));
