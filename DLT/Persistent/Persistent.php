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

namespace DLT\Persistent;

use DLT\Persistent\Storage\StorageAbstract;
use DLT\Persistent\Storage\Transient;
use DLT\Persistent\Storage\Session;

require_once dirname(__FILE__) . '/Storage/Abstract.php';
require_once dirname(__FILE__) . '/Storage/Session.php';
require_once dirname(__FILE__) . '/Storage/Transient.php';

class Persistent
{

    private static $instance;

    /** @var StorageAbstract */
    private $storage;

    public function __construct()
    {
        self::$instance = $this;
        add_action('init', array(
            $this,
            'init'
        ), 0);

        add_action('wp_login', array(
            $this,
            'transferSessionToUser'
        ), 10, 2);
    }

    public function init()
    {
        if ($this->storage === NULL) {
            if (is_user_logged_in()) {
                $this->storage = new Transient();
            } else {
                $this->storage = new Session();
            }
        }
    }

    public static function set($key, $value)
    {
        self::$instance->storage->set($key, $value);
    }

    public static function get($key)
    {
        return self::$instance->storage->get($key);
    }

    public static function delete($key)
    {
        self::$instance->storage->delete($key);
    }

    /**
     *
     * @param
     *            $user_login
     * @param \WP_User $user
     */
    public function transferSessionToUser($user_login, $user = null)
    {
        if (!$user) { // For do_action( 'wp_login' ) calls that lacked passing the 2nd arg.
            $user = get_user_by('login', $user_login);
        }

        $newStorage = new Transient($user->ID);
        /**
         * $this->storage might be NULL if init action not called yet
         */
        if ($this->storage !== NULL) {
            $newStorage->transferData($this->storage);
        }

        $this->storage = $newStorage;
    }
}

new Persistent();
