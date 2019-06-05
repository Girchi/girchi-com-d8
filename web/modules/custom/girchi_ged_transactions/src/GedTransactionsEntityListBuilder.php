<?php

namespace Drupal\girchi_ged_transactions;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Ged transactions entity entities.
 *
 * @ingroup girchi_ged_transactions
 */
class GedTransactionsEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Ged transactions entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.ged_transactions_entity.edit_form',
      ['ged_transactions_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
