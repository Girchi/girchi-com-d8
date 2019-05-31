<?php


namespace Drupal\girchi_utils\Plugin\Block;


use Drupal;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;




/**
 * Provides a 'Front news block' block.
 *
 * @Block(
 *  id = "front_news_block",
 *  admin_label = @Translation("Front news block"),
 * )
 */
class FrontNewsBlock extends BlockBase
{
    public function blockForm($form, FormStateInterface $form_state)
    {
        $vid = 'news_categories';
        $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
        $term_data = [];
        foreach ($terms as $term) {
            $term_data[$term->tid] = $term->name;
        }
        $term_data = ['all' => t('All')] + $term_data;
        $form['category_select'] = [
            '#type' => 'select',
            '#title' => $this->t('Select category'),
            '#options' => $term_data,
            '#default_value' =>  isset($this->configuration['category_select']) ? $this->configuration['category_select'] : 1
        ];
        return $form;
    }


    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['category_select'] = $form_state->getValue('category_select');
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $category_id = $this->configuration['category_select'];
        $em = \Drupal::entityTypeManager();

        /** @var NodeStorage $node_storage */
        $node_storage = $em->getStorage('node');
        if($category_id == 'all') {
            $lastest_articles = $node_storage->getQuery()
                ->condition('type', 'article')
                ->condition('status', 1)
                ->sort('created',"DESC")
                ->range(0,10)
                ->execute();
        } else {
            $lastest_articles = $node_storage->getQuery()
                ->condition('type', 'article')
                ->condition('status', 1)
                ->condition('field_category', $category_id, '=')
                ->sort('created',"DESC")
                ->range(0,10)
                ->execute();
        }



        if (!empty($lastest_articles)) {

            $articles = Node::loadMultiple($lastest_articles);
            krsort($articles);

            return array(
                '#theme' => 'front_page_articles',
                '#articles' => $articles,

            );
        }else {
            return array(
                '#theme' => 'top_videos'
            );
        }



    }

    public function getCacheMaxAge()
    {
        // set block cache max age 3 hours and then invalidate.
        return 10800;
    }
}