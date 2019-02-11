<?php

namespace Drupal\build_hooks\Plugin\FrontendEnvironment;

use Drupal\build_hooks\Annotation\FrontendEnvironment;
use Drupal\build_hooks\Plugin\FrontendEnvironmentBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'CircleCI' frontend environment type.
 *
 * @FrontendEnvironment(
 *  id = "circleci",
 *  label = "Circle CI"
 * )
 */
class CircleCiFrontendEnvironment extends FrontendEnvironmentBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
          ] + parent::defaultConfiguration();
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
      '#description' => $this->t("Circle ci / Github Project name for this environment. Include the organization name."),
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

}
