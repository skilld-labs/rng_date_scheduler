<?php

/**
 * @file
 * Contains \Drupal\rng_date_scheduler\Controller\DateExplain.
 */

namespace Drupal\rng_date_scheduler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides dynamic tasks.
 */
class DateExplain extends ControllerBase {

  public function eventDates(EntityInterface $rng_event) {
    /** @var \Drupal\rng\EventManagerInterface $event_manager */
//    $event_manager = \Drupal::service('rng.event_manager');
//    $event_type = $event_manager->eventType($rng_event->getEntityTypeId(), $rng_event->bundle());

    $dates = rng_date_scheduler_get($rng_event);
//    foreach (rng_date_scheduler_get($rng_event) as $data) {
//      $dates[] = [
//        'fn' => $data['field_name'],
//        'date' => $data['date'],
//        'actions' => [
//          'before' => $data['access']['before'] == -1 ? 'forbidden' : 'neutral',
//          'after' => $data['access']['after'] == -1 ? 'forbidden' : 'neutral',
//        ],
//      ];
//    }

    $render = [];
    $now = DrupalDateTime::createFromTimestamp(\Drupal::request()->server->get('REQUEST_TIME'));

    $row_dates = [];

    $current = FALSE;

    $render['#attached']['library'][] = 'rng_date_scheduler/rng_date_scheduler.user';

    $previous_after = NULL;
    $row = [];
    foreach ($dates as $date) {
      /** @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList $field_item_list */
      $field_item_list = $rng_event->{$date->getFieldName()};
//      $d->getSettings();

      $before = $date->canAccessBefore();
      $after = $date->canAccessAfter();

      $row[] = $this->permittedCell([$previous_after, $before]);

      $row_dates[]['#plain_text'] = \Drupal::service('date.formatter')
        ->format($date->getDate()->format('U'), 'long');

      $row[]['#plain_text'] = $field_item_list->getFieldDefinition()
        ->getLabel();

      $previous_after = $after;
    }

    $row[] = $this->permittedCell([$previous_after]);

    $render['table'] = [
      '#type' => 'table',
      '#attributes' => ['class' => 'rng-date-scheduler-explain']
    ];

    // Add the date indicator row.
    $row_indicator = [];
    $d = 0;
    for ($i = 0; $i < count($row); $i+=2) {
      // !isset detects after last day, as the index does not exist.
      if (!$current && (!isset($dates[$d]) || $now < $dates[$d]->getDate())) {
        $row_indicator[] = [
          '#markup' => $this->t('Now'),
          '#wrapper_attributes' => ['class' => ['active-time']]
        ];
        $current = TRUE;
      }
      else {
        $row_indicator[]['#wrapper_attributes'] = ['class' => ['inactive-time']];
        $row_indicator[]['#wrapper_attributes'] = ['class' => ['inactive-time']];
      }
      $d++;
    }

    $render['table'][] = $row;
    $render['table']['dates'] = $row_dates;
    $render['table']['indicator'] = $row_indicator;
    $render['table']['indicator']['#attributes'] = ['class' => ['current-indicator']];

    return $render;
  }

  function permittedCell(array $access) {
    $forbidden = in_array(FALSE, $access, TRUE);
    $class = $forbidden ? 'forbidden' : 'neutral';
    $cell = [
      '#wrapper_attributes' => ['class' => [$class], 'rowspan' => 2],
      '#markup' => $forbidden ? $this->t('New registrations forbidden') : $this->t('Neutral'),
    ];
    return $cell;
  }

}
