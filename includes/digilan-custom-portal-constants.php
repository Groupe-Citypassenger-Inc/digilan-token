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
class DigilanTokenCustomPortalConstants
{
    public static $user_form_fields = array(
        'gender' => array(
            'display-name' => array(
                'en_US' => 'Gender',
                'fr_FR' => 'Genre',
            ),
            'instruction'  =>  array(
                'en_US' => 'Gender',
                'fr_FR' => 'Genre',
            ),
            'type'         => 'radio',
            'options'      =>  array(
                'en_US' => 'Female, Male, Others',
                'fr_FR' => 'Femme, Homme, Autres',
            ),
        ),
        'age' => array(
            'display-name' => array(
                'en_US' => 'Age',
                'fr_FR' => 'Age',
            ),
            'instruction'  => array(
                'en_US' => 'How old are you ?',
                'fr_FR' => 'Quel âge avez-vous ?',
            ),
            'type'         => 'number',
            'unit'         => array(
                'en_US' => 'years',
                'fr_FR' => 'années',
            ),
        ),
        'nationality' => array(
            'display-name' => array(
                'en_US' => 'Nationality',
                'fr_FR' => 'Nationalité',
            ),
            'instruction'  => array(
                'en_US' => 'Select your nationality ?',
                'fr_FR' => 'Quel est votre nationalité ?',
            ),
            'type'         => 'select',
            'options'      => array(
                'en_US' => 'Français, English, Español',
                'fr_FR' => 'Français, English, Español',
            ),
        ),
        'stay-length' => array(
            'display-name' => array(
                'en_US' => 'Stay length',
                'fr_FR' => 'Durée du séjour',
            ),
            'instruction'  => array(
                'en_US' => 'Stay length in days',
                'fr_FR' => 'Durée du séjour en jours',
            ),
            'type'         => 'number',
            'unit'         => array(
                'en_US' => 'days',
                'fr_FR' => 'jours',
            ),
        )
    );

    public static $type_option_display_name = array(
        'text'     => 'Text',
        'email'    => 'Email',
        'tel'      => 'Tel',
        'number'   => 'Number',
        'radio'    => 'Radio buttons',
        'select'   => 'Drop-down menu',
        'checkbox' => 'Checkbox',
    );

    public static $langs_available = array(
        'English'    => array('name' => 'English'   , 'frenchName' => 'Anglais'   , 'code' => 'en_US', 'implemented' => true ),
        'French'     => array('name' => 'French'    , 'frenchName' => 'Français'  , 'code' => 'fr_FR', 'implemented' => true ),
        'German'     => array('name' => 'German'    , 'frenchName' => 'Allemand'  , 'code' => 'de_DE', 'implemented' => false),
        'Italian'    => array('name' => 'Italian'   , 'frenchName' => 'Italien'   , 'code' => 'it_IT', 'implemented' => false),
        'Portuguese' => array('name' => 'Portuguese', 'frenchName' => 'Portuguais', 'code' => 'pt_PT', 'implemented' => false),
        'Spanish'    => array('name' => 'Spanish'   , 'frenchName' => 'Espagnol'  , 'code' => 'es_ES', 'implemented' => false),
    );
}
