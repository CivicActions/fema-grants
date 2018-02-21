<?php

/**
 * @file
 * Alterations for this form.
 */

use \Drupal\Core\Form\FormStateInterface;

function femagrants_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (in_array($form_id, array('node_sf425_edit_form', 'node_sf425_form'))) {
    $form['field_3_recipient_organization']['widget'][0]['address']['#after_build'][] = 'femagrants_address_label';
  }
}

function femagrants_address_label($element, $form_state) {
  $element['organization']['#title'] = t('Recipient Organization Name');
  $element['organization']['#required'] = true;
  return $element;
}
