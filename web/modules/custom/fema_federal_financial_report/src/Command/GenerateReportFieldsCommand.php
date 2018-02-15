<?php

namespace Drupal\fema_federal_financial_report\Command;

use Drupal\Core\Config\Config;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class GenerateReportFieldsCommand.
 *
 * @DrupalCommand (
 *     extension="fema_federal_financial_report",
 *     extensionType="module"
 * )
 */
class GenerateReportFieldsCommand extends ContainerAwareCommand
{

  private $weight = 200;

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setName('fema_federal_financial_report:generate_report_fields')
      ->setDescription($this->trans('commands.fema_federal_financial_report.generate_report_fields.description'));
  }

  private function getWeight()
  {
    $this->weight++;
    return $this->weight - 1;
  }

  private function getUUID(Config $config)
  {
    $uuid = $config->get("uuid");
    if (empty($uuid)) {
      $uuid = "209e5750-2bfd-477b-938b-a48e74bdb" . rand(100, 999);
    }
    return $uuid;
  }

  private function generateFieldDefinition($field_name, $label, $required) {
    /* @var $config \Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable("field.field.node.report.field_{$field_name}");

    $definition = [
      "uuid" => $this->getUUID($config),
      "langcode" => "en",
      "status" => true,
      "dependencies" => [
        "config" => [
          "field.storage.node.field_{$field_name}",
          "node.type.report",
        ]
      ],
      "id" => "node.report.field_{$field_name}",
      "field_name" => "field_{$field_name}",
      "entity_type" => "node",
      "bundle" => "report",
      "label" => $label,
      "description" => '',
      "required" => $required,
      "translatable" => false,
      "default_value" => [],
      "default_value_callback" => '',
      "settings" => [],
      "field_type" => "string",
    ];

    $config->setData($definition);
    $config->save();
  }

  private function generateFieldStorage($field_name, $max_length, $cardinality) {
    /* @var $config \Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable("field.storage.node.field_{$field_name}");

    $storage = [
      "uuid" => $this->getUUID($config),
      "langcode" => "en",
      "status" => "true",
      "dependencies" => [
        "module" => ["node"],
      ],
      "id" => "node.field_{$field_name}",
      "field_name" => "field_{$field_name}",
      "entity_type" => "node",
      "type" => "string",
      "settings" => [
        "max_length" => $max_length,
        "is_ascii" => false,
        "case_sensitive" => false,
      ],
      "module" => "core",
      "locked" => false,
      "cardinality" => $cardinality,
      "translatable" => true,
      "indexes" => [],
      "persist_with_no_fields" => false,
      "custom_storage" => false,
    ];

    $config->setData($storage);
    $config->save();
  }

  private function includeFieldInForm($field_name)
  {
    $form = [
      "weight" => $this->getWeight(),
      "settings" => [
        "size" => 60,
        "placeholder" => '',
      ],
      "third_party_settings" => [],
      "type" => "string_textfield",
      "region" => "content",
    ];

    /* @var $config \Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable("core.entity_form_display.node.report.default");
    $content = $config->get("content");
    $content["field_{$field_name}"] = $form;
    $config->set('content', $content);
    $config->save();
  }

  private function includeFieldInDisplayDefault($field_name)
  {
    $display = [
      "weight" => $this->getWeight(),
      "label" => "above",
      "settings" => [
        "link_to_entity" => false
      ],
      "third_party_settings" => [],
      "type" => "string",
      "region" => "content",
    ];

    /* @var $config \Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable("core.entity_view_display.node.report.default");
    $content = $config->get("content");
    $content["field_{$field_name}"] = $display;
    $config->set('content', $content);
    $config->save();
  }

  private function includeFieldInDisplayTeaser($field_name)
  {
    /* @var $config \Drupal\Core\Config\Config */
    $config = \Drupal::service('config.factory')->getEditable("core.entity_view_display.node.report.teaser");
    $content = $config->get("hidden");
    $content["field_{$field_name}"] = true;
    $config->set('hidden', $content);
    $config->save();
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $io = new DrupalStyle($input, $output);

    $path = DRUPAL_ROOT . "/" . drupal_get_path("module", "fema_federal_financial_report") . "/data/fields.csv";
    $csv = Reader::createFromPath($path);
    $stmt = (new Statement())->offset(1);
    $records = $stmt->process($csv);

    foreach ($records as $record) {
      $field_name = $this->createFieldName($record);

      $io->simple("Creating field {$field_name}");

      $label = $this->getLabel($record);
      $required = $this->getRequired($record);
      $max_length = $this->getMaxLength($record);
      $cardinality = $this->getCardinality($record);

      $this->generateFieldDefinition($field_name, $label, $required);
      $this->generateFieldStorage($field_name, $max_length, $cardinality);
      $this->includeFieldInForm($field_name);
      $this->includeFieldInDisplayDefault($field_name);
      $this->includeFieldInDisplayTeaser($field_name);
    }

    $io->info($this->trans('commands.fema_federal_financial_report.generate_report_fields.messages.success'));
  }

  private function getCardinality($record) {
    return (int) $record[5];
  }

  private function getMaxLength($record) {
    $max = trim($record[9]);

    if (empty($max)) {
      $max = "1";
    }

    $max = (int) $max;
    if ($max > 500) {
      $max = 16;
    }

    return $max;
  }

  private function getRequired($record) {
    return ($record[3] == "Yes") ? true : false;
  }

  private function getLabel($record) {
    $label = trim($record[2]);
    if (empty($label)) {
      $label = trim($record[1]);
    }
    return $label;
  }

  private function createFieldName($record) {
    $field_number = trim($record[0]);
    $field_id = trim($record[1]);

    $field_name = str_replace(["-", " "], "_", strtolower($field_number . "_" . $field_id));

    $field_name = str_replace("/", "", $field_name);

    return substr($field_name, 0, 26);
  }

}