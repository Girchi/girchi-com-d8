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
 * Provides a 'TagFilterBlock' block.
 *
 * @Block(
 *  id = "tag_filter_block",
 *  admin_label = @Translation("Tag filter block"),
 * )
 */
class  TagFilterBlock extends BlockBase
{


    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $tags_tree =  Drupal::service('girchi_utils.taxonomy_term_tree')->load('tags');

        return array(
                '#theme' => 'tags_block',
                '#tags' => $tags_tree,
            );

    }

    public function getCacheMaxAge()
    {
        // set block cache max age 3 hours and then invalidate.
        return 10800;
    }



}
