<?php

namespace Drupal\girchi_ged_transactions;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SummaryGedCalculationService.
 */
class SummaryGedCalculationService {

  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PartyListCalculatorService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function summaryGedCalculation(){
    $users = NULL;
    $gedValue = 0;
    $arr = [];
    try {
      $user_storage = $this->entityTypeManager->getStorage('user');
      $user_ids = $user_storage->getQuery()
        ->condition('field_ged', '0', '>')
        ->execute();
      /** @var \Drupal\user\Entity\User $users */
      $users = $user_storage->loadMultiple($user_ids);

      foreach ($users as $user) {
         $gedArray = $user->get('field_ged')->getValue();
         $gedValue = $gedValue + (int)$gedArray[0]['value'];
      }

      $gedPercentage = $gedValue / 50000000;

      $arr = [
        'gedValue' => $gedValue,
        'gedPercentage' => $gedPercentage,
      ];

    }
    catch (InvalidPluginDefinitionException $e) {
      \Drupal::logger('girchi_ged_transactions')->error($e->getMessage());

    }
    catch (PluginNotFoundException $e) {
      \Drupal::logger('girchi_ged_transactions')->error($e->getMessage());

    }
    return $arr;
  }




}
