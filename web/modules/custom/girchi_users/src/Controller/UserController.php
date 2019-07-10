<?php

namespace Drupal\girchi_users\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\social_auth\SocialAuthDataHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class UserController.
 */
class UserController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Drupal\social_auth\SocialAuthDataHandler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  protected $SocialAuthDataHandler;


  /**
   * LoggerFactory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * User.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;

  /**
   * Constructs a new UserController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\social_auth\SocialAuthDataHandler $socialAuthDataHandler
   *   Social Auth Data Handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   LoggerFactory.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
  SocialAuthDataHandler $socialAuthDataHandler,
                                LoggerChannelFactoryInterface $loggerFactory,
  ConfigFactory $configFactory) {

    $this->entityTypeManager = $entity_type_manager;
    $this->SocialAuthDataHandler = $socialAuthDataHandler;
    $this->loggerFactory = $loggerFactory;
    $this->configFactory = $configFactory;

    try {
      $userStorage = $this->entityTypeManager->getStorage('user');
      $current_user_id = $this->currentUser()->id();
      $this->user = $userStorage->load($current_user_id);

    }
    catch (InvalidPluginDefinitionException $e) {
      $this->loggerFactory->get('girchi_users')->error($e->getMessage());
    }
    catch (PluginNotFoundException $e) {
      $this->loggerFactory->get('girchi_users')->error($e->getMessage());
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('social_auth.data_handler'),
      $container->get('logger.factory'),
      $container->get('config.factory')
    );
  }

  /**
   * Social auth password.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   *   Request.
   *
   * @return array|RedirectResponse
   *
   *   Return template
   */
  public function socialAuthPassword(Request $request) {

    $token = $this->SocialAuthDataHandler->get('social_auth_facebook_access_token');
    $password_check = $this->user->get('field_social_auth_password')->getValue()[0]['value'];
    $config = $this->configFactory->get('om_site_settings.site_settings');
    $subtitle = $config->get('createpass');

    if ($token && !$password_check) {
      return [
        '#type' => 'markup',
        '#theme' => 'girchi_users',
        '#uid' => $this->user->id(),
        '#subtitle' => $subtitle,
      ];
    }
    else {
      $response = new RedirectResponse("/user");
      $response->send();
      return $response;
    }

  }

  /**
   * Password Check.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   *   Request.
   *
   * @return \Zend\Diactoros\Response\JsonResponse
   *
   *   JsonResponse
   */
  public function passwordConfirm(Request $request) {

    try {

      $pass = $request->request->get('pass');
      $uid = $request->request->get('uid');

      if (empty($pass)) {
        return new JsonResponse('Password is empty');
      }

      /** @var \Drupal\user\Entity\User $user */
      if ($this->user) {
        if ($this->user->id() === $uid) {
          $this->user->setPassword($pass);
          $this->user->set('field_social_auth_password', TRUE);
          $this->user->save();
          return new JsonResponse('success');
        }
        else {
          return new JsonResponse('Unauthorized User');
        }
      }
      else {
        return new JsonResponse('User Not Found');
      }
    }
    catch (EntityStorageException $e) {
      $this->loggerFactory->get('girchi_users')->error($e->getMessage());
    }

    return new JsonResponse('Failed');

  }

}
