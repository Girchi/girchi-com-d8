<?php

namespace Drupal\girchi_donations\Commands;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class GirchiDonationsCommands extends DrushCommands {


  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   ET manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $loggerFactory) {
    parent::__construct();
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Main command.
   *
   * @command girchi_donations:fix-relation
   * @aliases fix-relation
   */
  public function makeRelation() {
    try {
      $donations_array = [];
      $donation_storage = $this->entityTypeManager->getStorage('donation');
      $ged_t_storage = $this->entityTypeManager->getStorage('ged_transaction');
      $donation_entity_ids = $donation_storage->getQuery()
        ->condition('status', 'OK')
        ->condition('user_id', '0', '!=')
        ->sort('amount', 'DESC')
        ->execute();
      $ged_t_ids = $ged_t_storage->getQuery()
        ->condition('Description', 'Transaction was created by donation')
        ->condition('user_id', '0', '!=')
        ->condition('user', '0', '!=')
        ->sort('ged_amount', 'DESC')
        ->execute();
      $donation_entities = $donation_storage->loadMultiple($donation_entity_ids);
      $ged_ts = $ged_t_storage->loadMultiple($ged_t_ids);
      /** @var \Drupal\girchi_donations\Entity\Donation $donation */
      foreach ($donation_entities as $donation) {
        $uid = $donation->getUser()->id();
        $donations_array[$uid]['donations'][] = $donation;
      }
      /** @var \Drupal\girchi_ged_transactions\Entity\GedTransaction $transaction */
      foreach ($ged_ts as $transaction) {
        $uid = $transaction->get('user')->entity->id();
        if (isset($donations_array[$uid])) {
          $donations_array[$uid]['transaction'][] = $transaction->id();
        }
      }
      foreach ($donations_array as $array_item) {
        $d = $array_item['donations'];
        $t = $array_item['transaction'];
        if ($t !== NULL) {
          if (count($d) != count($t)) {
            array_pop($d);
          };
          $length = count($d);
          for ($i = 0; $i < $length; $i++) {
            $d[$i]->set('field_ged_transaction', $t[$i])->save();
          }
        }
      }

    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());
    }
  }

  /**
   * Main command.
   *
   * @command girchi_donations:restore-dates
   * @aliases restore-dates
   */
  public function restoreDates(InputInterface $input, OutputInterface $output) {
    try {
      $regd_storage = $this->entityTypeManager->getStorage('regular_donation');
      $group = $regd_storage->getQuery()
        ->orConditionGroup()
        ->condition('status', 'ACTIVE', '=')
        ->condition('status', 'PAUSED', '=');
      $regd_ids = $regd_storage->getQuery()
        ->condition($group)
        ->condition('next_payment_date', $this->getMothDays(), 'IN')
        ->execute();
      $regds = $regd_storage->loadMultiple($regd_ids);
      $progress_bar = new ProgressBar($output, count($regd_ids));

      // Starts and displays the progress bar.
      $progress_bar->start();
      /** @var \Drupal\girchi_donations\Entity\RegularDonation $regd */
      foreach ($regds as $regd) {
        $progress_bar->setMessage(sprintf("\n Fixing date for %s \n", $regd->getOwner()
          ->get('name')->value));

        $carbon_date = Carbon::createFromFormat(DateTimeItemInterface::DATE_STORAGE_FORMAT, $regd->get('next_payment_date')->value);
        $next_payment_date = $carbon_date->addMonths($regd->get('frequency')->value)
          ->toDateTime()
          ->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE))
          ->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
        $regd->set('next_payment_date', $next_payment_date);

        $regd->save();
        $progress_bar->advance();
      }

      $progress_bar->finish();

    }
    catch (\Exception $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());

    }

  }

  /**
   * Main command.
   *
   * @command girchi_donations:delete-regs
   * @aliases delete-regs
   */
  public function forceDelete(InputInterface $input, OutputInterface $output, $ids, $options = ['show' => FALSE]) {
    try {

      $ids = explode(',', $ids);
      if (!empty($ids)) {
        if ($options['show']) {
          $user_storage = $this->entityTypeManager->getStorage('user');
          $users = $user_storage->loadMultiple($ids);

          /** @var \Drupal\user\Entity\User $user */
          foreach ($users as $user) {
            $output->writeln(sprintf("Deleting regular donations for %s", $user->getAccountName()));
          }
        }

        $regd_storage = $this->entityTypeManager->getStorage('regular_donation');
        $regd_ids = $regd_storage->getQuery()
          ->condition('user_id', $ids, 'IN')
          ->execute();

        if (!empty($regd_ids)) {
          $progress_bar = new ProgressBar($output, count($regd_ids));
          $regd_entities = $regd_storage->loadMultiple($regd_ids);
          $progress_bar->start();
          /** @var \Drupal\girchi_donations\Entity\RegularDonation $regd_entity */
          foreach ($regd_entities as $regd_entity) {
            $regd_entity->delete();
            $progress_bar->advance();
          }
          $progress_bar->finish();
        }
      }

    }
    catch (\Exception $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());

    }
  }

  /**
   * Main command.
   *
   * @command girchi_donations:fix-aims
   * @aliases fix-aims
   */
  public function fixAims() {
    try {
      $reg_donations_storage = $this->entityTypeManager
        ->getStorage('regular_donation');
      $reg_donations_ids = $reg_donations_storage->getQuery()
        ->condition('status', 'FAILED', '!=')
        ->condition('status', 'INITIAL', '!=')
        ->execute();

      $term = $this->entityTypeManager
        ->getStorage('taxonomy_term')->load(1035);
      $reg_donations = $reg_donations_storage->loadMultiple($reg_donations_ids);
      foreach ($reg_donations as $reg_donation) {
        if (!$reg_donation->get('aim_id')->entity) {
          $reg_donation->set('aim_id', $term);
          $reg_donation->save();
        }
      }
    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('girchi_donations')->error($e->getMessage());
    }
  }

  /**
   * Helper.
   */
  public function getMothDays() {
    $response = [];
    $period = CarbonPeriod::create(Carbon::createFromDate(2020, 2, 1), Carbon::yesterday());

    /** @var \Carbon\Carbon $day */
    foreach ($period as $day) {
      $response[] = $day->toDateTime()
        ->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    }

    return $response;
  }

}
