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
$secret = get_option('digilan_token_secret');
$re = '/^[0-9A-Za-z]{32}$/';
if (DigilanToken::isFromCitybox() || preg_match($re, $secret) == 1) :
?>
  <div class="dlt-dashboard-providers-container">
    <div class="dlt-dashboard-providers">

      <?php foreach (DigilanToken::$providers as $provider) : ?>
        <?php
        $state = $provider->getState();
        $providerAdmin = $provider->getAdmin();
        ?>

        <div
          class="dlt-dashboard-provider"
          data-provider="<?= esc_attr($provider->getId()); ?>"
          data-state="<?= esc_attr($state); ?>"
        > 
          <div class="dlt-dashboard-provider-top" style="background-color: <?= esc_attr($provider->getColor()); ?>;">
            <img
              src="<?= esc_url($provider->getIcon()); ?>"
              height="55"
              alt="<?= esc_attr($provider->getLabel()); ?>"
            />
            <h2><?= $provider->getLabel() ?></h2>
          </div>
          <div class="dlt-dashboard-provider-bottom">
            <div class="dlt-dashboard-provider-bottom-state">
              <?php
              switch ($state) {
                case 'not-configured':
                  _e('Not Configured', 'digilan-token');
                  break;
                case 'not-tested':
                  _e('Not Verified', 'digilan-token');
                  break;
                case 'configured':
                  _e('Configured', 'digilan-token');
                  break;
              }
              ?>
            </div>

            <?php
            switch ($state) {
              case 'not-configured':
            ?>
                <a href="<?= esc_url($providerAdmin->getUrl()); ?>" class="button button-secondary">
                  <?php _e('Getting Started', 'digilan-token'); ?>
                </a>
              <?php
                break;
              case 'not-tested':
              ?>
                <a href="<?= esc_url($providerAdmin->getUrl('settings')); ?>" class="button button-secondary">
                  <?php _e('Verify Settings', 'digilan-token'); ?>
                </a>
              <?php
                break;
              case 'configured':
              ?>
                <a
                  <?php if ($provider->getId() == 'transparent' || $provider->getId() == 'mail') : ?> 
                    href="<?= esc_url($providerAdmin->getUrl('buttons')); ?>"
                  <?php else : ?>
                    href="<?= esc_url($providerAdmin->getUrl('settings')); ?>"
                  <?php endif; ?>
                  class="button button-secondary"
                >
                  <?php _e('Settings', 'digilan-token'); ?>
                </a>
              <?php
                break;
              }
            ?>
          </div>

          <div class="dlt-dashboard-provider-sortable-handle"></div>
        </div>
      <?php endforeach; ?>

    </div>
    <div class="dlt-clear"></div>
  </div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif; ?>
