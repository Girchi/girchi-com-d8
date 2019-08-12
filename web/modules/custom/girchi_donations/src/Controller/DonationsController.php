<?php

namespace Drupal\girchi_donations\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\girchi_donations\Entity\Donation;
use Drupal\girchi_donations\Utils\GedCalculator;
use Drupal\om_tbc_payments\Services\PaymentService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DonationsController.
 */
class DonationsController extends ControllerBase {

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Payment service.
   *
   * @var \Drupal\om_tbc_payments\Services\PaymentService
   */
  protected $omediaPayment;

  /**
   * Ged calculator.
   *
   * @var GedCalculator
   */
  public $gedCalculator;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   * @param \Drupal\om_tbc_payments\Services\PaymentService $omediaPayment
   *   Payments.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   ET manager
   * @param \Drupal\girchi_donations\Utils\GedCalculator $gedCalculator
   *   GedCalculator
   */
  public function __construct(ConfigFactory $configFactory, PaymentService $omediaPayment, EntityTypeManager $entityTypeManager,GedCalculator $gedCalculator) {
    $this->configFactory = $configFactory;
    $this->omediaPayment = $omediaPayment;
    $this->entityTypeManager = $entityTypeManager;
    $this->gedCalculator= $gedCalculator;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('om_tbc_payments.payment_service'),
      $container->get('entity_type.manager'),
      $container->get('girchi_donations.ged_calculator')
    );
  }

  /**
   * Index.
   *
   * @return array
   *   Return array with template and variables
   */
  public function index() {
    $config = $this->configFactory->get('om_site_settings.site_settings');
    $right_block = $config->get('donation_right_block')['value'];
    $form_single = $this->formBuilder()
      ->getForm("Drupal\girchi_donations\Form\SingleDonationForm");
    $form_multiple = $this->formBuilder()
      ->getForm("Drupal\girchi_donations\Form\MultipleDonationForm");

    return [
      '#type' => 'markup',
      '#theme' => 'girchi_donations',
      '#form_single' => $form_single,
      '#form_multiple' => $form_multiple,
      '#right_block' => $right_block,
    ];
  }

  /**
   * Route for final destination of donation.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return mixed
   */
  public function finishDonation(Request $request) {
    try {
      $params = $request->request;
      $trans_id = $params->get('trans_id');
      $storage = $this->entityTypeManager()->getStorage('donation');
      /** @var Donation $donation */
      $donation = reset($storage->loadByProperties(['trans_id'=>$trans_id]));
      $ged_manager = $this->entityTypeManager()->getStorage('ged_transaction');

      if (!$trans_id) {
        $this->getLogger('girchi_donations')->error('Trans ID is missing.');
        return new JsonResponse('Transaction ID is missing', Response::HTTP_BAD_REQUEST);
      }

      $result = $this->omediaPayment->getPaymentResult($trans_id);
      if ($result['RESULT_CODE'] === "000") {
        if($donation->getStatus() !== 'OK'){
          $donation->setStatus('OK');
          $donation->save();
          $this->getLogger('girchi_donations')->info("Status was Updated to OK, ID:$trans_id.");
          if($this->currentUser()->id() !== 0){
            $gel_amount = $donation->getAmount();
            $ged_amount = $this->gedCalculator->calculate($gel_amount);
            $ged_manager->create([
              'user' => $this->currentUser()->id(),
              'ged_amount' => $ged_amount,
              'title' => 'Donation',
              'name' => 'Donation',
              'status' => TRUE,
              'Description' => 'Transaction was created by donation',
              'user_id' => $this->currentUser()->id(),
            ])
              ->save();
              $auth = true;
          }else {
            $auth = false;
          }

          $this->getLogger('girchi_donations')->info("Ged transaction was made.");
          $this->getLogger('girchi_donations')->info("Payment was successful, ID:$trans_id.");
          return [
            '#type' => 'markup',
            '#theme' => 'girchi_donations_success',
            '#amount' => $ged_amount,
            '#auth' => $auth
          ];
        }else{
          return $this->redirect('user.page');
        }
      }
      else {
        $code = $result['RESULT_CODE'];
        $donation->setStatus('FAILED');
        $donation->save();
        $this->getLogger('girchi_donations')
          ->error("Payment failed code:$code, ID:$trans_id.");
        return [
          '#type' => 'markup',
          '#theme' => 'girchi_donations_fail',
        ];
      }
    } catch (\Exception $e) {
      $this->getLogger($e->getMessage());
    }
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_donations_fail',
    ];
  }

  /**
   * Route for donation technical failure.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function failDonation(Request $request) {
    $params = $request->request;
    $trans_id = $params->get('trans_id');
    if (!$trans_id) {
      $this->getLogger('girchi_donations')->error("Trans ID is missing.");
      return new JsonResponse('Transaction ID is missing', Response::HTTP_BAD_REQUEST);
    }

    $result = $this->omediaPayment->getPaymentResult($trans_id);
    $code = $result['RESULT_CODE'];
    $storage = $this->entityTypeManager()->getStorage('donation');
    /** @var Donation $donation */
    $donation = $storage->loadByProperties(['trans_id'=>$trans_id]);
    $donation->setStatus('FAILED');
    $this->getLogger('girchi_donations')->info("Payment failed code:$code,  ID:$trans_id.");
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_donations_fail',
    ];
  }

}
