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
/** @var $this DigilanTokenSocialProviderAdmin */
/** @var $view string */
$provider = $this->getProvider()->getId();
?>
<div class="dlt-admin-sub-nav-bar">
   <?php if ($provider != 'transparent' && $provider != 'mail') : ?>
      <a href="<?php echo $this->getUrl(); ?>" class="dlt-admin-nav-tab<?php if ($view === 'getting-started') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Getting Started', 'digilan-token'); ?></a>
      <a href="<?php echo $this->getUrl('settings'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'settings') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Settings', 'digilan-token'); ?></a>
   <?php endif; ?>
   <a href="<?php echo $this->getUrl('buttons'); ?>" class="dlt-admin-nav-tab<?php if ($view === 'buttons') : ?> dlt-admin-nav-tab-active<?php endif; ?>"><?php _e('Buttons', 'digilan-token'); ?></a>
</div>