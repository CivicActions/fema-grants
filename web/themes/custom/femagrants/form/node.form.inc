<?php

/**
 * @file
 * Alterations for this form.
 */

use \Drupal\Core\Form\FormStateInterface;

function femagrants_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (in_array($form_id, array('node_sf425_edit_form', 'node_sf425_form'))) {
    // Remove collabsible field set around address fields.
    $form['field_3_recipient_organization']['widget'][0]['address']['#after_build'][] = 'femagrants_address_label';
    $form['field_3_recipient_organization']['widget'][0]['#collapsible'] = false;
    $form['field_3_recipient_organization']['widget'][0]['#type'] = 'container';
    // Clean up revision fields for grantees.
    if (\Drupal\user\Entity\User::load(\Drupal::currentUser()->id())->hasRole('grantee')) {
			$form['field_review_status']['#access'] = false;
			$form['revision_log']['#access'] = false;
			$form['revision']['#access'] = false;
    }
    // Process custom validations.
    $form['#validate'][] = 'femagrants_form_node_sf425_validate';
  }
}

function femagrants_address_label($element, $form_state) {
  // Change organization field name and move to top.
  $element['organization']['#title'] = t('Recipient Organization Name');
  $element['organization']['#required'] = true;
  $element['organization']['#weight'] = -1000;
  return $element;
}

function femagrants_form_node_sf425_validate(&$form, FormStateInterface $form_state) {
  // Validate DUNS field.
  if ($form_state->hasValue('field_4a_duns_number')) {
    $duns = $form_state->getValue('field_4a_duns_number');
    if (!preg_match('/^[0-9]{9}([0-9A-Z-a-z]{4})?$/', $duns[0]['value'])) {
      $form_state->setErrorByName('field_4a_duns_number', t('DUNS must be 9 digits, or 9 digits + 4 alphanumeric characters.'));
    }
  }
  // Validate EIN field.
  if ($form_state->hasValue('field_4b_ein')) {
    $ein = $form_state->getValue('field_4b_ein');
    if (!preg_match('/^^\d{2}\-?\d{7}$/', $ein[0]['value'])) {
      $form_state->setErrorByName('field_4b_ein', t('EIN must be 9 digits with an optional hypen.'));
    }
  }
  // Ensure only 1 of the program income fields is greater than 0.
  $m = 0;
  if ($form_state->hasValue('field_10m_program_income_exp_alt')) {
    $field = $form_state->getValue('field_10m_program_income_exp_alt');
    $m = $field[0]['value'];
  }
  $n = 0;
  if ($form_state->hasValue('field_10n_program_income_exp_alt')) {
    $field = $form_state->getValue('field_10n_program_income_exp_alt');
    $n = $field[0]['value'];
  }
  if (($m > 0) && ($n > 0)) {
    $form_state->setErrorByName('field_10m_program_income_exp_alt', t('Either the Program Income expended in accordance with the deduction alternative (field m) or the addition alternative (field n) must be zero.'));
    $form_state->setErrorByName('field_10n_program_income_exp_alt', t('Either the Program Income expended in accordance with the deduction alternative (field m) or the addition alternative (field n) must be zero.'));
  }
}
