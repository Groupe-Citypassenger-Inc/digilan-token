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
abstract class DigilanTokenSocialProviderDummy
{

    protected $id;

    protected $label;

    protected $path;

    protected $color = '#fff';

    protected $btnCss = '';

    protected $popupWidth = 600;

    protected $popupHeight = 600;

    /** @var DigilanTokenSettings */
    public $settings;

    /** @var DigilanTokenSocialProviderAdmin */
    protected $admin = null;

    /**
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function enable()
    {
        return false;
    }

    public function isTested()
    {
        return false;
    }

    public function isTest()
    {
        return false;
    }

    public function connect()
    {
    }

    public function getState()
    {
        return '';
    }

    public function getIcon()
    {
        return plugins_url('/providers/' . $this->id . '/' . $this->id . '.png', DLT_PATH_FILE);
    }

    /**
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    public function import()
    {
        return true;
    }

    /**
     *
     * @return int
     */
    public function getPopupWidth()
    {
        return $this->popupWidth;
    }

    /**
     *
     * @return int
     */
    public function getPopupHeight()
    {
        return $this->popupHeight;
    }

    /**
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *
     * @return DigilanTokenSocialProviderAdmin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    public function setBtnCss($newBtnCss)
    {
        $this->btnCss = $newBtnCss;
    }

    /**
     *
     * @param string $subview
     *
     * @return bool
     */
    public function adminDisplaySubView($subview)
    {
        return false;
    }
}
