<?php

namespace Drupal\girchi_donations\Plugin\QueueWorker;

use Carbon\Carbon;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\girchi_donations\Utils\DonationUtils;
use Drupal\girchi_donations\Utils\GedCalculator;
use Drupal\om_tbc_payments\Services\PaymentService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process RegularDonation tasks.
 *
 * @QueueWorker(
 *   id = "regular_donation_processor",
 *   title = @Translation("Process regular donation"),
 *   cron = {"time" = 25}
 * )
 */
class RegularDonationProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;


  /**
   * Drupal\om_tbc_payments\Services\PaymentService definition.
   *
   * @var \Drupal\om_tbc_payments\Services\PaymentService
   */
  private $omediaPayment;

  /**
   * Drupal\girchi_donations\Utils\DonationUtils definition.
   *
   * @var \Drupal\girchi_donations\Utils\DonationUtils
   */
  private $donationUtils;

  /**
   * Drupal\girchi_donations\Utils\GedCalculator definition.
   *
   * @var \Drupal\girchi_donations\Utils\GedCalculator
   */
  private $gedCalculator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory,
                              PaymentService $omediaPayment,
                              DonationUtils $donationUtils,
                              GedCalculator $gedCalculator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
    $this->omediaPayment = $omediaPayment;
    $this->donationUtils = $donationUtils;
    $this->gedCalculator = $gedCalculator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('om_tbc_payments.payment_service'),
      $container->get('girchi_donations.donation_utils'),
      $container->get('girchi_donations.ged_calculator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    try {
      /** @var  \Drupal\girchi_donations\Entity\RegularDonation $data */

      $this->loggerChannelFactory->get('girchi_donations')
        ->info(sprintf('Executing  regular donation for: %s', $data->getOwner()
          ->get('name')->value));

      $ged_t_storage = $this->entityTypeManager->getStorage('ged_transaction');
      $frequency = $data->get('frequency')->value;
      /** @var \DateTime $date */
      $date = $data->get('next_payment_date')->value;
      /** @var \Carbon\Carbon $carbon_date */
      $carbon_date = Carbon::createFromFormat(DateTimeItemInterface::DATE_STORAGE_FORMAT, $date);
      $next_payment_date = $carbon_date->addMonths($frequency)
        ->toDateTime()
        ->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE))
        ->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
      if ($data->getStatus() === 'ACTIVE') {
        $card_id = $data->get('card_id')->value;
        $type = $data->get('type')->value;
        if ($type === '1') {
          $target_id = $data->get('aim_id')->target_id;
        }
        else {
          $target_id = $data->get('politician_id')->target_id;
        }
        $result = $this->omediaPayment->executePayment(
          $card_id,
          $data->get('amount')->value,
          'Regular donation'
        );
        if ($result === NULL) {
          $this->loggerChannelFactory->get('girchi_donations')->info('Error while executing payment');
          return;
        }
        else {
          $trans_id = $result['transaction_id'];
          $resp_result = $result['code'];
          if ($resp_result === '000') {
            $status = 'OK';
            $ged_t = $ged_t_storage->create([
              'user_id' => "1",
              'user' => $data->getOwnerId(),
              'ged_amount' => $this->gedCalculator->calculate($data->get('amount')->value),
              'title' => 'Donation',
              'name' => 'Donation',
              'status' => TRUE,
              'Description' => 'Transaction was created by donation',
            ]);
            $ged_t->save();
          }
          else {
            $status = 'FAILED';
          }
          /** @var \Drupal\girchi_donations\Entity\Donation $donation */
          $donation = $this->donationUtils->addDonationRecord(
            $type,
            [
              'trans_id' => $trans_id,
              'user_id' => $data->getOwnerId(),
              'amount' => $data->get('amount')->value,
              'status' => $status,
              'field_regular_donation' => $data->id(),
              'field_donation_type' => 1,
            ], $target_id);
          $this->loggerChannelFactory->get('girchi_donations')->info(
            sprintf(
              'Donation status was updated to: %s , user: %s',
              $status,
              $data->getOwner()->get('name')->value));
          /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $donations */
          $donations = $data->get('field_donations');
          $donations->appendItem($donation->id());
        }

        $this->loggerChannelFactory->get('girchi_donations')->info(
          sprintf(
            'Regular donation execution finished for user: %s , status:%s',
            $data->getOwner()->get('name')->value,
            $status));
      }
      $data->set('next_payment_date', $next_payment_date);
      $data->save();

    }
    catch (EntityStorageException $e) {
      $this->loggerChannelFactory->get('girchi_donations')
        ->error($e->getMessage());
    }

  }

}