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
abstract class DigilanTokenSocialAuth
{

    protected $providerID;

    protected $access_token_data;

    public function __construct($providerID)
    {
        $this->providerID = $providerID;
    }

    public function checkError()
    {
    }

    /**
     *
     * @param string $access_token_data
     */
    public function setAccessTokenData($access_token_data)
    {
        $this->access_token_data = json_decode($access_token_data, true);
    }

    public abstract function createAuthUrl();

    public abstract function authenticate();

    public abstract function get($path, $data = array(), $endpoint = false);

    /**
     *
     * @return bool
     */
    public abstract function hasAuthenticateData();

    /**
     *
     * @return string
     */
    public abstract function getTestUrl();
}
