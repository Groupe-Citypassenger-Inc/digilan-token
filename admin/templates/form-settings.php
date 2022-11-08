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
function new_field_lang_row($lang, $is_required = false) 
{
  $src = 'images/flags/'. $lang['name'] .'.svg';
  $input_require_star = $is_required ? '*' : '';
  ob_start(); ?>
  <tr id="field-<?= $lang['name'] ?>-info" class="new_field_row">
    <th scope="row" style="vertical-align: middle;">
      <img
        class="language-flag"
        src="<?php echo plugins_url($src, DLT_ADMIN_PATH) ?>"
        alt="<?= $lang['name'] ?> flag"
        title="<?= $lang['name'] ?>"
        value="<?= $lang['name']; ?>"
        style="width: 45px;"
      />
    </th>
    <td>
      <fieldset class="language_fieldset">
        <label
          name="name"
          for="new-field-name-<?= $lang['code'] ?>"
          style="width: 500px; display: flex;"
        >
          <input
            type="text"
            name="digilan-token-new-field/display-name/<?= $lang['code'] ?>"
            id="new-field-name-<?= $lang['code'] ?>"
            pattern="^[a-zA-Z -]+$"
            placeholder="<?php _e('Field name', 'digilan-token'); ?><?= $input_require_star ?>"
            title="<?php _e('Field name', 'digilan-token'); ?><?= $input_require_star ?>"
            style="width:100%"
            <?php if( $is_required ): ?>
              required
            <?php endif; ?>
          />
        </label>
        <label
          name="instruction"
          for="new-field-instruction-<?= $lang['code'] ?>"
          style="width: 500px; display: flex;"
        >
          <input
            type="text"
            name="digilan-token-new-field/instruction/<?= $lang['code'] ?>"
            id="new-field-instruction-<?= $lang['code'] ?>"
            style="width:100%"
            placeholder="<?php _e('Instructions', 'digilan-token'); ?><?= $input_require_star ?>"
            title="<?php _e('Instructions*', 'digilan-token'); ?><?= $input_require_star ?>"
            <?php if( $is_required ): ?>
              required
            <?php endif; ?>
          />
        </label>
        <label 
          name="unit"
          for="new-field-unit-<?= $lang['code'] ?>"
          style="width: 500px; display: flex;"
        >
          <input
            type="text"
            name="digilan-token-new-field/unit/<?= $lang['code'] ?>"
            id="new-field-unit-<?= $lang['code'] ?>"
            style="width:100%"
            placeholder="<?php _e('Number unit', 'digilan-token'); ?>"
            title="<?php _e('Number unit', 'digilan-token'); ?>"
          />
        </label>
        <label
          name="options"
          for="new-field-options-<?= $lang['code'] ?>"
          style="width: 500px; display: flex;"
        >
          <input
            type="text"
            placeholder="<?php _e("Options: separate them with a comma [,]", "digilan-token"); ?><?= $input_require_star ?>"
            title="<?php _e("Options: separate them with a comma [,]", "digilan-token"); ?><?= $input_require_star ?>"
            name="digilan-token-new-field/options/<?= $lang['code'] ?>"
            id="new-field-options-<?= $lang['code'] ?>"
            pattern="^[a-zA-Z .\'-]+(,\s?[a-zA-Z .\'-]+)*[^,\s*]$"
            style="width:100%"
            <?php // use class for jquery to handle "required" with "display:none" input
            if( $is_required ): ?>
              class="required_input"
            <?php endif; ?>
            />
        </label>
      </fieldset>
    </td>
  </tr>
  <?php return ob_get_contents();
}

$fields = get_option("user_form_fields");
$form_languages = get_option("form_languages");
[$used_languages, $unused_languages] = array_reduce(
  $form_languages,
  function($acc, $lang) {
    $index = 1;
    if ($lang['implemented']) {
      $index = 0;
    }
    array_push($acc[$index], $lang);
    return $acc;
  },
  array(array(), array())
);

$user_lang = DigilanToken::get_user_lang();
$user_lang_code = $user_lang['code'];

$fields_key = array_keys($fields);
$form_shortcode = array_reduce($fields_key, function($shortcode, $field) {
  return $shortcode .= $field . '="1" ';
});

$types = get_option("typeOptions");
defined('ABSPATH') || die();?>
<div class="dlt-admin-content">
  <h1><?php _e('Form configuration', 'digilan-token'); ?></h1>
  <h2><?php _e('Languages integrated in the form:', 'digilan-token'); ?></h2>
  <div id="flags" style="display: flex; align-items: center; gap: 10px;">
    <?php
      foreach($used_languages as $lang):
        $src = 'images/flags/'. $lang['name'] .'.svg';
    ?>
      <div name="<?= $lang['name'] ?>" style="position: relative;">
        <img
          class="language-flag flag"
          src="<?php echo plugins_url($src, DLT_ADMIN_PATH) ?>"
          alt="<?= $lang['name'] ?> flag"
          title="<?= $lang['name'] ?>"
        />
        <input
          type="button"
          class="lang-flag-delete"
          value="x"
          name="<?= $lang['name'] ?>"
        />
      </div>
    <?php endforeach; ?>
    <div class="lang-select">
      <input
        type="text"
        placeholder="<?php _e('Add new language', 'digilan-token'); ?>"
        value=""
        id="lang-search"
        class="btn-select"
      />
      <div class="language_list_container">
        <ul id="language_list">
          <?php
          foreach($unused_languages as $lang):
            $src = 'images/flags/'. $lang['name'] .'.svg';
          ?>
            <li id="<?= $lang['name'] ?>" name="<?= $lang['name'] . '/' . $lang['frenchName']; ?>">
              <img
                class="language-flag"
                src="<?php echo plugins_url($src, DLT_ADMIN_PATH) ?>"
                alt="<?= $lang['name'] ?> flag"
                title="<?= $lang['name'] ?>"
                value="<?= $lang['name']; ?>"
              />
              <span><?= $lang['name']; ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <h2><?php _e('Add a new field for your form:', 'digilan-token'); ?></h2>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" >
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="digilan-token-new-form-field" value="true" />
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="form-settings" />
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row" style="vertical-align: middle;">
            <?php _e('Field type', 'digilan-token'); ?>
          </th>
          <td>
            <fieldset id="btns-field-type-choice">
              <input
                type="hidden"
                name="digilan-token-new-field/type"
                id="new-field-type"
                value=""
              />
              <button type="button" name="text" class="field_type">
                <?php _e('Text', 'digilan-token'); ?>
              </button>
              <button type="button" name="email" class="field_type">
                <?php _e('Email', 'digilan-token'); ?>
              </button>
              <button type="button" name="tel" class="field_type">
                <?php _e('Phone', 'digilan-token'); ?>
              </button>
              <button type="button" name="number" class="field_type">
                <?php _e('Number', 'digilan-token'); ?>
              </button>
              <button type="button" name="radio" class="field_type">
                <?php _e('Radio buttons', 'digilan-token'); ?>
              </button>
              <button type="button" name="select" class="field_type">
                <?php _e('Drop-down menu', 'digilan-token'); ?>
              </button>
              <button type="button" name="checkbox" class="field_type">
                <?php _e('Checkbox', 'digilan-token'); ?>
              </button>
            </fieldset>
          </td>
        </tr>
        <?php
        // Start with user language
        new_field_lang_row($user_lang, true);
        foreach($used_languages as $lang) {
          if ($lang === $user_lang) {
            continue;
          }
          new_field_lang_row($lang);
        }?>
      </tbody>
    </table>
    <p class="submit">
      <input
        type="submit"
        name="submit"
        id="submit-new-field"
        disabled
        class="button button-primary"
        value="<?php _e('Add Field', 'digilan-token'); ?>"
      />
    </p>
  </form>

  <h2><?php _e('Form fields', 'digilan-token'); ?></h2>
  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="form-field-list">
    <?php wp_nonce_field('digilan-token-plugin'); ?>
    <input type="hidden" name="digilan-token-user_form_fields" value="true" />
    <input type="hidden" name="action" value="digilan-token-plugin" />
    <input type="hidden" name="view" value="form-settings" />
    <div class="form-settings-field-row header">
      <div class="small"><?php _e('Name', 'digilan-token'); ?></div> 
      <div class="small"><?php _e('Position', 'digilan-token'); ?></div>
      <div class="small"><?php _e('Type', 'digilan-token'); ?></div>
      <div class="large"><?php _e('Instruction', 'digilan-token'); ?></div>
      <div class="small"><?php _e('Delete', 'digilan-token'); ?></div>
    </div>
    <?php foreach($fields as $field_key => $field_data): ?>
      <div name="field-row">
        <div class="form-settings-field-row">
          <div class="small">
            <?= $field_data['display-name'][$user_lang_code]; ?>
          </div>
          <div class="small">
            #<?= $field_data['position']; ?>
          </div>
          <div class="small">
            <?= $types[$field_data['type']]; ?>
          </div>
          <div class="large">
            <?= $field_data['instruction'][$user_lang_code]; ?>
          </div>
          <div class="small">
            <input
              type="checkbox"
              name="form-fields/delete/<?= $field_key; ?>"
              class="delete-field"
              value="delete"
            />
          </div>
        </div>
        <div class="edit-form-settings-field-row">
          <div>
            <?php foreach($used_languages as $lang):
              $lang_code = $lang['code'];
              $src = 'images/flags/'. $lang['name'] .'.svg';?>
              <div>
                <img
                  class="language-flag"
                  src="<?php echo plugins_url($src, DLT_ADMIN_PATH) ?>"
                  alt="<?= $lang['name'] ?> flag"
                  title="<?= $lang['name'] ?>"
                  value="<?= $lang['name']; ?>"
                  style="width: 45px;"
                />
                <label><?php _e('Name', 'digilan-token'); ?>: 
                  <input
                    type="text"
                    name="form-fields/display-name/<?= $field_key; ?>/<?= $lang_code; ?>"
                    class="update-field"
                    value="<?= $field_data['display-name'][$lang_code]; ?>"
                  />
                </label>
                <label><?php _e('Instruction', 'digilan-token'); ?>: 
                  <input
                    type="text"
                    name="form-fields/instruction/<?= $field_key; ?>/<?= $lang_code; ?>"
                    class="update-field"
                    value="<?= $field_data['instruction'][$lang_code]; ?>"
                  />
                </label>
                <?php if($field_data['options']): ?>
                  <label><?php _e('Options', 'digilan-token'); ?>: 
                    <input
                      type="text"
                      name="form-fields/options/<?= $field_key; ?>/<?= $lang_code; ?>"
                      class="update-field"
                      value="<?= $field_data['options'][$lang_code] ; ?>"
                    />
                  </label>
                <?php elseif($field_data['unit']): ?>
                  <label><?php _e('Unit', 'digilan-token'); ?>: 
                    <input
                      type="text"
                      name="form-fields/unit/<?= $field_key; ?>/<?= $lang_code; ?>"
                      class="update-field"
                      value="<?=  $field_data['unit'][$lang_code]; ?>"
                    />
                  </label>
                <?php endif; ?>
              </div>
            <?php endforeach ?>
            <!-- <?php if($config['regex'] && $config['type'] === 'text'): ?>
              <label><?php _e('Pattern', 'digilan-token'); ?>: 
                <input type="text" name="form-fields/regex/<?= $field; ?>" class="update-field" value="<?= $config['regex']; ?>" >
              </label>
              <?php endif; ?> -->
          </div>
          <input
            type="button"
            class="button button-primary"
            style="height: fit-content;"
            name="reset-changes-button"
            disabled
            value="<?php _e('Reset changes', 'digilan-token'); ?>"
          />
        </div>
      </div>
    <?php endforeach; ?>
    <p class="submit">
      <input
        type="submit"
        name="submit"
        id="submit-settings"
        class="button button-primary"
        value="<?php _e('Apply changes', 'digilan-token'); ?>"
      />
    </p>
  </form>
  <h2>
    <?php _e(
      'Select the fields you want to know about the visitors of your city:',
      'digilan-token'
    ); ?>
  </h2>
  <ol>
    <li>
      <?php _e(
        "Remove fields you don't want by setting '0' or by deleting them.",
        'digilan-token'
      ); ?>
    </li>
    <li>
      <?php _e(
        "Add this to the current 'digilan_token' shortcode to integrate the form.",
        'digilan-token'
      ); ?>
    </li>
  </ol>
    <div style="margin: 30px 0; display: flex; gap: 20px;">
    <input
      type="button"
      class="button button-primary"
      id="copy-shortcode"
      value="<?php _e('Copy form shortcode', 'digilan-token'); ?>"
    />
    <input
      type="text"
      value='<?= $form_shortcode ?>'
      id="form-shortcode"
      style="flex: 1"
    />
  </div>
</div>
