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
class DigilanTokenSocialProviderGoogle extends DigilanTokenSocialProvider
{

    /** @var DigilanTokenSocialProviderGoogleClient */
    protected $client;

    protected $color = '#dc4e41';

    protected $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M7.636 11.545v2.619h4.331c-.174 1.123-1.309 3.294-4.33 3.294-2.608 0-4.735-2.16-4.735-4.822 0-2.661 2.127-4.821 4.734-4.821 1.484 0 2.477.632 3.044 1.178l2.073-1.997C11.422 5.753 9.698 5 7.636 5A7.63 7.63 0 0 0 0 12.636a7.63 7.63 0 0 0 7.636 7.637c4.408 0 7.331-3.098 7.331-7.462 0-.502-.054-.884-.12-1.266h-7.21zm16.364 0h-2.182V9.364h-2.182v2.181h-2.181v2.182h2.181v2.182h2.182v-2.182H24"/></svg>';

    protected $svgOfficial = '<svg xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path fill="#4285F4" fill-rule="nonzero" d="M20.64 12.2045c0-.6381-.0573-1.2518-.1636-1.8409H12v3.4814h4.8436c-.2086 1.125-.8427 2.0782-1.7959 2.7164v2.2581h2.9087c1.7018-1.5668 2.6836-3.874 2.6836-6.615z"/><path fill="#34A853" fill-rule="nonzero" d="M12 21c2.43 0 4.4673-.806 5.9564-2.1805l-2.9087-2.2581c-.8059.54-1.8368.859-3.0477.859-2.344 0-4.3282-1.5831-5.036-3.7104H3.9574v2.3318C5.4382 18.9832 8.4818 21 12 21z"/><path fill="#FBBC05" fill-rule="nonzero" d="M6.964 13.71c-.18-.54-.2822-1.1168-.2822-1.71s.1023-1.17.2823-1.71V7.9582H3.9573A8.9965 8.9965 0 0 0 3 12c0 1.4523.3477 2.8268.9573 4.0418L6.964 13.71z"/><path fill="#EA4335" fill-rule="nonzero" d="M12 6.5795c1.3214 0 2.5077.4541 3.4405 1.346l2.5813-2.5814C16.4632 3.8918 14.426 3 12 3 8.4818 3 5.4382 5.0168 3.9573 7.9582L6.964 10.29C7.6718 8.1627 9.6559 6.5795 12 6.5795z"/><path d="M3 3h18v18H3z"/></g></svg>';

    const requiredApi1 = 'Google+ API';

    protected $sync_fields = array(
        'gender' => array(
            'label' => 'Gender',
            'node' => 'me'
        ),
        'link' => array(
            'label' => 'Profile link',
            'node' => 'me'
        ),
        'locale' => array(
            'label' => 'Locale',
            'node' => 'me'
        ),
        'aboutMe' => array(
            'label' => 'Introduction',
            'node' => 'gplus',
            'description' => self::requiredApi1
        ),
        'birthday' => array(
            'label' => 'Birthday',
            'node' => 'gplus',
            'description' => self::requiredApi1
        ),
        'occupation' => array(
            'label' => 'Occupation',
            'node' => 'gplus',
            'description' => self::requiredApi1
        ),
        'organizations' => array(
            'label' => 'Organizations',
            'node' => 'gplus',
            'description' => self::requiredApi1
        ),
        'placesLived' => array(
            'label' => 'Places lived',
            'node' => 'gplus',
            'description' => self::requiredApi1
        ),
        'tagline' => array(
            'label' => 'Tag line',
            'node' => 'gplus',
            'description' => self::requiredApi1
        )
    );

    public function __construct()
    {
        $this->id = 'google';
        $this->label = 'Google';

        $this->path = dirname(__FILE__);

        $this->requiredFields = array(
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret'
        );

        parent::__construct(array(
            'client_id' => '',
            'client_secret' => '',
            'skin' => 'uniform',
            'login_label' => 'Continue with <b>Google</b>',
            'link_label' => 'Link account with <b>Google</b>',
            'unlink_label' => 'Unlink account from <b>Google</b>'
        ));
    }

    protected function translateButtonText()
    {
        __('Continue with <b>Google</b>', 'digilan-token');
        __('Link account with <b>Google</b>', 'digilan-token');
        __('Unlink account from <b>Google</b>', 'digilan-token');
    }

    public function getRawDefaultButton()
    {
        $skin = $this->settings->get('skin');
        switch ($skin) {
            case 'dark':
                $color = '#4285f4';
                $svg = $this->svgOfficial;
                break;
            case 'light':
                $color = '#fff';
                $svg = $this->svgOfficial;
                break;
            default:
                $color = $this->color;
                $svg = $this->svg;
        }

        return '<span class="dlt-button dlt-button-default dlt-button-' . $this->id . '" data-skin="' . $skin . '" style="height: 40px; background-color:' . $color . '; ' . $this->btnCss . '"><span class="dlt-button-svg-container">' . $svg . '</span><span class="dlt-button-label-container">{{label}}</span></span>';
    }

    public function validateSettings($newData, $postedData)
    {
        $newData = parent::validateSettings($newData, $postedData);

        foreach ($postedData as $key => $value) {

            switch ($key) {
                case 'tested':
                    if ($postedData[$key] == '1' && (!isset($newData['tested']) || $newData['tested'] != '0')) {
                        $newData['tested'] = 1;
                    } else {
                        $newData['tested'] = 0;
                    }
                    break;
                case 'skin':
                    $newData[$key] = trim(sanitize_text_field($value));
                    break;
                case 'client_id':
                case 'client_secret':
                    $newData[$key] = trim(sanitize_text_field($value));
                    if ($this->settings->get($key) !== $newData[$key]) {
                        $newData['tested'] = 0;
                    }

                    if (empty($newData[$key])) {
                        \DLT\Notices::addError(sprintf(__('The %1$s entered did not appear to be a valid. Please enter a valid %2$s.', 'digilan-token'), $this->requiredFields[$key], $this->requiredFields[$key]));
                    }
                    break;
            }
        }

        return $newData;
    }

    public function getClient()
    {
        if ($this->client === null) {

            require_once dirname(__FILE__) . '/google-client.php';

            $this->client = new DigilanTokenSocialProviderGoogleClient($this->id);

            $this->client->setClientId($this->settings->get('client_id'));
            $this->client->setClientSecret($this->settings->get('client_secret'));
            $this->client->setRedirectUri($this->getRedirectUri());
            $this->client->setApprovalPrompt('auto');
        }

        return $this->client;
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    protected function getCurrentUserInfo()
    {
        $fields = array(
            'id',
            'name',
            'email',
            'family_name',
            'given_name',
            'picture'
        );
        $extra_me_fields = apply_filters('dlt_google_sync_node_fields', array(), 'me');

        return $this->getClient()->get('userinfo?fields=' . implode(',', array_merge($fields, $extra_me_fields)));
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    public function getMyGooglePlus()
    {
        $extra_gplus_fields = apply_filters('dlt_google_sync_node_fields', array(), 'gplus');

        if (!empty($extra_gplus_fields)) {
            return $this->getClient()->get('people/me?fields=' . implode(',', $extra_gplus_fields), array(), 'https://www.googleapis.com/plus/v1/');
        }

        return $extra_gplus_fields;
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
        switch ($key) {
            case 'id':
                return $this->authUserData['id'];
            case 'email':
                return $this->authUserData['email'];
            case 'name':
                return $this->authUserData['name'];
            case 'first_name':
                return $this->authUserData['given_name'];
            case 'last_name':
                return $this->authUserData['family_name'];
            case 'picture':
                return $this->authUserData['picture'];
        }

        return parent::getAuthUserData($key);
    }

    public function syncProfile($user_id, $provider, $access_token)
    {
        if ($this->needUpdateAvatar($user_id)) {
            $this->updateAvatar($user_id, $this->getAuthUserData('picture'));
        }

        $this->storeAccessToken($user_id, $access_token);
    }

    public function getState()
    {
        return parent::getState();
    }

    public function adminDisplaySubView($subview)
    {
        return parent::adminDisplaySubView($subview);
    }

    public function deleteLoginPersistentData()
    {
        parent::deleteLoginPersistentData();

        if ($this->client !== null) {
            $this->client->deleteLoginPersistentData();
        }
    }

    public function getSyncDataFieldDescription($fieldName)
    {
        if (isset($this->sync_fields[$fieldName]['description'])) {
            return sprintf(__('Required API: %1$s', 'digilan-token'), $this->sync_fields[$fieldName]['description']);
        }

        return parent::getSyncDataFieldDescription($fieldName);
    }
}

DigilanToken::addProvider(new DigilanTokenSocialProviderGoogle());
