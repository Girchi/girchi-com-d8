<?php

namespace Drupal\girchi_utils\Plugin\Block;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use Drupal;
use Drupal\taxonomy\Entity\Term;

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
        $tags_tree = [];
        $query = \Drupal::entityQuery('taxonomy_term')
            ->condition('vid', 'tags')
            ->condition('field_featured', '1')
            ->range(0,10)
            ->condition('status', 1);

        $tids = $query->execute();
        if(!empty($tids) && count($tids) < 10) {
            $query = \Drupal::entityQuery('taxonomy_term')
                ->condition('vid', 'tags')
                ->condition('tid', $tids, 'NOT IN')
                ->range(0,10 - count($tids))
                ->condition('status', 1);
            $additional_tids = $query->execute();
            $tids = array_merge($tids, $additional_tids);
        } elseif (empty($tids)) {
            $query = \Drupal::entityQuery('taxonomy_term')
                ->condition('vid', 'tags')
                ->range(0,10)
                ->condition('status', 1);
            $tids = $query->execute();
        }

        if(!empty($tids)) {
            $terms = Term::loadMultiple($tids);
            foreach ($terms as $term) {
                $tags_tree[] = ['tid' => $term->id(),'name' => $term->getName()];
            }
        }

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
