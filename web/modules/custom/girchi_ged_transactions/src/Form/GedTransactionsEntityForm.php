<?php

namespace Drupal\girchi_ged_transactions\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Ged transactions entity edit forms.
 *
 * @ingroup girchi_ged_transactions
 */
class GedTransactionsEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\girchi_ged_transactions\Entity\GedTransactionsEntity */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Ged transactions entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Ged transactions entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.ged_transactions_entity.canonical', ['ged_transactions_entity' => $entity->id()]);
  }

}
