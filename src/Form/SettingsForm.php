<?php

namespace Drupal\build_hooks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  protected $entityTypeManager;

  protected $nodeTypes;

  protected $entityTypes;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'build_hooks.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'build_hooks_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('build_hooks.settings');

    $form['divider_line'] = [
      '#markup' => '<h2>' . $this->t('Triggers') . '</h2>' . '<hr/>',
    ];

    $form['divider_user'] = [
      '#markup' => '<h4>' . $this->t('User Interaction') . '</h4>',
    ];

    $form['menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Execute via toolbar'),
      '#default_value' => $config->get('triggers.menu'),
    ];

    $form['divider_automatic'] = [
      '#markup' => '<h4>' . $this->t('Automatic') . '</h4>',
    ];

    $form['cron'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Execute via cron'),
      '#default_value' => $config->get('triggers.cron'),
    ];

    $form['divider_node'] = [
      '#markup' => '<h4>' . $this->t('Node Update') . '</h4>',
    ];

    foreach ($this->getNodeTypes() as $nodeType) {
      $form['node_type_' . $nodeType->id()] = [
        '#type' => 'checkbox',
        '#title' => $this->t($nodeType->label()),
        '#default_value' => $config->get('triggers.node.' . $nodeType->id()),
      ];
    }

    $form['divider_messages'] = [
      '#markup' => '<h2>' . $this->t('Messages') . '</h2>' . '<hr/>',
    ];

    $form['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Message'),
      '#default_value' => $config->get('messages.show'),
    ];

    $form['log'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Log Message'),
      '#default_value' => $config->get('messages.log'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('build_hooks.settings');
    $config->set('build_hook', $form_state->getValue('build_hook'));
    $config->set('messages.log', $form_state->getValue('log'));
    $config->set('messages.show', $form_state->getValue('show'));
    $config->set('triggers.cron', $form_state->getValue('cron'));
    $config->set('triggers.menu', $form_state->getValue('menu'));

    foreach ($this->getNodeTypes() as $nodeType) {
      $config->set(
        'triggers.node.' . $nodeType->id(),
        $form_state->getValue('node_type_' . $nodeType->id())
      );
    }

    $config->save();
  }

  private function getNodeTypes() {
    if ($this->nodeTypes) {
      return $this->nodeTypes;
    }

    $this->nodeTypes = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    return $this->nodeTypes;
  }

  private function getContentEntityTypes() {
    if ($this->nodeTypes) {
      return $this->nodeTypes;
    }

    $this->nodeTypes = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    return $this->nodeTypes;
  }

}
