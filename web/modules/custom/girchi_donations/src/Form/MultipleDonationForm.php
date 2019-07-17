<?php

namespace Drupal\girchi_donations\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\girchi_donations\Utils\DonationUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SingleDonationForm.
 */
class MultipleDonationForm extends FormBase {


  /**
   * Utils service.
   *
   * @var \Drupal\girchi_donations\Utils\DonationUtils
   */
  private $donationUtils;

  /**
   * Constructs a new UserController object.
   *
   * @param \Drupal\girchi_donations\Utils\DonationUtils $donationUtils
   *   Donation Utils.
   */
  public function __construct(DonationUtils $donationUtils) {
    $this->donationUtils = $donationUtils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('girchi_donations.donation_utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multiple_donation_form';
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
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['periodicity'] = [
      '#type' => 'select',
      '#options' => [
        '1' => 'Month',
        '3' => '3 Month',
        '6' => '6 Month',
      ],
      '#required' => TRUE,
    ];
    $form['date'] = [
      '#type' => 'number',
      '#attributes' => [
        'class' => [
          'form-control form-control-lg',
        ],
        'placeholder' => $this->t('Date'),
      ],
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
