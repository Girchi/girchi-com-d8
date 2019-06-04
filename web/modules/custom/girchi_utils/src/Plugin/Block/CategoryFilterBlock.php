<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use Drupal;
/**
 * Provides a 'CategoryFilterBlock' block.
 *
 * @Block(
 *  id = "category_filter_block",
 *  admin_label = @Translation("Category filter block"),
 * )
 */
class  CategoryFilterBlock extends BlockBase
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $categories_tree =  Drupal::service('girchi_utils.taxonomy_term_tree')->load('news_categories');
        $current_category = \Drupal::request()->query->get('category');
        $path = \Drupal::service('path.alias_manager')->getPathByAlias('/media/news/14-ad-huic-melior-ymo');

        if(preg_match('/node\/(\d+)/', $path, $matches)) {
          $node = \Drupal\node\Entity\Node::load($matches[1]);
          $current_category = $node->get('field_category')[0]->entity->id();
        }

        return array(
                '#theme' => 'categories_block',
                '#categories' => $categories_tree,
                '#current_category' => $current_category,
            );

    }

    /**
     * {@inheritdoc}
     */
    public function getCacheContexts() {
      return Cache::mergeContexts(parent::getCacheContexts(), ['url.query_args']);
    }
}
