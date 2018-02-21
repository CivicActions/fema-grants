<?php

namespace Drupal\fgam_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the Example form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class Example extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('A sample form'),
    ];

    $form['name'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Name'),
    ];
    $form['name']['first'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
    ];
    $form['name']['last'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
    ];

    $form['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('description'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    $form['#groups_custom'] = [
      'group_identity' => [
        'group_name' => 'group_identity',
        'label' => 'Identity',
        'format_type' => 'multipage',
        'children' => [
          'name',
        ],
      ],
      'group_contact' => [
        'group_name' => 'group_contact',
        'label' => 'Contact',
        'format_type' => 'multipage',
        'children' => [
          'address',
        ],
      ],
      'group_description' => [
        'group_name' => 'group_description',
        'label' => 'Description',
        'format_type' => 'multipage',
        'children' => [
          'description',
        ],
      ],
      'group_steps' => [
        'group_name' => 'group_steps',
        'label' => 'Steps',
        'format_type' => 'multipage_group',
        'children' => [
          'group_identity',
          'group_contact',
          'group_description',
        ],
        'format_settings' => [
          'label' => 'Steps',
          'ajaxify' => '1',
          'nonjs_multistep' => '0',
          'classes' => ' group-steps field-group-multipage-group',
          'page_header' => '3',
          'page_counter' => '1',
          'move_button' => '1',
          'move_additional' => '1',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fgam_example';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!is_null($form_state->getValues())) {
      $values = $form_state->getValues();
      if (!is_null($form_state->get('field_group_ajaxified_multipage_enabled'))) {
        if (!is_null($form_state->get('field_group_ajaxified_multipage_enabled')) && !is_null($form_state->get('all')['values'])) {
          $values = $form_state->get('all')['values'];
        }
      }
    }
    drupal_set_message(
      $this->t(
        'The form has been submitted. name="@first @last", address=@address, description=@description', [
          '@first' => $values['first'],
          '@last' => $values['last'],
          '@address' => $values['address'],
          '@description' => $values['description'],
        ]
      ));
    return '';
  }

}
