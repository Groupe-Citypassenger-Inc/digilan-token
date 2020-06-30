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
class DigilanTokenSocialProviderFacebook extends DigilanTokenSocialProvider
{

    protected $dbID = 'fb';

    /** @var DigilanTokenSocialProviderFacebookClient */
    protected $client;

    protected $color = '#4267b2';

    protected $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M22.688 0H1.323C.589 0 0 .589 0 1.322v21.356C0 23.41.59 24 1.323 24h11.505v-9.289H9.693V11.09h3.124V8.422c0-3.1 1.89-4.789 4.658-4.789 1.322 0 2.467.1 2.8.145v3.244h-1.922c-1.5 0-1.801.711-1.801 1.767V11.1h3.59l-.466 3.622h-3.113V24h6.114c.734 0 1.323-.589 1.323-1.322V1.322A1.302 1.302 0 0 0 22.688 0z"/></svg>';

    protected $popupWidth = 475;

    protected $popupHeight = 175;

    protected $sync_fields = array(
        'age_range' => array(
            'label' => 'Age range',
            'node' => 'me'
        ),
        'birthday' => array(
            'label' => 'Birthday',
            'node' => 'me',
            'scope' => 'user_birthday'
        ),
        'link' => array(
            'label' => 'Profile link',
            'node' => 'me'
        ),
        'locale' => array(
            'label' => 'Locale',
            'node' => 'me'
        ),
        'timezone' => array(
            'label' => 'Timezone',
            'node' => 'me'
        ),
        'currency' => array(
            'label' => 'Currency',
            'node' => 'me'
        ),
        'hometown' => array(
            'label' => 'Hometown',
            'node' => 'me',
            'scope' => 'user_hometown'
        ),
        'location' => array(
            'label' => 'Location',
            'node' => 'me',
            'scope' => 'user_location'
        ),
        'gender' => array(
            'label' => 'Gender',
            'node' => 'me'
        )
    );

    public function __construct()
    {
        $this->id = 'facebook';
        $this->label = 'Facebook';

        $this->path = dirname(__FILE__);

        $this->requiredFields = array(
            'appid' => 'App ID',
            'secret' => 'App Secret'
        );

        add_filter('dlt_finalize_settings_' . $this->optionKey, array(
            $this,
            'finalizeSettings'
        ));

        parent::__construct(array(
            'appid' => '',
            'secret' => '',
            'login_label' => 'Continue with <b>Facebook</b>',
            'link_label' => 'Link account with <b>Facebook</b>',
            'unlink_label' => 'Unlink account from <b>Facebook</b>'
        ));
    }

    protected function translateButtonText()
    {
        __('Continue with <b>Facebook</b>', 'digilan-token');
        __('Link account with <b>Facebook</b>', 'digilan-token');
        __('Unlink account from <b>Facebook</b>', 'digilan-token');
    }

    public function finalizeSettings($settings)
    {
        if (defined('DIGILAN_FB_APP_ID')) {
            $settings['appid'] = DIGILAN_FB_APP_ID;
        }
        if (defined('DIGILAN_FB_APP_SECRET')) {
            $settings['secret'] = DIGILAN_FB_APP_SECRET;
        }

        return $settings;
    }

    /**
     *
     * @return DigilanTokenSocialProviderFacebookClient
     */
    public function getClient()
    {
        if ($this->client === null) {

            require_once dirname(__FILE__) . '/facebook-client.php';

            $this->client = new DigilanTokenSocialProviderFacebookClient($this->id, $this->isTest());

            $this->client->setClientId($this->settings->get('appid'));
            $this->client->setClientSecret($this->settings->get('secret'));
            $this->client->setRedirectUri($this->getRedirectUri());
        }

        return $this->client;
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
                case 'appid':
                case 'secret':
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

    /**
     *
     * @param
     *            $accessTokenData
     *            
     * @return string
     * @throws Exception
     */
    protected function requestLongLivedToken($accessTokenData)
    {
        $client = $this->getClient();
        if (!$client->isAccessTokenLongLived()) {

            return $client->requestLongLivedAccessToken();
        }

        return $accessTokenData;
    }

    /**
     *
     * @return array|mixed
     * @throws Exception
     */
    protected function getCurrentUserInfo()
    {
        $fields = array(
            'id',
            'name',
            'email',
            'first_name',
            'last_name',
            'picture.type(large)'
        );
        $extra_fields = apply_filters('dlt_facebook_sync_node_fields', array(), 'me');

        return $this->getClient()->get('/me?fields=' . implode(',', array_merge($fields, $extra_fields)));
    }

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
                return $this->authUserData['first_name'];
            case 'last_name':
                return $this->authUserData['last_name'];
        }

        return parent::getAuthUserData($key);
    }

    public function syncProfile($user_id, $provider, $access_token)
    {
        if ($this->needUpdateAvatar($user_id)) {

            $profilePicture = $this->authUserData['picture'];
            if (!empty($profilePicture) && !empty($profilePicture['data'])) {
                if (isset($profilePicture['data']['is_silhouette']) && !$profilePicture['data']['is_silhouette']) {
                    $this->updateAvatar($user_id, $profilePicture['data']['url']);
                }
            }
        }

        $this->storeAccessToken($user_id, $access_token);
    }

    protected function saveUserData($user_id, $key, $data)
    {
        switch ($key) {
            case 'access_token':
                update_user_meta($user_id, 'fb_user_access_token', $data);
                break;
            default:
                parent::saveUserData($user_id, $key, $data);
                break;
        }
    }

    protected function getUserData($user_id, $key)
    {
        switch ($key) {
            case 'access_token':
                return get_user_meta($user_id, 'fb_user_access_token', true);
                break;
        }

        return parent::getUserData($user_id, $key);
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
        if (isset($this->sync_fields[$fieldName]['scope'])) {
            return sprintf(__('Required scope: %1$s', 'digilan-token'), $this->sync_fields[$fieldName]['scope']);
        }

        return parent::getSyncDataFieldDescription($fieldName);
    }
}

DigilanToken::addProvider(new DigilanTokenSocialProviderFacebook());
