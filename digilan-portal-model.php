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

class DigilanPortalModel {
    
    /**
     * @var String
     */
    private $portal = '';
    /**
     * @var String
     */
    private $landing = '';
    /**
     * @var Int
     */
    private $timeout = 7200;
    /**
     * @var String
     */
    private $error_page = '';
    /**
     * @var String
     */
    private $schedule = ''; 
    /**
     * @var String
     */
    private $ssid = '';
    /**
     * @var String
     */
    private $country_code = '';

    /**
     * DigilanPortalModel constructor.
     *
     * @param string $portal portal page
     * @param string $landing landing page
     * @param int $timeout timeout allowed connection
     * @param string $error_page error page
     * @param string $schedule schedule of connection availability
     * @param string $ssid ssid
     * @param string $country_code country code
     * 
     */
    function __construct(string $portal ='', string $landing='', int $timeout=7200, string $error_page='', string $schedule='', string $ssid='', string $country_code='') 
    {
        $this->portal = $portal;
        $this->landing = $landing;
        $this->timeout = $timeout;
        $this->error_page = $error_page;
        $this->schedule = $schedule;
        $this->ssid = $ssid;
        $this->country_code = $country_code;
    }
    
    public function get_config() 
    {
        $config = array(
            'portal' => $this->portal,
            'landing' => $this->landing,
            'timeout' => $this->timeout,
            'error_page' => $this->error_page,
            'schedule' => $this->schedule
        );
        return $config;
    }
    
}