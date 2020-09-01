<?php

namespace Drupal\rng_date_scheduler;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for event date provider.
 */
interface EventDateProviderInterface {

  /**
   * Get dates for an event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   An event entity.
   *
   * @return \Drupal\rng_date_scheduler\EventDateAccess[]
   *   An array of event date access objects.
   */
  public function getDates(EntityInterface $event);

  /**
   * Get date scheduler opinions on registration create access for an event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $event
   *   An event entity.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An access result object with cachability.
   */
  public function getRegistrationCreateAccess(EntityInterface $event);

  /**
   * Get default access.
   *
   * @param int $entity_type_id
   *   An event type entity type ID.
   * @param string $bundle
   *   An event type bundle.
   *
   * @return bool|null
   *   FALSE if forbidden. NULL if neutral.
   */
  public function getDefaultAccess($entity_type_id, $bundle);

  /**
   * Get field access settings for an event type.
   *
   * @param int $entity_type_id
   *   An event type' entity type ID.
   * @param string $bundle
   *   An event type' bundle.
   * @param bool|null $status
   *   The status of each field, or NULL to get all.
   *
   * @return array
   *   Field settings from configuration.
   */
  public function getFieldAccess($entity_type_id, $bundle, $status = TRUE);

}
