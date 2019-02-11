<?php

namespace Drupal\build_hooks\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for Frontend environment plugins.
 */
interface FrontendEnvironmentInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {





  // Add get/set methods for your plugin type here.

}
