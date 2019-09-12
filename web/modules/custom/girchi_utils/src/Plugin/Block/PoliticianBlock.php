<?php

namespace Drupal\girchi_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'PoliticianBlock' block.
 *
 * @Block(
 *  id = "politician_block",
 *  admin_label = @Translation("Politician block"),
 * )
 */
class PoliticianBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $language = $this->languageManager->getCurrentLanguage()->getId();

    return [
      '#theme' => 'politician_block',
      '#language' => $language,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
