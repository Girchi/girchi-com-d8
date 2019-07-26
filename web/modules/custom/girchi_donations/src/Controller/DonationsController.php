<?php

namespace Drupal\girchi_donations\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Construct.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory')
    );
  }

  /**
   * Index.
   *
   * @return array
   *   Return array with template and variables
   */
  public function index() {
    // dump($this->GedCalculator->calculate(1000));die;.
    $config = $this->configFactory->get('om_site_settings.site_settings');
    $right_block = $config->get('donation_right_block')['value'];
    $form_single = $this->formBuilder()->getForm("Drupal\girchi_donations\Form\SingleDonationForm");
    $form_multiple = $this->formBuilder()->getForm("Drupal\girchi_donations\Form\MultipleDonationForm");

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
   */
  public function finishDonation() {
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_donations_success',
    ];
  }

  /**
   * Route for donation technical failure.
   */
  public function failDonation() {
    return [
      '#type' => 'markup',
      '#theme' => 'girchi_donations_fail',
    ];
  }

}
