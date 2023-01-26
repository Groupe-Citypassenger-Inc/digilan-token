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

namespace DLT\Persistent\Storage;

class Session extends StorageAbstract
{

  /**
   *
   * @var string name of the cookie. Can be changed with dlt_session_name filter and DLT_SESSION_NAME constant.
   *     
   * @see https://pantheon.io/docs/caching-advanced-topics/
   */
  private $sessionName = 'SESSdlt';

  public function __construct()
  {
    if (defined('DLT_SESSION_NAME')) {
      $this->sessionName = DLT_SESSION_NAME;
    }
    $this->sessionName = apply_filters('dlt_session_name', $this->sessionName);
  }

  public function clear()
  {
    parent::clear();

    $this->destroy();
  }

  private function destroy()
  {
    $sessionID = $this->sessionId;
    if ($sessionID) {
      $this->setCookie($sessionID, time() - YEAR_IN_SECONDS, apply_filters('dlt_session_use_secure_cookie', false));

      add_action('shutdown', array(
        $this,
        'destroySiteTransient'
      ));
    }
  }

  public function destroySiteTransient()
  {
    $sessionID = $this->sessionId;
    if ($sessionID) {
      delete_site_transient('dlt_' . $sessionID);
    }
  }

  protected function load($createSession = false)
  {
    static $isLoaded = false;
    if ($this->sessionId === null) {
      if (isset($_COOKIE[$this->sessionName])) {
        $this->sessionId = 'dlt_persistent_' . md5(SECURE_AUTH_KEY . $_COOKIE[$this->sessionName]);
      } else if ($createSession) {
        $unique = uniqid('dlt', true);

        $this->setCookie($unique, time() + DAY_IN_SECONDS, apply_filters('dlt_session_use_secure_cookie', false));

        $this->sessionId = 'dlt_persistent_' . md5(SECURE_AUTH_KEY . $unique);

        $isLoaded = true;
      }
    }

    if (!$isLoaded) {
      if ($this->sessionId !== null) {
        $data = maybe_unserialize(get_site_transient($this->sessionId));
        if (is_array($data)) {
          $this->data = $data;
        }
        $isLoaded = true;
      }
    }
  }

  private function setCookie($value, $expire, $secure = false)
  {
    setcookie($this->sessionName, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure);
  }
}
