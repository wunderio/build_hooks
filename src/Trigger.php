<?php

namespace Drupal\build_hooks;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\build_hooks\Entity\FrontendEnvironment;

/**
 * Class Trigger.
 */
class Trigger implements TriggerInterface {

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The http_client service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The string_translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $stringTranslation;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger.factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * @var \Drupal\build_hooks\DeployLogger
   */
  protected $deployLogger;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new Trigger object.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ClientInterface $httpClient,
    AccountProxyInterface $currentUser,
    TranslationManager $stringTranslation,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger,
    DeployLogger $deployLogger,
    EntityTypeManager $entityTypeManager
  ) {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->currentUser = $currentUser;
    $this->stringTranslation = $stringTranslation;
    $this->messenger = $messenger;
    $this->logger = $logger;
    $this->deployLogger = $deployLogger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   *
   */
  public function execute($environment_id) {
    if (!$this->isValidUser()) {
      $message = $this->stringTranslation->translate(
        'Insufficient user access',
        []
      );
      $this->logger->get('build_hooks')->error($message);
      return FALSE;
    }

    // Load details of the environment:
    try {
      /** @var FrontendEnvironment $environment */
      $environment = $this->entityTypeManager->getStorage('frontend_environment')->load($environment_id);
    }
    catch (\Exception $e) {
      $message = $this->stringTranslation->translate(
        'Build Hook execute error: "%error"',
        ['%error' => $e->getMessage()]
      );
      $this->messenger->addError($message);
      $this->logger->get('build_hooks')->error($message);
      return FALSE;
    }

    try {
      $responseCode = $this->circleciManager->runCircleciWorkflowOnEnvironment($environment);

      if ($responseCode == 200) {
        $this->deployLogger->setLastDeployTimeForEnvironment($environment);
        $envName = $environment->label();
        $message = "Deployment triggered for environment $envName.";
        $this->processMessage($message);
        $this->processLog($message);
      }
    }
    catch (GuzzleException $exception) {
      $message = $this->stringTranslation->translate(
        'Build Hook execute error: "%error"',
        ['%error' => $exception->getMessage()]
          );
      $this->messenger->addError($message);
      $this->logger->get('build_hooks')->error($message);
      return FALSE;
    }

    return TRUE;
  }

  /**
   *
   */
  public function executeCron() {
    $execute = (bool) $this->configFactory
      ->get('build_hooks.settings')
      ->get('triggers.cron');

    if ($execute) {
      $circleCiConf = $this->configFactory->get('build_hooks.circleci');
      $this->execute($circleCiConf->get('prodbranch'));
    }
  }

  /**
   *
   */
  public function executeNode($nodeType) {
    $execute = (bool) $this->configFactory
      ->get('build_hooks.settings')
      ->get('triggers.node.' . $nodeType);

    if ($execute) {
      $circleCiConf = $this->configFactory->get('build_hooks.circleci');
      $this->execute($circleCiConf->get('prodbranch'));
    }
  }

  /**
   *
   */
  public function showMenu() {
    if (!$this->isValidUser()) {
      return FALSE;
    }

    return (bool) $this->configFactory
      ->get('build_hooks.settings')
      ->get('triggers.menu');
  }

  /**
   *
   */
  private function isValidUser() {
    return $this->currentUser->hasPermission('deploy to endpoints');
  }

  /**
   *
   */
  private function processMessage($message, $args = []) {
    $show = (bool) $this->configFactory
      ->get('build_hooks.settings')
      ->get('messages.show');

    if (!$show) {
      return;
    }

    $this->messenger->addMessage(
      $this->stringTranslation->translate(
        $message,
        $args
      )
    );
  }

  /**
   *
   */
  private function processLog($message, $args = []) {
    $log = (bool) $this->configFactory
      ->get('build_hooks.settings')
      ->get('messages.log');

    if (!$log) {
      return;
    }

    $this->logger->get('build_hooks')->info(
      $this->stringTranslation->translate(
        $message,
        $args
      )
    );
  }

}
