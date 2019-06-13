<?php

namespace Drupal\girchi_utils;

class UpdatePageViews
{
  private static function _apiCall($url)
  {
    $apiBaseUrl = 'https://www.googleapis.com';
    $accountId = '186382891';
    $oauthToken = 'ya29.GmQnB4IjND-Clj9M7rhBeBKP4SX0gRE2wzOiqRO4qXCN56W6OzO8jZLAJvBzBFfzHl92vqlmdSL3t4l2c_SIJrdhIUIClfzFIWLXEgRICkIZ2DOVYcKrVCHlJZd-R1NfW1CTg_0D';
    $currentDate = date('Y-m-d');

    $apiUrl = "{$apiBaseUrl}/analytics/v3/data/ga?ids=ga%3A{$accountId}&dimensions=ga:pagePath&metrics=ga:pageviews&filters=ga:pagePath=={$url}&start-date=2005-01-01&end-date={$currentDate}&access_token={$oauthToken}";

    $apiResult = @file_get_contents($apiUrl);

    $apiResult = json_decode($apiResult, 1);

    $return = isset($apiResult["rows"][0][1]) ? $apiResult["rows"][0][1] : null;

    return $return;
  }

  public static function updateViews($nids, &$context)
  {
    $message = t('Updating Views...');
    $results = array();
    $pathAliasService = \Drupal::service('path.alias_manager');
    /** @var \Drupal\Core\Database\Connection $database */
    $database = \Drupal::service('database');


    foreach ($nids as $nid) {
      $url = $pathAliasService->getAliasByPath('/node/' . $nid);

      $viewsCount = self::_apiCall('/about');

      if ($viewsCount !== null) {
        $updateQuery = $database->query(
          "UPDATE `girchidrpl_node_counter` SET `totalcount` = :views_count WHERE `nid` = :nid;",
          [
            ':views_count' => $viewsCount,
            ':nid' => $nid,
          ]
        );

        $updateQuery = $updateQuery->execute();

        if (!$updateQuery) {
          $insertQuery = $database->query(
            "INSERT INTO `girchidrpl_node_counter` (`nid`, `totalcount`, `daycount`, `timestamp`) VALUES (:nid, :views_count, :views_count, :curent_time);",
            [
              ':views_count' => $viewsCount,
              ':nid' => $nid,
              ':curent_time' => time(),
            ]
          );

          $insertQuery = $insertQuery->execute();
        }
      }

      $results[] = $insertQuery;
    }

    $context['message'] = $message;
    $context['results'] = $results;
  }

  public function updateViewsFinishedCallback($success, $results, $operations)
  {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One node views has been updated.', '@count nodes views has been updated.'
      );
    } else {
      $message = t('Finished with an error.');
    }

    \Drupal::messenger()->addMessage($message);

    dump($results);die;
  }


}