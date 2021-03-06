<?php

namespace Drupal\om_site_settings\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Config\ExtensionInstallStorage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SiteSettingsForm.
 *
 * @package Drupal\om_site_settings\Form
 */
class SiteSettingsForm extends ConfigFormBase {
  /**
   * Drupal\Core\Config\ConfigManager definition.
   *
   * @var \Drupal\Core\Config\ConfigManager
   */
  protected $configManager;
  /**
   * Drupal\Core\Config\ExtensionInstallStorage definition.
   *
   * @var \Drupal\Core\Config\ExtensionInstallStorage
   */
  protected $configStorageSchema;
  /**
   * Drupal\Core\Language\LanguageManager definition.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Entity type manager.
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LoggerFactory.
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new GeneralSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Config\ConfigManager $config_manager
   * @param \Drupal\Core\Config\ExtensionInstallStorage $config_storage_schema
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   */
  public function __construct(
        ConfigFactoryInterface $config_factory,
        ConfigManager $config_manager,
        ExtensionInstallStorage $config_storage_schema,
        LanguageManager $language_manager,
        EntityTypeManagerInterface $entityTypeManager,
        LoggerChannelFactoryInterface $loggerFactory
    ) {
    parent::__construct($config_factory);
    $this->configManager = $config_manager;
    $this->configStorageSchema = $config_storage_schema;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerFactory = $loggerFactory->get('om_site_settings');
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory'),
          $container->get('config.manager'),
          $container->get('config.storage.schema'),
          $container->get('language_manager'),
          $container->get('entity_type.manager'),
          $container->get('logger.factory')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'om_site_settings.site_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'om_site_settings_site_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('om_site_settings.site_settings');
    try {
      $user_storage = $this->entityTypeManager->getStorage('user');
    } catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->error($e->getMessage());
    } catch (PluginNotFoundException $e) {
      $this->loggerFactory->error($e->getMessage());
    }

    $cache_life_options = [
      60 => '1 Minute',
      60 * 3 => '3 Minutes',
      60 * 5 => '5 Minutes',
      60 * 15 => '15 Minutes',
      60 * 30 => '30 Minutes',
      60 * 60 => '1 Hour',
      60 * 60 * 3 => '3 Hours',
      60 * 60 * 6 => '6 Hours',
      60 * 60 * 12 => '12 Hours',
      60 * 60 * 24 => '24 Hours',
    ];

    $form['contact_info'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Contact information'),
    ];
    $form['contact_info']['contact_info_phone'] = [
      '#description' => t('Enter your contact phone number'),
      '#type' => 'textfield',
      '#title' => t('Contact phone'),
      '#default_value' => $config->get('contact_info_phone'),
    ];
    $form['contact_info']['contact_info_email'] = [
      '#description' => t('Enter your contact email address'),
      '#type' => 'textfield',
      '#title' => t('Contact Email'),
      '#default_value' => $config->get('contact_info_email'),
    ];
    $form['social_media'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Social Media'),
    ];
    $form['social_media']['social_media_facebook'] = [
      '#description' => t('Enter your facebook page url'),
      '#type' => 'textfield',
      '#title' => t('Facebook page url'),
      '#default_value' => $config->get('social_media_facebook'),
    ];
    $form['social_media']['social_media_twitter'] = [
      '#description' => t('Enter your twitter page url'),
      '#type' => 'textfield',
      '#title' => t('Twitter profile url'),
      '#default_value' => $config->get('social_media_twitter'),
    ];
    $form['social_media']['social_media_instagram'] = [
      '#description' => t("Enter your instagram page url"),
      '#type' => 'textfield',
      '#title' => t('Instagram profile url'),
      '#default_value' => $config->get('social_media_instagram'),
    ];
    $form['social_media']['social_media_youtube'] = [
      '#description' => t('Enter your youtube channel url'),
      '#type' => 'textfield',
      '#title' => t('Youtube chanel url'),
      '#default_value' => $config->get('social_media_youtube'),
    ];

    $form['general_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('General settings'),
    ];

    $form['general_settings']['copyright_text'] = [
      '#description' => t('Enter copyright text'),
      '#type' => 'textfield',
      '#title' => t('Copyright text'),
      '#default_value' => $config->get('copyright_text'),
    ];

    $form['general_settings']['enable_user_ui'] = [
      '#description' => t('Display user login/register buttons'),
      '#type' => 'checkbox',
      '#title' => t('Display user interface'),
      '#default_value' => $config->get('enable_user_ui'),
    ];
    $form['general_settings']['politician_checkbox_text'] = [
      '#description' => t('Enter want to be politician text'),
      '#type' => 'text_format',
      '#title' => t('Want to be politician text'),
      '#default_value' => $config->get('politician_checkbox_text')['value'],
    ];
    $form['general_settings']['google_analytics_view_id'] = [
      '#description' => t('Enter google analytics view id'),
      '#type' => 'textfield',
      '#title' => t('Google analytics view id'),
      '#default_value' => $config->get('google_analytics_view_id'),
    ];
    $form['donation'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Donation settings'),
    ];
    $form['donation']['donation_right_block'] = [
      '#description' => t('Right block text'),
      '#type' => 'text_format',
      '#title' => t('Page text'),
      '#format' => 'full_html',
      '#default_value' => $config->get('donation_right_block')['value'],
      ];
    $form['extra_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Extra Settings'),
    ];
    $form['extra_settings']['createpass'] = [
      '#description' => t('Enter text for password set page'),
      '#type' => 'textarea',
      '#title' => t('Password set form'),
      '#default_value' => $config->get('createpass')
    ];
    $form['politician-rating'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Politician rating settings'),
    ];
    $form['politician-rating']['number_of_politicians'] = [
      '#type' => 'select',
      '#title' => t('Number of politicians'),
      '#options' => ['5' => 5, '10' => 10],
      '#default_value' => $config->get('number_of_politicians')
    ];
    $form['donation-aim'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Default notification receiver'),
    ];
    $form['donation-aim']['default_receiver'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      "#bundle" => "user",
      '#title' => t('Default receiver'),
      '#default_value' => !empty($config->get('default_receiver')) ? $user_storage->load($config->get('default_receiver')) : NULL,
    ];
    $form['party_list_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('Party list settings'),
    ];

    $form['party_list_settings']['party_list'] = [
      '#description' => t('Checkbox should be checked if you want to close party list'),
      '#type' => 'checkbox',
      '#title' => t('Close Party List'),
      '#default_value' => !(empty($config->get('party_list'))) ? $config->get('party_list') : FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $fields = [
      'contact_info_phone',
      'contact_info_email',
      'social_media_facebook',
      'social_media_twitter',
      'social_media_instagram',
      'social_media_youtube',
      'politician_checkbox_text',
      'enable_user_ui',
      'copyright_text',
      'google_analytics_view_id',
      'donation_right_block',
      'createpass',
      'number_of_politicians',
      'default_receiver',
      'party_list'
    ];

    foreach ($fields as $field_key) {
      $this->config('om_site_settings.site_settings')
        ->set($field_key, $form_state->getValue($field_key))
        ->save();
    }
    drupal_flush_all_caches();
  }
}
