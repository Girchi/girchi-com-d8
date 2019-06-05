<?php

/**
 * @file
 * Contains ged_transactions_entity.page.inc.
 *
 * Page callback for Ged transactions entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Ged transactions entity templates.
 *
 * Default template: ged_transactions_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ged_transactions_entity(array &$variables) {
  // Fetch GedTransactionsEntity Entity Object.
  $ged_transactions_entity = $variables['elements']['#ged_transactions_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
