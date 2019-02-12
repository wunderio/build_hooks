<?php

namespace Drupal\build_hooks_circleci\Plugin\FrontendEnvironment;

use Drupal\build_hooks\Annotation\FrontendEnvironment;
use Drupal\build_hooks\Plugin\FrontendEnvironmentBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\build_hooks_circleci\CircleCiManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'CircleCI' frontend environment type.
 *
 * @FrontendEnvironment(
 *  id = "circleci",
 *  label = "Circle CI",
 *  description = "An environment connected to Circle CI"
 * )
 */
class CircleCiFrontendEnvironment extends FrontendEnvironmentBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\giroola_checkfront\CheckfrontManager definition.
   *
   * @var \Drupal\build_hooks_circleci\CircleCiManager
   */
  protected $circleCiManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\build_hooks_circleci\CircleCiManager $circleCiManager
   *   The Circle CI Manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    CircleCiManager $circleCiManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->circleCiManager = $circleCiManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('build_hooks_circleci.circleci_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function frontEndEnvironmentForm($form, FormStateInterface $form_state) {
    $form['project'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project name'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['project'],
      '#description' => $this->t("Circle CI / Github Project name for this environment. Include the organization name."),
      '#required' => TRUE,
    ];

    $form['branch'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Git branch'),
      '#maxlength' => 255,
      '#default_value' => $this->configuration['branch'],
      '#description' => $this->t("Git branch to deploy to for this environment."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function frontEndEnvironmentSubmit($form, FormStateInterface $form_state) {
    $this->configuration['project'] = $form_state->getValue('project');
    $this->configuration['branch'] = $form_state->getValue('branch');
  }

  /**
   * {@inheritdoc}
   */
  public function getBuildHookDetails() {
    return $this->circleCiManager->getBuildHookDetailsForPluginConfiguration($this->getConfiguration());
  }

}
