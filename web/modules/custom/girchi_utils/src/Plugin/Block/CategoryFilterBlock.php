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

        return array(
                '#theme' => 'categories_block',
                '#categories' => $categories_tree,
            );

    }

    public function getCacheMaxAge()
    {
        // set block cache max age 3 hours and then invalidate.
        return 10800;
    }



}
