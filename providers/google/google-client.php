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

require_once DLT_PATH . '/includes/digilan-oauth2.php';

class DigilanTokenSocialProviderGoogleClient extends DigilanTokenSocialOauth2
{

    protected $access_token_data = array(
        'access_token' => '',
        'expires_in'   => -1,
        'created'      => -1,
    );

    private $accessType = 'offline';
    private $approvalPrompt = 'force';

    protected $scopes = array(
        'email',
        'profile'
    );

    protected $endpointAuthorization = 'https://accounts.google.com/o/oauth2/auth';

    protected $endpointAccessToken = 'https://accounts.google.com/o/oauth2/token';

    protected $endpointRestAPI = 'https://www.googleapis.com/oauth2/v1/';

    protected $defaultRestParams = array(
        'alt' => 'json'
    );

    /**
     * @param string $access_token_data
     */
    public function setAccessTokenData($access_token_data)
    {
        $this->access_token_data = json_decode($access_token_data, true);
    }


    public function createAuthUrl()
    {
        return add_query_arg(array(
            'access_type'     => urlencode($this->accessType),
            'approval_prompt' => urlencode($this->approvalPrompt)
        ), parent::createAuthUrl());
    }

    /**
     * @param string $approvalPrompt
     */
    public function setApprovalPrompt($approvalPrompt)
    {
        $this->approvalPrompt = $approvalPrompt;
    }

    /**
     * @param $response
     *
     * @throws Exception
     */
    protected function errorFromResponse($response)
    {
        if (isset($response['error']['message'])) {
            throw new Exception($response['error']['message']);
        }
    }
}
