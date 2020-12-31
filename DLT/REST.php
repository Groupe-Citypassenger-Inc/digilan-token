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

namespace DLT;

class REST
{

    public function __construct()
    {
        \add_action('rest_api_init', array(
            $this,
            'rest_api_init'
        ));
    }

    public function rest_api_init()
    {
        \register_rest_route('digilan-token-plugin/v1', '/(?P<provider>\w[\w\s\-]*)/get_user', array(
            'args' => array(
                'provider' => array(
                    'required' => true,
                    'validate_callback' => array(
                        $this,
                        'validate_provider'
                    )
                ),
                'access_token' => array(
                    'required' => true
                )
            ),
            array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'get_user'
                )
            )
        ));
    }

    public function validate_provider($providerID)
    {
        return \DigilanToken::isProviderEnabled($providerID);
    }

    /**
     *
     * @param \WP_REST_Request $request
     *            Full details about the request.
     *            
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_user($request)
    {
        $provider = \DigilanToken::$allowedProviders[$request['provider']];
        $user = $provider->findUserByAccessToken($request['access_token']);
    
        return $user;
    }
}

new REST();
