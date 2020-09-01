<?php

namespace Drupal\rng_date_scheduler;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Event date access.
 */
class EventDateAccess {

  /**
   * Date field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Date object.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  protected $date;

  /**
   * Can be accessed before date.
   *
   * @var bool
   */
  protected $accessBefore;

  /**
   * Can be accessed after date.
   *
   * @var bool
   */
  protected $accessAfter;

  /**
   * Get date field name.
   *
   * @return string
   *   Date field name.
   */
  public function getFieldName() {
    return $this->fieldName;
  }

  /**
   * Set date field name.
   *
   * @param string $field_name
   *   Date field name.
   */
  public function setFieldName($field_name) {
    $this->fieldName = $field_name;
    return $this;
  }

  /**
   * Get date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   Date object.
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Set date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   Date object.
   */
  public function setDate(DrupalDateTime $date) {
    $this->date = $date;
    return $this;
  }

  /**
   * Check if event can be accessed before date.
   *
   * @return bool
   *   Can be accessed before date.
   */
  public function canAccessBefore() {
    return $this->accessBefore;
  }

  /**
   * Set if event can be accessed before date.
   *
   * @param bool $access_before
   *   Can be accessed before date.
   */
  public function setAccessBefore($access_before) {
    $this->accessBefore = $access_before;
    return $this;
  }

  /**
   * Check if event can be accessed after date.
   *
   * @return bool
   *   Can be accessed after date.
   */
  public function canAccessAfter() {
    return $this->accessAfter;
  }

  /**
   * Set if event can be accessed after date.
   *
   * @param bool $access_after
   *   Can be accessed after date.
   */
  public function setAccessAfter($access_after) {
    $this->accessAfter = $access_after;
    return $this;
  }

}
