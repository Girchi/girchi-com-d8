<?php

namespace Drupal\girchi_banking\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\girchi_banking\Entity\CreditCard;
use Drupal\om_tbc_payments\Services\PaymentService;

/**
 * Class BankingUtils.
 */
class BankingUtils {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Drupal\om_tbc_payments\Services\PaymentService definition.
   *
   * @var \Drupal\om_tbc_payments\Services\PaymentService
   */
  protected $omTbcPaymentsPaymentService;

  /**
   * Constructs a new BankingUtils object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EM.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger.
   * @param \Drupal\om_tbc_payments\Services\PaymentService $om_tbc_payments_payment_service
   *   Omedia TBC payment service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory, PaymentService $om_tbc_payments_payment_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $logger_factory;
    $this->omTbcPaymentsPaymentService = $om_tbc_payments_payment_service;
  }

  /**
   * Prepare credit card.
   *
   * @param string $trans_id
   *   TransactionID.
   * @param string $card_id
   *   Card ID for TBC execution.
   *
   * @return mixed
   *   Card or NULL.
   */
  public function prepareCard($trans_id, $card_id) {
    try {
      $card_storage = $this->entityTypeManager->getStorage('credit_card');
      if ($trans_id && $card_id) {
        $card = $card_storage->create([
          'trans_id' => $trans_id,
          'tbc_id' => $card_id,
          'status' => 'INITIAL',
        ]);
        $card->save();
        return $card;
      }
      else {
        return NULL;
      }
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
    }
    catch (EntityStorageException $e) {
      $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
    }

    return NULL;
  }

  /**
   * Parse and merge card data.
   *
   * @param \Drupal\girchi_banking\Entity\CreditCard $creditCard
   *   Credit card.
   * @param array $result
   *   Result from TBC.
   *
   * @return \Drupal\girchi_banking\Entity\CreditCard|null
   *   Credit card or null.
   */
  public function parseAndMerge(CreditCard $creditCard, array $result) {

    if (isset($result['CARD_NUMBER'], $result['RECC_PMNT_EXPIRY'])) {
      try {

        $creditCard->setType($this->guessType($result['CARD_NUMBER']));
        $creditCard->setExpiry($result['RECC_PMNT_EXPIRY']);
        $creditCard->setStatus('ACTIVE');
        $creditCard->setDigits($this->parseDigits($result['CARD_NUMBER']));
        $creditCard->save();

        return $creditCard;
      }
      catch (EntityStorageException $e) {
        $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
      }
      catch (\Exception $e) {
        $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
      }
      return NULL;
    }
    else {
      return NULL;
    }
  }

  /**
   * Function for guessing type.
   *
   * @param string $card_number
   *   String 4 or 5.
   *
   * @return string
   *   Visa or MC.
   */
  private function guessType($card_number) {
    $num = substr($card_number, 0, 1);
    if ($num == '4') {
      return 'VISA';
    }
    elseif ($num == '5') {
      return 'MC';

    }

    return NULL;
  }

  /**
   * Function for parsing curd number and get 4 digits.
   *
   * @param string $card_number
   *   Number of card.
   *
   * @return false|string
   *   Last 4 digits.
   */
  private function parseDigits($card_number) {
    return substr($card_number, -4);
  }

  /**
   * Check if user has active credit cards.
   *
   * @param string $uid
   *   User id.
   *
   * @return bool
   *   true or false.
   */
  public function hasAvailableCards($uid) {
    try {
      $card_storage = $this->entityTypeManager->getStorage('credit_card');
      $cards = $card_storage->getQuery()
        ->condition('user_id', $uid)
        ->condition('status', 'ACTIVE')
        ->execute();

      if (!empty($cards)) {
        return TRUE;
      }

      return FALSE;
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('girchi_banking')->error($e->getMessage());
    }

    return FALSE;
  }

}
