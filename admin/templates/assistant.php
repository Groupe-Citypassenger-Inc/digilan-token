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
?>
<div class="dlt-admin-content">

    <?php

    echo '<h1>' . __('Test network connection with providers', 'digilan-token') . '</h1>';

    foreach (DigilanToken::$allowedProviders as $provider) {
    ?>
        <p>
            <a target="_blank" href="<?php echo add_query_arg('provider', $provider->getId(), DigilanTokenAdmin::getAdminUrl('test-connection')); ?>" class="button button-primary">
                <?php printf(__('Test %1$s connection', 'digilan-token'), $provider->getLabel()); ?>
            </a>
        </p>
    <?php
    }

    ?>
    <a class="button button-primary" href="<?php echo DigilanTokenAdmin::getAdminUrl('fix-redirect-uri'); ?>">
        <?php _e('Check OAuth redirect URI', 'digilan-token'); ?>
    </a>
</div>