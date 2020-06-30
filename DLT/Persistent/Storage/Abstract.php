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

abstract class StorageAbstract
{

    protected $sessionId = null;

    protected $data = array();

    public function set($key, $value)
    {
        $this->load(true);

        $this->data[$key] = $value;

        $this->store();
    }

    public function get($key)
    {
        $this->load();

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }

    public function delete($key)
    {
        $this->load();

        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            $this->store();
        }
    }

    public function clear()
    {
        $this->data = array();
        $this->store();
    }

    protected function load($createSession = false)
    {
        static $isLoaded = false;

        if (!$isLoaded) {
            $data = maybe_unserialize(get_site_transient($this->sessionId));
            if (is_array($data)) {
                $this->data = $data;
            }
            $isLoaded = true;
        }
    }

    private function store()
    {
        if (empty($this->data)) {
            delete_site_transient($this->sessionId);
        } else {
            set_site_transient($this->sessionId, $this->data, 60);
        }
    }

    /**
     *
     * @param StorageAbstract $storage
     */
    public function transferData($storage)
    {
        $this->data = $storage->data;
        $this->store();

        $storage->clear();
    }
}
