<?php

namespace Drupal\girchi_donations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\girchi_donations\Utils\DonationUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SingleDonationForm.
 */
class SingleDonationForm extends FormBase {

  /**
   * Utils service.
   *
   * @var \Drupal\girchi_donations\Utils\DonationUtils
   */
  private $donationUtils;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new UserController object.
   *
   * @param \Drupal\girchi_donations\Utils\DonationUtils $donationUtils
   *   Donation Utils.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger.
   */
  public function __construct(DonationUtils $donationUtils, MessengerInterface $messenger) {
    $this->donationUtils = $donationUtils;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('girchi_donations.donation_utils'),
        $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'single_donation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $politicians = $this->donationUtils->getPoliticians();
    $options = $this->donationUtils->getTerms();

    $form['amount'] = [
      '#type' => 'number',
      '#attributes' => [
        'class' => [
          'form-control form-control-lg',
        ],
        'placeholder' => $this->t('Enter amount of GEL'),
      ],
      '#required' => TRUE,
      '#weight' => '0',
    ];
    $form['donation_aim'] = [
      '#type' => 'select',
      '#options' => $options,
      '#required' => FALSE,
      '#empty_value' => '',

    ];
    $form['politicians'] = [
      '#type' => 'select',
      '#options' => $politicians,
      '#required' => FALSE,
      '#empty_value' => '',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#attributes' => [
        'class' => [
          'btn btn-lg btn-block btn-warning text-uppercase mt-4',
        ],
      ],
      '#value' => $this->t('Donate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $donation_aim = $form_state->getValue('donation_aim');
    $politician = $form_state->getValue('politicians');
    if (empty($donation_aim) && empty($politician)) {
      $this->messenger->addError('Please choose Donation aim OR Donation to politician');
      $form_state->setRebuild();
    }
  }

}
