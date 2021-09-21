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

/** @var $view string */
$secret = get_option('digilan_token_secret');
$re = '/^[0-9A-Za-z]{32}$/';
?>
<div class="dlt-admin-nav-bar">
    <?php if (DigilanToken::isFromCitybox()) : ?>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('access-point'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'access-point') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Configuration', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('providers'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'providers') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Providers', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('connections'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'connections') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Connections', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('settings'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'settings') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Settings', 'digilan-token'); ?></a>
    <?php elseif (preg_match($re, $secret) == 1) : ?>
        <a href="<?php echo DigilanTokenAdmin::getAdminBaseUrl(); ?>" class="dlt-admin-nav-tab<?php if ($view === 'access-point') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Configuration', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('multi-portal'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'multi-portal') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Multi-Portal', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('providers'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'providers') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Providers', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('connections'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'connections') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Connections', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('logs'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'logs') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Logs', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('settings'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'settings') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Settings', 'digilan-token'); ?></a>
    <?php else : ?>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('access-point'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'access-point') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Configuration', 'digilan-token'); ?></a>
        <a href="<?php echo DigilanTokenAdmin::getAdminUrl('settings'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'settings') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Settings', 'digilan-token'); ?></a>
    <?php endif; ?>
</div>
