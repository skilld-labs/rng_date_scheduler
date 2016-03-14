<?php

/**
 * @file
 * Contains \Drupal\rng_date_scheduler\Form\EventTypeDateSchedulerForm.
 */

namespace Drupal\rng_date_scheduler\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Form for event type access defaults.
 */
class EventTypeDateSchedulerForm extends EntityForm {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\rng\EventTypeInterface
   */
  protected $entity;

  /**
   * Constructs a new EventTypeDateSchedulerForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $event_type = &$this->entity;

    $field_definitions = $this->entityFieldManager
      ->getFieldDefinitions($this->entity->getEventEntityTypeId(), $this->entity->getEventBundle());

    $form['default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Deny by default'),
      '#description' => $this->t('Deny new registrations if no dates are set on the event.'),
      '#default_value' => $event_type->getThirdPartySetting('rng_date_scheduler', 'default_access') == -1,
    ];

    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        [
          'data' => $this->t('Field'),
          'width' => '10%',
        ],
        [
          'data' => $this->t('Description'),
          'width' => '20%',
        ],
        [
          'data' => $this->t('Enabled'),
          'width' => '10%',
        ],
        [
          'data' => $this->t('Before'),
          'width' => '20%',
        ],
        [
          'data' => $this->t('During'),
          'width' => '20%',
        ],
        [
          'data' => $this->t('After'),
          'width' => '20%',
        ],
      ],
      '#empty' => $this->t('There are no date fields attached to this entity type.'),
    ];

    $fields = [];
    $status = [];
    foreach ($event_type->getThirdPartySetting('rng_date_scheduler', 'fields', []) as $field) {
      if (isset($field['field_name'])) {
        $field_name = $field['field_name'];
        $status[$field_name] = !empty($field['status']);
        $fields[$field_name] = $field['access'];
      }
    }

    foreach ($field_definitions as $field_definition) {
      $field_name = $field_definition->getName();
      $field_type = $field_definition->getType();

      if ($field_type == 'datetime') {
        $access = [];
        foreach (['before', 'during', 'after'] as $time) {
          $access[$time] = isset($fields[$field_name][$time]) && $fields[$field_name][$time] == '-1';
        }

        $row = [];

        $row['label'] = [
          '#plain_text' => $field_definition->getLabel(),
        ];

        $description = $field_definition->getDescription() ?: $this->t('None');
        $row['description'] = [
          '#plain_text' => $description,
        ];

        $row['status'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Status'),
          '#title_display' => 'invisible',
          '#default_value' => $status[$field_name],
        ];

        $row['before'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Deny new registrations'),
          '#description' => $this->t('Forbid creation of registrations before date in this field.'),
          '#default_value' => $access['before'],
        ];

        if ($field_definition->getSetting('datetime_type') == 'datetime') {
          $row[] = [
            '#plain_text' => $this->t('Not applicable'),
          ];
        }
        else {
          // Does not include time.
          $row['during'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Deny new registrations'),
            '#description' => $this->t('Forbid creation of registrations within date in this field.'),
            '#default_value' => $access['during'],
          ];
        }

        $row['after'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Deny new registrations'),
          '#description' => $this->t('Forbid creation of registrations after date in this field.'),
          '#default_value' => $access['after'],
        ];

        $form['table'][$field_name] = $row;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $event_type = &$this->entity;
    $default = $form_state->getValue('default') ? -1 : 0;

    $fields = [];
    foreach ($form_state->getValue('table') as $field_name => $row) {
      $field = [];
      $field['status'] = (boolean) $row['status'];
      $field['field_name'] = $field_name;

      foreach (['before', 'during', 'after'] as $time) {
        $deny_registrations = !empty($row[$time]);
        $field['access'][$time] = $deny_registrations ? -1 : 0;
      }

      $fields[] = $field;
    }

    $event_type
      ->setThirdPartySetting('rng_date_scheduler', 'default_access', $default)
      ->setThirdPartySetting('rng_date_scheduler', 'fields', $fields)
      ->save();

    drupal_set_message($this->t('Date settings saved.'));
  }

  /**
   * {@inheritdoc}
   *
   * Remove delete element since it is confusing on non CRUD forms.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    return $actions;
  }

}
