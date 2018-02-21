<?php

/**
 * @file
 * Alterations for this form.
 */

use \Drupal\Core\Form\FormStateInterface;

function femagrants_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (in_array($form_id, array('node_sf425_edit_form', 'node_sf425_form'))) {
    $form['field_3_recipient_organization']['widget'][0]['address']['#after_build'][] = 'femagrants_address_label';
    $form['#validate'][] = 'femagrants_form_node_sf425_validate';
  }
}

function femagrants_address_label($element, $form_state) {
  $element['organization']['#title'] = t('Recipient Organization Name');
  $element['organization']['#required'] = true;
  $element['organization']['#weight'] = -1000;
  return $element;
}

function femagrants_form_node_sf425_validate(&$form, FormStateInterface $form_state) {
  if ($form_state->hasValue('field_4a_duns_number')) {
    $duns = $form_state->getValue('field_4a_duns_number');
    if (!preg_match('/^[0-9]{9}([0-9A-Z-a-z]{4})?$/', $duns[0]['value'])) {
      $form_state->setErrorByName('field_4a_duns_number', t('Must be 9 digits, or 9 digits + 4 alphanumeric characters.'));
    }
  }
}
