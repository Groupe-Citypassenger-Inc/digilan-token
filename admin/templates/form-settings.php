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
$fields = get_option("formFields");
$shortcode;
foreach ($fields as $field => $config) {
  $shortcode .= $field . '="1" ';
}
$types = get_option("typeOptions");
defined('ABSPATH') || die();?>
<div class="dlt-admin-content">
  <h1><?php _e('Form configuration', 'digilan-token'); ?></h1>
  <h2><?php _e('Add a new field for your form:', 'digilan-token'); ?></h1>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" >
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="digilan-token-new-form-field" value="true" />
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="form" />
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Field type', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset id="button-choice">
              <input
                type="hidden"
                name="digilan-token-new-field-type"
                id="new-field-type"
                value=""
              />
              <button type="button" name="text" class="field_type">Text</button>
              <button type="button" name="email" class="field_type">Email</button>
              <button type="button" name="tel" class="field_type">Tel</button>
              <button type="button" name="number" class="field_type">Number</button>
              <button type="button" name="radio" class="field_type">Radio buttons</button>
              <button type="button" name="select" class="field_type">Drop-down menu</button>
              <button type="button" name="checkbox" class="field_type">Checkbox</button>
            </fieldset>
          </td>
        </tr>
        <tr id="name" style="display:none">
          <th scope="row" style="vertical-align: middle;">
          <?php _e('Field name*', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="new-field-name" style="width:500px">
                <input
                  type="text"
                  name="digilan-token-new-field-name"
                  id="new-field-name"
                  pattern="^[a-zA-Z \'-]+$"
                  style="width:100%"
                  required
                />
              </label>
            </fieldset>
          </td>
        </tr>
       
        <tr id="instruction" style="display:none">
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Instructions*', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="new-field-instruction" style="width:500px">
                <input
                  type="text"
                  name="digilan-token-new-field-instruction"
                  id="new-field-instruction"
                  style="width:100%"
                />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr id="unit" style="display:none">
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Number unit*', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="new-field-unit" style="width:500px">
                <input
                  type="text"
                  name="digilan-token-new-field-unit"
                  id="new-field-unit"
                  style="width:100%"
                />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr id="options" style="display:none">
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Select options*', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="new-field-options" style="width:500px">
                <input
                  type="text"
                  placeholder="Separate options with a comma ','"
                  name="digilan-token-new-field-options"
                  id="new-field-options"
                  pattern="^[a-zA-Z .\'-]+(,\s?[a-zA-Z .\'-]+)*[^,\s*]$"
                  style="width:100%"
                />
              </label>
            </fieldset>
          </td>
        </tr>
        <tr id="multiple" style="display:none">
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Allow multiple', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset>
              <label for="new-field-multiple" style="width:500px">
                <input
                  type="checkbox"
                  name="digilan-token-new-field-multiple"
                  id="new-field-multiple"
                />
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit-new-field" disabled class="button button-primary" value="<?php _e('Add Field', 'digilan-token'); ?>">
    </p>
  </form>
  <h2><?php _e('Select the fields you want to know about the visitors of your city:', 'digilan-token'); ?></h1>
  <h4>Add this to your current "digilan_token" to add the form. Remove fields you don't want by setting "0" or by deleting them</h4>
  <div style="margin: 30px 0; display: flex; gap: 20px;">
    <input type="button" class="button button-primary" id="copy-shortcode" value="<?php _e('Copy form shortcode', 'digilan-token'); ?>">
    <input type="text" value='<?= $shortcode ?>' id="form-shortcode" style="flex: 1">
  </div>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="form-field-list">
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="digilan-token-formFields" value="true" />
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="form" />
    <div class="form-settings-field-row header">
      <div class="small">Title</div> 
      <div class="small">Position</div>
      <div class="small">Type</div>
      <div class="large">Instruction</div>
      <div class="small">Delete</div>
    </div>
    <?php foreach($fields as $field=>$config): ?>
    <div name="field">
      <div class="form-settings-field-row">
        <div class="small"><?= $config['display-name']; ?></div>
        <div class="small">#<?= $config['position']; ?></div>
        <div class="small"><?= $types[$config['type']]; ?></div>
        <div class="large"><?= $config['placeholder']; ?></div>
        <div class="small">
          <input type="checkbox" name="delete-<?= $field; ?>" class="delete-field" value="delete">
        </div>
      </div>
      <div class="edit-form-settings-field-row">
        <label>Name: 
          <input type="text" name="display-name-<?= $field; ?>" value="<?= $config['display-name']; ?>" >
        </label>
        <label>Instruction: 
          <input type="text" name="instruction-<?= $field; ?>" value="<?= $config['placeholder']; ?>" >
        </label>
        <?php if($config['regex'] && $config['type'] === 'text'): ?>
          <label>Pattern: 
            <input type="text" name="regex-<?= $field; ?>" value="<?= $config['regex']; ?>" >
          </label>
        <?php elseif($config['options']): ?>
          <label>Options: 
            <input type="text" name="options-<?= $field; ?>" value="<?= implode(", ", $config['options']); ?>" >
          </label>
        <?php elseif($config['unit']): ?>
          <label>Unit: 
            <input type="text" name="unit-<?= $field; ?>" value="<?=  $config['unit']; ?>" >
          </label>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <p class="submit">
      <input type="submit" name="submit" id="submit-settings" class="button button-primary" value="<?php _e('Apply changes', 'digilan-token'); ?>">
    </p>
  </form>
</div>
