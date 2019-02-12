<?php

namespace Drupal\build_hooks;

/**
 * Interface TriggerInterface.
 */
interface TriggerInterface {

  /**
   * @param String $environment_id
   *
   * return void
   */
  public function execute($environment_id);

  /**
   * return void
   */
  public function executeCron();

  /**
   * @param String $nodeType
   *
   * return void
   */
  public function executeNode($nodeType);

  /**
   * return void
   */
  public function showMenu();


  /**
   * Triggers a build hook.
   *
   * @param \Drupal\build_hooks\BuildHookDetails $buildHookDetails
   *   An object that holds the information about the call.
   *
   * @return int
   *   The status code of the operation.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function triggerBuildHook(BuildHookDetails $buildHookDetails);

}
