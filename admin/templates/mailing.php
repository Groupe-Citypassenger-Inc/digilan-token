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
  <?php
  if (array_key_exists('regenerate_keys', $_POST)) {
    DigilanToken::generate_keys();
  }
  ?>
  <div class="dlt-admin-content">
    <h1><?php _e('Mailing', 'digilan-token'); ?></h1>
    <?php if (false == dkim_is_configured()) { ?>
      <div class ="public_key_instructions">
        <h2><?php _e('DKIM configuration', 'digilan-token')?></h2>
        <ul class ="dkim_step">
          <li><?php _e('Connect to your domain host', 'digilan-token'); ?></li>
          <li><?php _e('Go to DNS record configuration panel', 'digilan-token'); ?></li>
          <li><?php _e('Add a TXT record with the public key', 'digilan-token'); ?></li>
          <li><?php _e('Activate DKIM signature', 'digilan-token'); ?></li>
        </ul>
        <button onclick="show_public_key()">Show public key</button>
        <div id="public_key_content" style="display:none">
          <?php 
          $public_key_base64 = get_option('digilan_token_mail_public_key');
          $public_key = base64_decode($public_key_base64);
          $public_key = str_replace('-----BEGIN PUBLIC KEY-----','',$public_key);
          $public_key = str_replace('-----END PUBLIC KEY-----','',$public_key);
          _e($public_key, 'digilan-token'); ?>
        </div>
        <form method="POST">
          <input type="submit" name="regenerate_keys" value="regenerate keys">
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
      </div>
    <?php } else { ?>
      <p style="color: green;"><?php _e('DKIM is already configured', 'digilan-token'); ?></p>
    <?php } ?>
    
    <h2><?php _e('Email content', 'digilan-token')?></h2>
    <p><?php _e('For each email in the system we can send a promotional or informative email', 'digilan-token'); ?>.</p>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Frequency', 'digilan-token'); ?>:</th>
            <td>
              <input type="hidden" name="action" value="digilan-token-plugin" />
              <input type="hidden" name="view" value="mailing" />
              <p style="display:inline"><?php _e('We will send the message', 'digilan-token'); ?> </p>
              <input type="number" name="dlt-frequency-begin" id="dlt-frequency-begin" min="0" max="31" placeholder="0 - 31" required />
              <p style="display:inline"><?php _e('days after the first connection and then every', 'digilan-token'); ?></p>
              <input type="number" name="dlt-frequency" id="dlt-frequency" min="1" max="31" placeholder="0 - 31" required />
              <p style="display:inline"><?php _e('days', 'digilan-token'); ?></p>
            </td>
          </tr>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Mail subject', 'digilan-token'); ?>:</th>
            <td>
              <fieldset>
                <label for="digilan-token-subject">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="mailing" />
                  <input type="text" name="dlt-mail-subject" id="dlt-mail-subject" class="regular-text" required />
                </label>
              </fieldset>
            </td>
          </tr>
          <tr>
            <th scope="row"><?php _e('Mail body', 'digilan-token'); ?>:</th>
            <td>
              <fieldset>
                <label for="digilan-token-body">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="mailing" />
                  <textarea name="dlt-mail-body" id="dlt-mail-body" rows="10" cols="50" required></textarea>
                </label>
              </fieldset>
            </td>
        </tbody>
      </table>
      <div class="submit">
        <input type="submit" name="dlt-mailing-submit" id="dlt-mailing-submit" class="button button-primary" value="<?php _e('Save settings', 'digilan-token'); ?>" disabled />
      </div>
    </form>
    <h1><?php _e('Testing', 'digilan-token'); ?></h1>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
      <?php wp_nonce_field('digilan-token-plugin'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row" style="vertical-align: middle;"><?php _e('Receiver', 'digilan-token'); ?>:</th>
            <td>
              <fieldset>
                <label for="digilan-token-body">
                  <input type="hidden" name="action" value="digilan-token-plugin" />
                  <input type="hidden" name="view" value="mailing" />
                  <input type="email" name="dlt-test-mail" id="dlt-test-mail" placeholder="<?php _e('Email address', 'digilan-token'); ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2, 4}$" />
                  <input type="submit" name="dlt-mailing-test-submit" id="dlt-mailing-test-submit" class="button button-primary" value="<?php _e('Test', 'digilan-token'); ?>" disabled />
                </label>
              </fieldset>
            </td>
          </tr>
        </tbody>
      </table>
  </form>
  </div>
  
<?php  else : ?>
  <div class="digilan-token-activation-required">
    <h1><?php _e('Activation required', 'digilan-token'); ?></h1>
    <p><?php _e('Please head to Configuration tab to activate the plugin.', 'digilan-token') ?></p>
  </div>
<?php endif;

function dkim_is_configured() {
  $output=null;
  $retval=null;

  $selector = "default";
  $domain = get_domain();
  $records = $selector."._domainkey.".$domain;
  exec('dig '.$records.' txt +short',$output,$retval);
  return !empty($output);
}
function get_domain() {
  $protocols = array( 'http://', 'https://', 'www.' );
  return str_replace( $protocols, '', site_url() );
}