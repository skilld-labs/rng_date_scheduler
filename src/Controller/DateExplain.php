<?php

/**
 * @file
 * Contains \Drupal\rng_date_scheduler\Controller\DateExplain.
 */

namespace Drupal\rng_date_scheduler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides dynamic tasks.
 */
class DateExplain extends ControllerBase {

  public function eventDates(EntityInterface $rng_event) {
    /** @var \Drupal\rng\EventManagerInterface $event_manager */
//    $event_manager = \Drupal::service('rng.event_manager');
//    $event_type = $event_manager->eventType($rng_event->getEntityTypeId(), $rng_event->bundle());

    $dates = [];
    foreach (rng_date_scheduler_get($rng_event) as $timestamp => $access) {
      $dates[] = [
        'date' => $timestamp,
        'actions' => [
          'before' => $access['before'] == -1 ? 'forbidden' : 'neutral',
          'after' => $access['after'] == -1 ? 'forbidden' : 'neutral',
        ],
      ];
    }

    $render = [];
    $now = \Drupal::request()->server->get('REQUEST_TIME');

    $row_dates = [];

    $current = FALSE;

    $render['#attached']['library'][] = 'rng_date_scheduler/rng_date_scheduler.user';

    $previous_after = NULL;
    $row = [];
    foreach ($dates as $date) {
      $before = $date['actions']['before'];
      $after = $date['actions']['after'];
      $timestamp = $date['date'];

      $row[] = $this->permittedCell($previous_after == 'forbidden' || $before == 'forbidden');

      $row_dates[]['#markup'] = \Drupal::service('date.formatter')->format($timestamp);
      $row[]['#plain_text'] = $this->t('Date field name');

      $previous_after = $after;
    }

    $row[] = $this->permittedCell($previous_after == 'forbidden');


    $render['table'] = [
      '#type' => 'table',
      '#attributes' => ['class' => 'rng-date-scheduler-explain']
    ];

    // Add the date indicator row.
    $row_indicator = [];
    $d = 0;
    for ($i = 0; $i < count($row); $i+=2) {
      if (!$current && (!isset($dates[$d]) || $now < $dates[$d]['date'])) {
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

  function permittedCell($forbidden) {
    $allowed = !$forbidden;
    $class = $allowed ? 'neutral' : 'forbidden';
    $cell = [
      '#wrapper_attributes' => ['class' => [$class], 'rowspan' => 2],
      '#markup' => $allowed ? $this->t('Neutral') : $this->t('New registrations forbidden'),
    ];
    return $cell;
  }

}
