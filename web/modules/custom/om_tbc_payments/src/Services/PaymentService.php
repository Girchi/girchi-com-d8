<?php

namespace Drupal\om_tbc_payments\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\language\ConfigurableLanguageManager;
use GuzzleHttp\Client;
use WeAreDe\TbcPay\TbcPayProcessor;

/**
 * Service for TBC Payments.
 */
class PaymentService {
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
   * Language.
   *
   * @var \Drupal\language\ConfigurableLanguageManager
   */
  protected $languageManager;


  /**
   * Language.
   *
   * @var \WeAreDe\TbcPay\TbcPayProcessor
   */
  protected $tbcPayProcessor;

  /**
   * Guzzle.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Constructor for service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger.
   * @param \Drupal\language\ConfigurableLanguageManager $languageManager
   *   LanguageManager.
   * @param \GuzzleHttp\Client $client
   *   Guzzle.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactoryInterface $loggerFactory,
                              ConfigurableLanguageManager $languageManager,
                              Client $client
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $loggerFactory;
    $this->languageManager = $languageManager;
    $this->client = $client;
    $this->tbcPayProcessor = new TbcPayProcessor(
        "test",
        "test",
        "test"
    );
  }

  /**
   * Wrapper function to create transaction ID.
   *
   * @param int $amount
   *   Amount of GEL.
   * @param string $description
   *   Description for transaction.
   *
   * @return array
   *   Array with transaction id.
   */
  public function generateTransactionId($amount, $description) {

    if ($this->languageManager->getCurrentLanguage()->getId() === 'ka') {
      $lang = "GE";
    }
    else {
      $lang = "EN";
    }

    $this->tbcPayProcessor->amount = $amount * 100;
    $this->tbcPayProcessor->currency = 981;
    $this->tbcPayProcessor->description = $description;
    $this->tbcPayProcessor->language = $lang;

    return $this->tbcPayProcessor->sms_start_transaction();
  }

  /**
   * Make redirect to ufc.
   *
   * @param string $id
   *   Transaction ID.
   *
   * @return mixed
   *   Redirect.
   */
  public function makePayment($id) {

    // TODO see results and build logic.
    $response = $this->client->post('https://securepay.ufc.ge/ecomm2/ClientHandler', [
      'body' => [
        'trans_id' => $id,
      ],
    ]);

    return $response;
  }

  /**
   * Function for close day for TBC.
   */
  public function closeDay() {
    // TODO cron job.
    $response = $this->tbcPayProcessor->close_day();
    return Json::encode($response);
  }

  /**
   * Function for getting result of payment.
   *
   * @param string $id
   *   Transaction ID.
   *      * //   * @return array      * //   *   Result.
   */
  public function getPaymentResult($id) {
    // TODO main logic.
    // $result = $this->tbcPayProcessor->get_transaction_result($id);
  }

}
