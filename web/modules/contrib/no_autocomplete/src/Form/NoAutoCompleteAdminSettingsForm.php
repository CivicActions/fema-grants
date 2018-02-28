<?php

namespace Drupal\no_autocomplete\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to set our options..
 */
class NoAutoCompleteAdminSettingsForm extends ConfigFormBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'no_autocomplete_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['no_autocomplete.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('no_autocomplete.settings');
    $form['no_autocomplete_login_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use "autocomplete=off" on user login form'),
      '#default_value' => $config->get('no_autocomplete_login_form'),
    ];

    $form['no_autocomplete_profile_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use "autocomplete=off" on user profile edit form'),
      '#default_value' => $config->get('no_autocomplete_profile_form'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('no_autocomplete.settings');
    $config->set('no_autocomplete_login_form', $form_state->getValue('no_autocomplete_login_form'));
    $config->set('no_autocomplete_profile_form', $form_state->getValue('no_autocomplete_profile_form'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
