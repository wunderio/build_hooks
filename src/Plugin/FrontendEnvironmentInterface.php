<?php

namespace Drupal\build_hooks\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines an interface for Frontend environment plugins.
 */
interface FrontendEnvironmentInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Get the info to trigger the hook based on the configuration of the plugin.
   *
   * @return \Drupal\build_hooks\BuildHookDetails
   *   An object containing the details to trigger the hook.
   */
  public function getBuildHookDetails();

}
