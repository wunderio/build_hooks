<?php

namespace Drupal\build_hooks\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for building the block instance add form.
 */
class FrontendEnvironmentAddController extends ControllerBase {

  /**
   * Build the block instance add form.
   *
   * @param string $plugin_id
   *   The plugin ID for the block instance.
   * @param string $theme
   *   The name of the theme for the block instance.
   *
   * @return array
   *   The block instance edit form.
   */
  public function frontendEnvironmentAddConfigureForm($plugin_id) {
    // Create a block entity.
    $entity = $this->entityTypeManager()->getStorage('static_front_environment')->create(['plugin' => $plugin_id]);

    return $this->entityFormBuilder()->getForm($entity);
  }

}
