<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
/**
 * Provides a 'UserProfile' block.
 *
 * @Block(
 *  id = "user_profile",
 *  admin_label = @Translation("User profile"),
 * )
 */
class UserProfile extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state)
  {

    $form['user_profile_ged'] = [
        '#type' => 'checkbox',
        '#title' => 'Show Ged',
        '#default_value' => isset($this->configuration['ged']) ? $this->configuration['ged'] : 1  ,
    ];
    $form['user_profile_member'] = [
        '#type' => 'checkbox',
        '#title' => 'Show Member',
        '#default_value' => isset($this->configuration['member']) ? $this->configuration['member'] : 1
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {

    $this->configuration['ged'] = $form_state->getValue('user_profile_ged');
    $this->configuration['member'] = $form_state->getValue('user_profile_member');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $currentUserId = \Drupal::currentUser()->id();
    $currentUser = User::load($currentUserId);
    $currentUserFirstName = $currentUser->get('field_first_name')->value;
    $currentUserLastName = $currentUser->get('field_last_name')->value;
    $currentUserGed = $currentUser->get('field_ged')->value;
    $avatarEntity = $currentUser->get('user_picture')->entity;

    if($avatarEntity) {
      $currentUserAvatar = $avatarEntity->url();
    }else{
      //for testing
      $currentUserAvatar = 'https://banner2.kisspng.com/20180705/kwk/kisspng-computer-icons-user-avatar-clip-art-simple-icon-5b3dc2f094d281.3228171615307742566096.jpg';
    }

    $build = [];
    $build['user_profile']['#markup'] = 'Implement UserProfile.';

    return array(
      '#theme' => 'user_profile',
      '#title' => t('User Profile'),
      '#description' => 'User profile block',
      '#ged' => $this->configuration['ged'],
      '#member'=> $this->configuration['member'],
      '#user_id' => $currentUserId,
      '#user_first_name' => $currentUserFirstName,
      '#user_last_name' => $currentUserLastName,
      '#user_ged' => $currentUserGed,
      '#user_profile_picture' => $currentUserAvatar,
    );
  }

}
