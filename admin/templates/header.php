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

defined('ABSPATH') || die();
?>
<div id="dlt-admin">
  <div class="dlt-admin-header">
    <h1>
      <a href="<?php echo DigilanTokenAdmin::getAdminBaseUrl(); ?>">
        <img
          src="<?php echo plugins_url('images/mrwifi.png', DLT_ADMIN_PATH) ?>"
          width="64"
          height="64"
          alt="Digilan Token"
        />
        Monsieur WiFi
      </a>
    </h1>

    <a href="<?php echo DigilanTokenAdmin::getAdminUrl('assistant'); ?>" class="dlt-admin-header-nav">
      <?php _e('Assistant', 'digilan-token'); ?>
    </a>
  </div>
