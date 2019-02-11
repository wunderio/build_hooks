<?php

namespace Drupal\build_hooks\Controller;

use Drupal\build_hooks\Plugin\FrontendEnvironmentManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Menu\LocalActionManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of frontend environment plugins to be added to the layout.
 */
class FrontendEnvironmentLibraryController extends ControllerBase {

  /**
   * The frontend environment manager.
   *
   * @var \Drupal\build_hooks\Plugin\FrontendEnvironmentManager
   */
  protected $frontendEnvironmentManager;

  /**
   * The local action manager.
   *
   * @var \Drupal\Core\Menu\LocalActionManagerInterface
   */
  protected $localActionManager;

  /**
   * Constructs a BlockLibraryController object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Plugin\Context\LazyContextRepository $context_repository
   *   The context repository.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Menu\LocalActionManagerInterface $local_action_manager
   *   The local action manager.
   */
  public function __construct(FrontendEnvironmentManager $frontendEnvironmentManager, LocalActionManagerInterface $local_action_manager) {
    $this->frontendEnvironmentManager = $frontendEnvironmentManager;
    $this->localActionManager = $local_action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.frontend_environment'),
      $container->get('plugin.manager.menu.local_action')
    );
  }

  /**
   * Shows a list of frontend environments that can be added.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function listFrontendEnvironments() {
    // Since modals do not render any other part of the page, we need to render
    // them manually as part of this listing.
    $headers = [
      ['data' => $this->t('Type')],
      ['data' => $this->t('Module')],
      ['data' => $this->t('Operations')],
    ];

    $definitions = $this->frontendEnvironmentManager->getDefinitions();

    $rows = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $row = [];
      $row['title']['data'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="block-filter-text-source">{{ label }}</div>',
        '#context' => [
          'label' => $plugin_definition['label'],
        ],
      ];
      $row['provider']['data'] = $plugin_definition['provider'];
      $links['add'] = [
        'title' => $this->t('Add new environment'),
        'url' => Url::fromRoute('build_hooks.admin_add', ['plugin_id' => $plugin_id]),
      ];

      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
      $rows[] = $row;
    }

    $build['frontend_environments'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No types available. Please enable one of the submodules or add your own custom plugin.'),
      '#attributes' => [
        'class' => ['block-add-table'],
      ],
    ];

    return $build;
  }

  /**
   * Builds the local actions for this listing.
   *
   * @return array
   *   An array of local actions for this listing.
   */
  protected function buildLocalActions() {
    $build = $this->localActionManager->getActionsForRoute($this->routeMatch->getRouteName());
    // Without this workaround, the action links will be rendered as <li> with
    // no wrapping <ul> element.
    if (!empty($build)) {
      $build['#prefix'] = '<ul class="action-links">';
      $build['#suffix'] = '</ul>';
    }
    return $build;
  }

}
