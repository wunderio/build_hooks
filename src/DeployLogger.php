<?php

namespace Drupal\build_hooks;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Database\Connection;
use Drupal\build_hooks\Entity\StaticFrontEnvironment;

/**
 * Class DeployLogger.
 */
class DeployLogger {

  CONST LOGGABLE_ENTITY_TYPES = ['node','taxonomy_term','author','media','menu_link_content'];
  CONST LOGGER_CHANNEL_NAME = 'build_hooks_logger';

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * Drupal\Core\State\StateInterface definition.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new DeployLogger object.
   */
  public function __construct(LoggerChannel $logger, StateInterface $state, Connection $database) {
    $this->logger = $logger;
    $this->state = $state;
    $this->database = $database;
  }

  /**
   * Determines if we should log activity related to the passed entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return bool
   */
  private function isEntityTypeLoggable(ContentEntityInterface $entity) {
     $entityType = $entity->getEntityTypeId();
     return in_array($entityType, self::LOGGABLE_ENTITY_TYPES);
  }

  /**
   * Logs the creation of an entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function logEntityCreated(ContentEntityInterface $entity) {
    if(!$this->isEntityTypeLoggable($entity)) {
      return;
    }
    $this->logger->info('@entityBundle: %entityTitle was created.',['@entityBundle' => $entity->bundle(), '%entityTitle' => $entity->label()]);
  }

  /**
   * Logs the updating of an entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function logEntityUpdated(ContentEntityInterface $entity) {
    if(!$this->isEntityTypeLoggable($entity)) {
      return;
    }
    $this->logger->info('@entityBundle: %entityTitle was updated.',['@entityBundle' => $entity->bundle(), '%entityTitle' => $entity->label()]);
  }

  /**
   * Logs the deleting of an entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function logEntityDeleted(ContentEntityInterface $entity) {
    if(!$this->isEntityTypeLoggable($entity)) {
      return;
    }
    $this->logger->info('@entityBundle: %entityTitle was deleted.',['@entityBundle' => $entity->bundle(), '%entityTitle' => $entity->label()]);
  }

  /**
   * Get the last deployed time for an environment.
   *
   * @param \Drupal\build_hooks\Entity\StaticFrontEnvironment $environment
   */
  public function setLastDeployTimeForEnvironment(StaticFrontEnvironment $environment) {
    $this->state->set('lastDeployForEnv' . $environment->getBranch(), time());
  }

  /**
   * Get the last deployed time for an environment.
   *
   * @param \Drupal\build_hooks\Entity\StaticFrontEnvironment $environment
   *
   * @return mixed
   */
  public function getLastDeployTimeForEnvironment(StaticFrontEnvironment $environment) {
    return $this->state->get('lastDeployForEnv' . $environment->getBranch(), 0);
  }

  /**
   * Gets a list of the last relevant log items after a certain timestamp.
   *
   * @param $timestamp
   *
   * @return array
   */
  public function getLogItemsSinceTimestamp($timestamp) {
    $wids = [];
    $type = self::LOGGER_CHANNEL_NAME;
    $query = $this->database->select('watchdog', 'w');
    $query->fields('w', [
      'wid'
    ]);
    $query->where("w.timestamp > $timestamp AND w.type = '$type'");
    $result = $query
      ->execute();
    foreach ($result as $item) {
      $wids[] = $item->wid;
    };
    return $wids;
  }

  /**
   * Returns the number of changes since the last deployment for a specific branch
   *
   * @param $branch
   *
   * @return int
   */
  public function getNumberOfItemsSinceLastDeploymentForEnvironment(StaticFrontEnvironment $environment) {
    $timestamp = $this->getLastDeployTimeForEnvironment($environment);
    $elements = $this->getLogItemsSinceTimestamp($timestamp);
    return count($elements);
  }

}
