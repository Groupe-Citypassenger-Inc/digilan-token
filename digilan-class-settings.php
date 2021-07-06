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
class DigilanTokenSettings
{

    protected $optionKey;

    protected $settings = array(
        'default' => array(),
        'stored' => array(),
        'final' => array()
    );

    /**
     * DigilanTokenSettings constructor.
     *
     * @param $optionKey string
     * @param $defaultSettings array
     */
    public function __construct($optionKey, $defaultSettings)
    {
        $this->optionKey = $optionKey;

        $this->settings['default'] = $defaultSettings;

        $storedSettings = get_option($this->optionKey);
        if ($storedSettings !== false) {
            $storedSettings = (array) maybe_unserialize($storedSettings);
        } else {
            $storedSettings = array();
        }

        $this->settings['stored'] = array_merge($this->settings['default'], $storedSettings);

        $this->settings['final'] = apply_filters('dlt_finalize_settings_' . $optionKey, $this->settings['stored']);
    }

    public function get($key, $storage = 'final')
    {
        return $this->settings[$storage][$key];
    }

    public function getAll($storage = 'final')
    {
        return $this->settings[$storage];
    }

    public function update($postedData)
    {
        if (is_array($postedData)) {
            $newData = array();
            $newData = apply_filters('dlt_update_settings_validate_' . $this->optionKey, $newData, $postedData);
            if (count($newData)) {

                $isChanged = false;
                foreach ($newData as $key => $value) {
                    if ($this->settings['stored'][$key] != $value) {
                        $this->settings['stored'][$key] = $value;
                        $isChanged = true;
                    }
                }

                if ($isChanged) {
                    $allowedKeys = array_keys($this->settings['default']);
                    $this->settings['stored'] = array_intersect_key($this->settings['stored'], array_flip($allowedKeys));

                    $this->storeSettings();
                }
            }
        }
    }

    protected function storeSettings()
    {
        update_option($this->optionKey, maybe_serialize($this->settings['stored']));

        $this->settings['final'] = apply_filters('dlt_finalize_settings_' . $this->optionKey, $this->settings['stored']);
    }
    public static function getAccessPointsByClient($hostname){
        $ap_list = array();
        $query = "SELECT 'user_id','meta_value' FROM {$wpdb->prefix}usermeta AS meta WHERE meta.meta_key = '%s'";
        $query = $wpdb->prepare($query, 'digilan-token-ap-list');
        // a row represent an array of hostname/mac for a user
        $rows = $wpdb->get_results($query);
        if (null === $rows)) {
            error_log('Access points are not available.');
            return false;
        } else {
            foreach ($rows as $row) {
                $row = (array) maybe_unserialize($row);
                $aps = $row->meta_value;
                foreach ($aps as $ap) {
                    if ($ap['hostname']==$hostname) {
                        $ap_list = $row->meta_value;
                    }
                }
            }
        }
        if (count($ap_list)) {
            return $ap_list;
        }
        return false;
        
    }
}
