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
$re = '/^[0-9A-Za-z]{32}$/';
$secret = get_option('digilan_token_secret');
if (preg_match($re, $secret) == 1) :
?>
  <div class="dlt-admin-content">
    <h1><?php _e('Mailing', 'digilan-token'); ?></h1>
    <div class ="public_key_instructions">
      <h2><?php _e('DKIM configuration', 'digilan-token')?></h2>
      <ul class ="dkim_step">
        <li><?php _e('Connect to your domain host', 'digilan-token'); ?></li>
        <li><?php _e('Go to DNS record configuration panel', 'digilan-token'); ?></li>
        <li><?php _e('Create a TXT record and name it with '.get_option('digilan_token_mail_selector').'._domainkey', 'digilan-token'); ?></li>
        <li><?php _e('Put generated TXT record in the newly created record', 'digilan-token'); ?></li>
        <li><?php _e('Activate DKIM signature', 'digilan-token'); ?></li>
      </ul>
      <button onclick="show_public_key()">Show/Hide TXT record</button>
      <div id="public_key_content" style="display:none;word-break: break-all;">
        <?php
        $public_key = DigilanTokenAdmin::dkim_txt_record();
        _e($public_key, 'digilan-token');?>
      </div>
      <h2><?php _e('SSH key configuration', 'digilan-token')?></h2>
      <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <input type="hidden" name="digilan-token-ssh-key-config" value="true" />
        <input type="hidden" name="action" value="digilan-token-plugin" />
        <input type="hidden" name="view" value="mailing" />

        <input type="submit" name="digilan_token_regenerate_keys" value="regenerate keys">
      </form>
      <script>
        function show_public_key() {
          var x = document.getElementById("public_key_content");
          if (x.style.display === "none") {
            x.style.display = "block";
          } else {
            x.style.display = "none";
          }
        }
      </script>
      <h2><?php _e('Test your DKIM configuration', 'digilan-token')?></h2>
      <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <input type="hidden" name="digilan-token-mail-params" value="true" />
        <input type="hidden" name="action" value="digilan-token-plugin" />
        <input type="hidden" name="view" value="mailing" />
        <fieldset>
          <label for="selector"><?php _e('Selector'); ?>: 
            <input type="text" name="digilan-token-mail-selector" value="<?php echo get_option('digilan_token_mail_selector',false);?>">
          </label>
        </fieldset>
        <fieldset>
          <label for="domain"><?php _e('Domain'); ?>: 
            <input type="text" name="digilan-token-domain" value="<?php echo get_option('digilan_token_domain',false);?>">
          </label>
        </fieldset>
        <input type="submit" name="digilan_token_mail_params" value="Valider">
      </form>

      <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <input type="hidden" name="digilan-token-dkim-test" value="true" />
        <input type="hidden" name="action" value="digilan-token-plugin" />
        <input type="hidden" name="view" value="mailing" />
        <input type="submit" name="digilan_token_dkim_test" value="Test DKIM configuration">
      </form>

      <h2><?php _e('SMTP configuration', 'digilan-token')?></h2>
      <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <input type="hidden" name="digilan-token-smtp-config" value="true" />
        <input type="hidden" name="action" value="digilan-token-plugin" />
        <input type="hidden" name="view" value="mailing" />
        <fieldset>
          <label for="digilan-token-smtp-host"><?php _e('Host'); ?>: 
            <input type="text" name="digilan-token-smtp-host" value="<?php echo get_option('digilan_token_smtp_host',false);?>">
          </label>
        </fieldset>
        <fieldset>
          <label for="digilan-token-smtp-host"><?php _e('Username'); ?>: 
            <input type="text" name="digilan-token-smtp-username" value="<?php echo get_option('digilan_token_smtp_username',false);?>">
          </label>
        </fieldset>
        <fieldset>
          <label for="digilan-token-smtp-password"><?php _e('Password'); ?>: 
            <input type="password" name="digilan-token-smtp-password" value="<?php echo get_option('digilan_token_smtp_password',false);?>">
          </label>
        </fieldset>
        <fieldset>
          <label for="digilan-token-smtp-port"><?php _e('Port'); ?>: 
            <input type="text" name="digilan-token-smtp-port" value="<?php echo get_option('digilan_token_smtp_port',false);?>">
          </label>
        </fieldset>

        <input type="submit" name="digilan_token_smtp_config" value="Valider">
      </form>
      
      <h2><?php _e('Send mail', 'digilan-token')?></h2>
      <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('digilan-token-plugin'); ?>
        <input type="hidden" name="digilan-token-send-mail" value="true" />
        <input type="hidden" name="action" value="digilan-token-plugin" />
        <input type="hidden" name="view" value="mailing" />
        <fieldset>
          <label for="digilan-token-mail-from"><?php _e('From'); ?>: 
            <input type="text" name="digilan-token-mail-from">
          </label>
        </fieldset>
        <fieldset>
          <label for="digilan-token-mail-subject"><?php _e('Mail subject'); ?>: 
            <input type="text" name="digilan-token-mail-subject">
          </label>
        </fieldset>
        <fieldset>
          <label for="digilan-token-mail-body"><?php _e('Email body'); ?>: 
            <input type="text" name="digilan-token-mail-body">
          </label>
        </fieldset>
        <input type="submit" name="digilan_token_smtp_config" value="Valider">
      </form>
      
    </div>
  </div>
<?php else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif; 