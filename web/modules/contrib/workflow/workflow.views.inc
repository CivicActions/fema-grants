<?php

/**
 * @file
 * Provide Views data for workflow.module.
 *
 * @ingroup views_module_handlers
 */

use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_field_views_data().
 */
function workflow_field_views_data(FieldStorageConfigInterface $field) {
  $data = views_field_default_views_data($field);
  $settings = $field->getSettings();

  foreach ($data as $table_name => $table_data) {
    foreach ($table_data as $field_name => $field_data) {
      if (isset($field_data['filter']) && $field_name != 'delta') {
        $data[$table_name][$field_name]['filter']['wid'] = (array_key_exists('workflow_type', $settings)) ? $settings['workflow_type'] : '';
        $data[$table_name][$field_name]['filter']['id'] = 'workflow_state';
      }
    }
  }

  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function workflow_views_data_alter(array &$data) {
  // Provide an integration for each entity type except workflow entities.
  // copied from comment.views.inc
  foreach (\Drupal::entityTypeManager()->getDefinitions() as $entity_type_id => $entity_type) {
    if (\Drupal\workflow\Entity\WorkflowManager::isWorkflowEntityType($entity_type_id)) {
      continue;
    }
    if (!$entity_type->entityClassImplements(\Drupal\Core\Entity\ContentEntityInterface::class)) {
      continue;
    }
    if (!$entity_type->getBaseTable()) {
      continue;
    }

    $fields = \Drupal::service('workflow.manager')->getFields($entity_type_id);
    if ($fields) {
      $base_table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();
      $args = ['@entity_type' => $entity_type_id];
      foreach ($fields as $field_name => $field) {
        $data[$base_table][$field_name . '_tid'] = [
          'title' => t('Workflow transitions on @entity_type using field: @field_name', $args + ['@field_name' => $field_name]),
          'help' => t('Relate all transitions ongit status @entity_type. This will create 1 duplicate record for every transition. Usually if you need this it is better to create a Transition view.', $args),
          'relationship' => [
            'group' => t('Workflow transition'),
            'label' => t('workflow transition'),
            'base' => 'workflow_transition_history',
            'base field' => 'entity_id',
            'relationship field' => $entity_type->getKey('id'),
            'id' => 'standard',
            'extra' => [
              [
                'field' => 'entity_type',
                'value' => $entity_type_id,
              ],
              [
                'field' => 'field_name',
                'value' => $field_name,
              ],
            ],
          ],
        ];
      }
    }
  }
}
