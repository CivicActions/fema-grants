<?php

namespace Drupal\workflow\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\user\UserInterface;

/**
 * Workflow configuration entity to persistently store configuration.
 *
 * @ConfigEntityType(
 *   id = "workflow_config_transition",
 *   label = @Translation("Workflow config transition"),
 *   label_singular = @Translation("Workflow config transition"),
 *   label_plural = @Translation("Workflow config transitions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Workflow config transition",
 *     plural = "@count Workflow config transitions",
 *   ),
 *   module = "workflow",
 *   translatable = FALSE,
 *   handlers = {
 *     "form" = {
 *        "delete" = "\Drupal\Core\Entity\EntityDeleteForm",
 *      }
 *   },
 *   admin_permission = "administer workflow",
 *   config_prefix = "transition",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "module",
 *     "from_sid",
 *     "to_sid",
 *     "roles",
 *   },
 *   links = {
 *     "collection" = "/admin/config/workflow/workflow/{workflow_type}/transitions",
 *   },
 * )
 */
class WorkflowConfigTransition extends ConfigEntityBase implements WorkflowConfigTransitionInterface {

  // Transition data.
  public $id;
  public $from_sid;
  public $to_sid;
  public $roles = [];

  // Extra fields.
  protected $wid;
  // The following must explicitly defined, and not be public, to avoid errors
  // when exporting with json_encode().
  protected $workflow = NULL;

  /*
   * Entity class functions.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values = [], $entityType = NULL) {
    // Please be aware that $entity_type and $entityType are different things!
    return parent::__construct($values, $entity_type = 'workflow_config_transition');
  }

  /**
   * Helper function for __construct. Used for all children of WorkflowTransition (aka WorkflowScheduledTransition)
   * @param $from_sid
   * @param $to_sid
   */
  public function setValues($from_sid, $to_sid) {
    $this->from_sid = $from_sid;
    $this->to_sid = $to_sid;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $workflow = $this->getWorkflow();

    // To avoid double posting, check if this (new) transition already exist.
    if (empty($this->id())) {
      if ($workflow) {
        $config_transitions = $workflow->getTransitionsByStateId($this->from_sid, $this->to_sid);
        $config_transition = reset($config_transitions);
        if ($config_transition) {
          $this->set('id', $config_transition->id());
        }
      }
    }

    // Create the machine_name. This can be used to rebuild/revert the Feature in a target system.
    if (empty($this->id())) {
      $wid = $workflow->id();
      $this->set('id', implode('', [$wid, substr($this->from_sid, strlen($wid)), substr($this->to_sid, strlen($wid))]));
    }

    $status = parent::save();

    if ($status) {
      // Save in current workflow for the remainder of this page request.
      // Keep in sync with Workflow::getTransitions() !
      if ($workflow) {
        $workflow->transitions[$this->id()] = $this;
        // $workflow->sortTransitions();
      }
    }

    return $status;
  }

  /** @noinspection PhpMissingParentCallCommonInspection
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().

    /** @var WorkflowTransitionInterface $a */
    /** @var WorkflowTransitionInterface $b */
    if (!$a->getFromSid() || !$b->getFromSid()) {
      return 0;
    }
    // First sort on From-State.
    $from_state_a = $a->getFromState();
    $from_state_b = $b->getFromState();
    if ($from_state_a->weight < $from_state_b->weight) return -1;
    if ($from_state_a->weight > $from_state_b->weight) return +1;

    // Then sort on To-State.
    $to_state_a = $a->getToState();
    $to_state_b = $b->getToState();
    if ($to_state_a->weight < $to_state_b->weight) return -1;
    if ($to_state_a->weight > $to_state_b->weight) return +1;

    return 0;
  }

  /**
   * Property functions.
   */

  /**
   * {@inheritdoc}
   */
  public function getWorkflow() {
    if (!$this->workflow && $wid = $this->getWorkflowId()) {
      $this->workflow = Workflow::load($wid);
    }
    return $this->workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function getWorkflowId() {
    if (!$this->wid) {
      $from_sid = $this->getFromSid();
      $to_sid = $this->getToSid();
      $state = WorkflowState::load($to_sid ? $to_sid : $from_sid);
      $this->wid = $state->getWorkflowId();
    }
    return $this->wid;
  }

  /**
   * {@inheritdoc}
   */
  public function getFromState() {
    return WorkflowState::load($this->from_sid);
  }

  /**
   * {@inheritdoc}
   */
  public function getToState() {
    return WorkflowState::load($this->to_sid);
  }

  /**
   * {@inheritdoc}
   */
  public function getFromSid() {
    return $this->from_sid;
  }

  /**
   * {@inheritdoc}
   */
  public function getToSid() {
    return $this->to_sid;
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed(UserInterface $user, $force = FALSE) {

    $type_id = $this->getWorkflowId();
    if ($user->hasPermission("bypass $type_id workflow_transition access")) {
      // Superuser is special. And $force allows Rules to cause transition.
      return TRUE;
    }
    if ($force) {
      return TRUE;
    }
    if ($this->getFromSid() == $this->getToSid()) {
      // Anyone may save an entity without changing state.
      return TRUE;
    }
    return TRUE == array_intersect($user->getRoles(), $this->roles);
  }

}
