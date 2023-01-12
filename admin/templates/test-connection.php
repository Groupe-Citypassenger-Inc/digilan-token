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

$providerID = DigilanTokenSanitize::sanitize_get('provider');
if ($providerID) {
  if (isset(DigilanToken::$allowedProviders[$providerID])) {
    $provider = DigilanToken::$allowedProviders[$providerID];
?>
    <div class="dlt-admin-content">
      <h1>Assistant: <?= $provider->getLabel() ?></h1>

      <?php
      $url = esc_url_raw($provider->getTestUrl());
      $res = wp_remote_get($url);
      if (!is_wp_error($res)) {
      ?>
        <div class="updated">
          <p>
            <b>
              <?php printf(__('Network connection successful: %1$s', 'digilan-token'), $provider->getTestUrl()); ?>
            </b>
          </p>
        </div>
      <?php
      } else {
      ?>
        <div class="error">
          <p>
            <b><?php printf(__('Network connection failed: %1$s', 'digilan-token'), $provider->getTestUrl()); ?></b>
          </p>
          <p>
            <?php _e('Please contact with your hosting provider to resolve the network issue between your server and the provider.', 'digilan-token'); ?>
          </p>
        </div>
    </div>
<?php
    }
  }
}
