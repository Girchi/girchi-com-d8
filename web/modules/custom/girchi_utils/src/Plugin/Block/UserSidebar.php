<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'UserSidebar' block.
 *
 * @Block(
 *  id = "user_sidebar",
 *  admin_label = @Translation("User sidebar"),
 * )
 */
class UserSidebar extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['user_sidebar']['#markup'] = 'Implement UserSidebar.';

    $current_user = \Drupal::currentUser()->id();

    return array(
      '#theme' => 'user_sidebar',
      '#title' => t('User sidebar'),
      '#description' => 'sidebar for users profile',
      '#current_user' => $current_user,
    );
  }

}
