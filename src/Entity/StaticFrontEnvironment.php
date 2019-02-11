<?php

namespace Drupal\build_hooks\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\build_hooks\FrontendEnvironmentPluginCollection;

/**
 * Defines the Static front environment entity.
 *
 * @ConfigEntityType(
 *   id = "static_front_environment",
 *   label = @Translation("Static front environment"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\build_hooks\StaticFrontEnvironmentListBuilder",
 *     "form" = {
 *       "default" = "Drupal\build_hooks\Form\StaticFrontEnvironmentForm",
 *       "edit" = "Drupal\build_hooks\Form\StaticFrontEnvironmentForm",
 *       "delete" = "Drupal\build_hooks\Form\StaticFrontEnvironmentDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\build_hooks\StaticFrontEnvironmentHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "static_front_environment",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/static_front_environment/{static_front_environment}",
 *     "add-form" = "/admin/structure/static_front_environment/add",
 *     "edit-form" = "/admin/structure/static_front_environment/{static_front_environment}/edit",
 *     "delete-form" = "/admin/structure/static_front_environment/{static_front_environment}/delete",
 *     "collection" = "/admin/structure/static_front_environment"
 *   },
 *   config_export = {
 *     "id",
 *     "weight",
 *     "provider",
 *     "plugin",
 *     "settings",
 *     "url",
 *   },
 * )
 */
class StaticFrontEnvironment extends ConfigEntityBase implements StaticFrontEnvironmentInterface, EntityWithPluginCollectionInterface {

  /**
   * The Static front environment ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The plugin collection that holds the block plugin for this entity.
   *
   * @var \Drupal\block\BlockPluginCollection
   */
  protected $pluginCollection;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The Static front environment label.
   *
   * @var string
   */
  protected $label;

  /**
   * The type of environment.
   *
   * @var string
   */
  protected $type;

  /**
   * The project name of the environment.
   *
   * @var string
   */
  protected $project;

  /**
   * The git branch name of the environment.
   *
   * @var string
   */
  protected $branch;

  /**
   * The url of the environment.
   *
   * @var string
   */
  protected $url;

  /**
   * The weight of the environment.
   *
   * @var string
   */
  protected $weight;

  /**
   * The plugin instance settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getProject() {
    return $this->project;
  }

  /**
   * @return string
   */
  public function getBranch() {
    return $this->branch;
  }

  /**
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @return int
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * Encapsulates the creation of the block's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The block's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $this->pluginCollection = new FrontendEnvironmentPluginCollection(\Drupal::service('plugin.manager.frontend_environment'), $this->plugin, $this->get('settings'), $this->id());
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'settings' => $this->getPluginCollection(),
    ];
  }

}
