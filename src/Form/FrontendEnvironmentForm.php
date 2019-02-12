<?php

namespace Drupal\build_hooks\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\build_hooks\Entity\FrontendEnvironment;
use Drupal\Core\Form\SubformState;
use Drupal\build_hooks\Plugin\FrontendEnvironmentInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class StaticFrontEnvironmentForm.
 */
class FrontendEnvironmentForm extends EntityForm {

  /**
   * The plugin form manager.
   *
   * @var \Drupal\Core\Plugin\PluginFormFactoryInterface
   */
  protected $pluginFormFactory;


  public function __construct(PluginFormFactoryInterface $plugin_form_manager) {
    $this->pluginFormFactory = $plugin_form_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin_form.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var FrontendEnvironment $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t("Label for the Frontend environment."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\build_hooks\Entity\FrontendEnvironment::load',
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#maxlength' => 255,
      '#default_value' => $entity->getUrl(),
      '#description' => $this->t("Url at which this environment is available for viewing."),
      '#required' => TRUE,
    ];

    $form['weight'] = [
      '#type' => 'number',
      '#title' => $this->t('Weight'),
      '#max' => 100,
      '#min' => -100,
      '#size' => 3,
      '#default_value' => $entity->getWeight() ? $entity->getWeight() : 0,
      '#description' => $this->t("Set the weight, lighter environments will be rendered first in the toolbar."),
      '#required' => TRUE,
    ];

    $form['#tree'] = TRUE;
    $form['settings'] = [];
    $subform_state = SubformState::createForSubform($form['settings'], $form, $form_state);
    $form['settings'] = $this->getPluginForm($entity->getPlugin())->buildConfigurationForm($form['settings'], $subform_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $entity = $this->entity;

    $sub_form_state = SubformState::createForSubform($form['settings'], $form, $form_state);
    // Call the plugin submit handler.
    $block = $entity->getPlugin();
    $this->getPluginForm($block)
      ->submitConfigurationForm($form, $sub_form_state);


    // Save the settings of the plugin.
    $entity->save();

    $this->messenger()
      ->addStatus($this->t('The frontend environment configuration has been saved.'));
    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }

  protected function getPluginForm(FrontendEnvironmentInterface $frontendEnvironment) {
    if ($frontendEnvironment instanceof PluginWithFormsInterface) {
      return $this->pluginFormFactory->createInstance($frontendEnvironment, 'configure');
    }
    return $frontendEnvironment;
  }

}
