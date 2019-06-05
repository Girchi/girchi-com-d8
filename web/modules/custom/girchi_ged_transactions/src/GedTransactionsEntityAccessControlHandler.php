<?php

namespace Drupal\girchi_ged_transactions;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Ged transactions entity entity.
 *
 * @see \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntity.
 */
class GedTransactionsEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished ged transactions entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published ged transactions entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit ged transactions entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete ged transactions entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add ged transactions entity entities');
  }

}
