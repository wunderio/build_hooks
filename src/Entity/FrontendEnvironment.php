<?php

namespace Drupal\build_hooks\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\build_hooks\FrontendEnvironmentPluginCollection;

/**
 * Defines the Frontend environment entity.
 *
 * @ConfigEntityType(
 *   id = "frontend_environment",
 *   label = @Translation("Front end environment"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\build_hooks\FrontendEnvironmentListBuilder",
 *     "form" = {
 *       "default" = "Drupal\build_hooks\Form\FrontendEnvironmentForm",
 *       "edit" = "Drupal\build_hooks\Form\FrontendEnvironmentForm",
 *       "delete" = "Drupal\build_hooks\Form\FrontendEnvironmentDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\build_hooks\FrontendEnvironmentHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "frontend_environment",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/frontend_environment/{frontend_environment}",
 *     "add-form" = "/admin/structure/frontend_environment/add",
 *     "edit-form" = "/admin/structure/frontend_environment/{frontend_environment}/edit",
 *     "delete-form" = "/admin/structure/frontend_environment/{frontend_environment}/delete",
 *     "collection" = "/admin/structure/frontend_environment"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "weight",
 *     "provider",
 *     "plugin",
 *     "settings",
 *     "url",
 *   },
 * )
 */
class FrontendEnvironment extends ConfigEntityBase implements FrontendEnvironmentInterface, EntityWithPluginCollectionInterface {

  /**
   * The Frontend environment ID.
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
   * The Frontend environment label.
   *
   * @var string
   */
  protected $label;

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
   * Encapsulates the creation of the frontend environment's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The frontend environment's plugin collection.
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
