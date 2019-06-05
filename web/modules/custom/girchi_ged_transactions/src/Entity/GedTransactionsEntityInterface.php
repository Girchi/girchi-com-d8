<?php

namespace Drupal\girchi_ged_transactions\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Ged transactions entity entities.
 *
 * @ingroup girchi_ged_transactions
 */
interface GedTransactionsEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Ged transactions entity name.
   *
   * @return string
   *   Name of the Ged transactions entity.
   */
  public function getName();

  /**
   * Sets the Ged transactions entity name.
   *
   * @param string $name
   *   The Ged transactions entity name.
   *
   * @return \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntityInterface
   *   The called Ged transactions entity entity.
   */
  public function setName($name);

  /**
   * Gets the Ged transactions entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Ged transactions entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Ged transactions entity creation timestamp.
   *
   * @param int $timestamp
   *   The Ged transactions entity creation timestamp.
   *
   * @return \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntityInterface
   *   The called Ged transactions entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Ged transactions entity published status indicator.
   *
   * Unpublished Ged transactions entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Ged transactions entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Ged transactions entity.
   *
   * @param bool $published
   *   TRUE to set this Ged transactions entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntityInterface
   *   The called Ged transactions entity entity.
   */
  public function setPublished($published);

}
