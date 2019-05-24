<?php
namespace Drupal\om_site_settings\Form;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManager;
use Drupal\Core\Config\ExtensionInstallStorage;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Class SiteSettingsForm.
 *
 * @package Drupal\om_site_settings\Form
 */
class SiteSettingsForm extends ConfigFormBase
{
    /**
     * Drupal\Core\Config\ConfigManager definition.
     *
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * Drupal\Core\Config\ExtensionInstallStorage definition.
     *
     * @var ExtensionInstallStorage
     */
    protected $configStorageSchema;
    /**
     * Drupal\Core\Language\LanguageManager definition.
     *
     * @var LanguageManager
     */
    protected $languageManager;

    /**
     * Constructs a new GeneralSettingsForm object.
     * @param ConfigFactoryInterface $config_factory
     * @param ConfigManager $config_manager
     * @param ExtensionInstallStorage $config_storage_schema
     * @param LanguageManager $language_manager
     */
    public function __construct(
        ConfigFactoryInterface $config_factory,
        ConfigManager $config_manager,
        ExtensionInstallStorage $config_storage_schema,
        LanguageManager $language_manager
    )
    {
        parent::__construct($config_factory);
        $this->configManager = $config_manager;
        $this->configStorageSchema = $config_storage_schema;
        $this->languageManager = $language_manager;
    }
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('config.factory'),
            $container->get('config.manager'),
            $container->get('config.storage.schema'),
            $container->get('language_manager')
        );
    }
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'om_site_settings.site_settings',
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'om_site_settings_site_settings_form';
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('om_site_settings.site_settings');

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
            '#open' => true,
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
            '#open' => true,
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
            '#default_value' => $config->get('social_media_instagram')
        ];
        $form['social_media']['social_media_youtube'] = [
            '#description' => t('Enter your youtube channel url'),
            '#type' => 'textfield',
            '#title' => t('Youtube chanel url'),
            '#default_value' => $config->get('social_media_youtube')
        ];

        return parent::buildForm($form, $form_state);
    }
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);

    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $fields = [
            'contact_info_phone',
            'contact_info_email',
            'social_media_facebook',
            'social_media_twitter',
            'social_media_instagram',
            'social_media_youtube',
        ];

        foreach ($fields as $field_key) {
            $this->config('om_site_settings.site_settings')
                ->set($field_key, $form_state->getValue($field_key))
                ->save();
        }
        drupal_flush_all_caches();
    }
}