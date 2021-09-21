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
$settings = DigilanToken::$settings;
DigilanTokenActivator::cityscope_bonjour();
$secret = get_option("digilan_token_secret");
$re = "/^[0-9A-Za-z]{32}$/";
if (preg_match($re, $secret) == 1) :
  $args = array(
    'post_type' => 'page',
    'posts_per_page' => -1
  );
  $loop = new WP_Query($args);
?>
<div class="dlt-admin-content">
    <h1><?php _e('Multiportal Configuration','digilan-token'); ?></h1>
    <div id='digilan_token_link_ap_div'>
        <h2><?php _e('Link User to an AP','digilan-token'); ?></h2>
        <?php 
        if (false == DigilanTokenMultiPortal::is_ap_available()) {
        ?>
            <p>
                <?php _e('There are not ap available','digilan-token'); ?>
            </p>
        <?php
        } else {
        ?>
            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('digilan-token-plugin'); ?>
                <input type="hidden" name="digilan-token-link-ap" value="true" />
                <input type="hidden" name="action" value="digilan-token-plugin" />
                <input type="hidden" name="view" value="multi-portal" />
                <fieldset>
                    <select name="digilan-token-hostname" id="digilan-token-select-hostname" class="regular-text">
                        <?php
                        $access_points = $settings->get('access-points');
                        foreach ($access_points as $hostname=>$content) :
                            if (false == isset($content['specific_ap_settings'])) {
                        ?>
                            <option value="<?php echo $hostname; ?>"><?php echo $hostname; ?></option>
                        <?php 
                            } 
                        endforeach; ?>
                    </select>
                    =>
                    <select name="digilan-token-user-id" id="digilan-token-user-id" class="regular-text">
                        <?php
                        $users = get_users( 'role=subscriber' );
                        foreach ( $users as $user ) :
                        ?>
                            <option value="<?php echo $user->ID; ?>"><?php echo $user->user_email; ?></option>
                        <?php 
                        endforeach; 
                        ?>
                    </select>
                </fieldset>
                <input type="submit" name="digilan_token_link_ap" class="button button-primary" value="Valider">
            </form>
        <?php
        } 
        ?>
    </div>
    <div id='digilan_token_generate_page_div'>
        <h2><?php _e('Generate multi-portal page','digilan-token'); ?></h2>
        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('digilan-token-plugin'); ?>
            <input type="hidden" name="digilan-token-generate-pages" value="true" />
            <input type="hidden" name="action" value="digilan-token-plugin" />
            <input type="hidden" name="view" value="multi-portal" />
            <p class="submit">
                <input type="submit" name="digilan_token_generate_page" class="button button-primary" value="Generer les pages">
            </p>
        </form>
    </div>
</div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif;