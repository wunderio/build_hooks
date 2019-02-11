<?php

namespace Drupal\build_hooks_circle_ci\Plugin\FrontendEnvironment;

use Drupal\build_hooks\Annotation\FrontendEnvironment;
use Drupal\build_hooks\Plugin\FrontendEnvironmentBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Netlify' frontend environment type.
 *
 * @FrontendEnvironment(
 *  id = "netlify",
 *  label = "Netlify"
 * )
 */
class NetlifyFrontendEnvironment extends FrontendEnvironmentBase {

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
      '#description' => $this->t("Netlify project for this environment."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function frontEndEnvironmentSubmit($form, FormStateInterface $form_state) {
    $this->configuration['project'] = $form_state->getValue('project');
  }

}
