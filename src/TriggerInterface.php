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

}
