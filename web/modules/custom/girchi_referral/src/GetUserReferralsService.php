<?php

namespace Drupal\girchi_referral;

use Drupal\user\Entity\User;

/**
 * Class GetUserReferralsService.
 */
class GetUserReferralsService {

  /**
   * GetUserReferrals.
   */
  public function getUserReferrals($uid) {
    // Count number of referrals.
    $countReferrals = \Drupal::entityQuery('user')
      ->condition('field_referral', $uid)
      ->count()
      ->execute();

    // Get referrals.
    $referralsId = \Drupal::entityQuery('user')
      ->condition('field_referral', $uid)
      ->sort('field_ged', 'DESC')
      ->range(0, 5)
      ->execute();
    $referralsArray = User::loadMultiple($referralsId);

    $resultArr = [
      'referralCount' => $countReferrals,
      'referralUsers' => $referralsArray,
    ];

    return $resultArr;

  }

}
