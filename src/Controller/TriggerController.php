<?php

namespace Drupal\build_hooks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\build_hooks\TriggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TriggerController.
 */
class TriggerController extends ControllerBase {

  /**
   * Drupal\build_hooks\TriggerInterface definition.
   *
   * @var \Drupal\build_hooks\TriggerInterface
   */
  protected $buildHooksTrigger;

  /**
   * Constructs a new TriggerController object.
   */
  public function __construct(TriggerInterface $build_hooks_trigger) {
    $this->buildHooksTrigger = $build_hooks_trigger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('build_hooks.trigger')
    );
  }

  public function execute($branch) {
    $previousUrl = \Drupal::request()
      ->server->get('HTTP_REFERER');

    $referer = Request::create($previousUrl);

    $this->buildHooksTrigger->execute($branch);

    return new RedirectResponse($referer->getRequestUri());
  }

}
