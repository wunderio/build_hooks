<?php

namespace Drupal\build_hooks\Form;

use Drupal\build_hooks\CircleCiManager;
use Drupal\build_hooks\Entity\FrontendEnvironment;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\build_hooks\TriggerInterface;
use Drupal\build_hooks\DeployLogger;
use Drupal\views\Views;
use Drupal\Core\Datetime\DateFormatter;

/**
 * Class DeploymentForm.
 */
class DeploymentForm extends FormBase {

  /**
   * Drupal\build_hooks\TriggerInterface definition.
   *
   * @var \Drupal\build_hooks\TriggerInterface
   */
  protected $buildHooksTrigger;

  /**
   * Drupal\build_hooks\DeployLogger definition.
   *
   * @var \Drupal\build_hooks\DeployLogger
   */
  protected $buildHooksDeploylogger;

  /**
   * Drupal\Core\Render\Renderer definition.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a new DeploymentForm object.
   */
  public function __construct(
    TriggerInterface $build_hooks_trigger,
    DeployLogger $build_hooks_deploylogger,
    Renderer $renderer,
    DateFormatter $dateFormatter
  ) {
    $this->buildHooksTrigger = $build_hooks_trigger;
    $this->buildHooksDeploylogger = $build_hooks_deploylogger;
    $this->renderer = $renderer;
    $this->dateFormatter = $dateFormatter;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('build_hooks.trigger'),
      $container->get('build_hooks.deploylogger'),
      $container->get('renderer'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'deployment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, FrontendEnvironment $frontend_environment = NULL) {

    $last_deployment_timestamp = $this->buildHooksDeploylogger->getLastDeployTimeForEnvironment($frontend_environment);
    $last_deployment_timestamp_formatted = $this->dateFormatter->format($last_deployment_timestamp, 'long');
    $environmentName = $frontend_environment->label();

    $form['display'] = [
      '#markup' => '<h2>' . t('@envName Environment', ['@envName' => $frontend_environment->label()]) . '</h2>',
    ];

    $form['environment link'] = [
      '#markup' => t('Frontend @environmentName site url: @link', [
        '@link' => Link::fromTextAndUrl($frontend_environment->getUrl(), Url::fromUri($frontend_environment->getUrl(), ['attributes' => ['target' => '_blank']]))
          ->toString(),
        '@environmentName' => $frontend_environment->label()
      ]),
    ];

    $form['lastdeployment'] = [
      '#markup' => '<p>' . t('Last deployment triggered on: <strong>@date</strong>', ['@date' => $last_deployment_timestamp_formatted]) . '</p>',
    ];

    $form['changelog'] = [
      '#type' => 'details',
      '#title' => $this->t('Changelog'),
      '#description' => $this->t("This is a summary of the changes since the previous deployment to the <strong>%branch</strong> environment:", ['%branch' => $environmentName]) . '</p>',
      '#open' => TRUE,
    ];

    if ($this->buildHooksDeploylogger->getNumberOfItemsSinceLastDeploymentForEnvironment($frontend_environment) > 0) {

      $form['changelog']['log'] = [
        '#markup' => $this->getChangelogView($last_deployment_timestamp),
      ];

    }
    else {
      $form['changelog']['#description'] = '<p>' . $this->t('No changes recorded since the last deployment for this environment. If needed you can still trigger a deployment using this page.') . '</p>';
    }

    $form['latestCircleCiDeployments'] = [
      '#type' => 'details',
      '#title' => $this->t('Recent deployments'),
      '#description' => $this->t('Here you can see the details for the latest deployments for this environment.'),
      '#open' => TRUE,
    ];


    //$form['latestCircleCiDeployments']['table'] = $this->getLastCicleCiDeploymentsTable($frontend_environment);

//    $form['latestCircleCiDeployments']['refresher'] = [
//      '#type' => 'button',
//      '#ajax' => [
//        'callback' => '::refreshDeploymentTable',
//        'wrapper' => 'ajax-replace-table',
//        'effect' => 'fade',
//        'progress' => [
//          'type' => 'throbber',
//          'message' => t('Refreshing deployment status...'),
//        ],
//      ],
//      '#value' => $this->t('Refresh'),
//    ];
//
//    $form['environment_id'] = [
//      '#type' => 'value',
//      '#value' => $frontend_environment->id(),
//    ];
//
//    $form['submit'] = [
//      '#type' => 'submit',
//      '#value' => $this->t('Start a new deployment to the @environment environment', ['@environment' => $frontend_environment->label()]),
//    ];

    return $form;
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
    // Get the environment id:
    $environment_id = $form_state->getValue('environment_id');
    // Trigger the deployment!
    $this->buildHooksTrigger->execute($environment_id);
  }

  /**
   * Renders a changelog of watchdog events since a specific timestamp
   *
   * @param $timestamp
   *
   * @return mixed
   */
  private function getChangelogView($timestamp) {
    $thisView = Views::getView('build_hooks_edititing_log');
    $wids = $this->buildHooksDeploylogger->getLogItemsSinceTimestamp($timestamp);
    $arg = implode('+', $wids);
    // TODO: do not use drupal_render
    return $this->renderer->render($thisView->buildRenderable('embed_1', [$arg]));
  }


  private function getLastCicleCiDeploymentsTable(FrontendEnvironment $environment) {
    $circleCiData = $this->circleCiManager->retrieveLatestBuildsFromCicleciForEnvironment($environment, 8);
    $element = [
      '#type' => 'table',
      '#attributes' => ['id' => 'ajax-replace-table'],
      '#header' => [
        $this->t('Started at'),
        $this->t('Finished at'),
        $this->t('Status'),
      ],
    ];
    if (!empty($circleCiData)) {
      foreach ($circleCiData as $circleCiDeployment) {

        // HACK: We do not want to show the "validate" jobs:
        if ($circleCiDeployment['build_parameters']['CIRCLE_JOB'] == 'validate') {
          continue;
        }

        $started_time = $circleCiDeployment['start_time'] ? format_date(\DateTime::createFromFormat('Y-m-d\TH:i:s+', $circleCiDeployment['start_time'])->getTimestamp(), 'long') : '';

        $element[$circleCiDeployment['build_num']]['started_at'] = [
          '#type' => 'item',
          '#markup' => $started_time,
        ];

        $stopped_time = $circleCiDeployment['stop_time'] ? format_date(\DateTime::createFromFormat('Y-m-d\TH:i:s+', $circleCiDeployment['stop_time'])->getTimestamp(), 'long') : '';

        $element[$circleCiDeployment['build_num']]['finished_at'] = [
          '#type' => 'item',
          '#markup' => $stopped_time,
        ];

        $element[$circleCiDeployment['build_num']]['status'] = [
          '#type' => 'item',
          '#markup' => '<strong>' . $circleCiDeployment['status'] . '</strong>',
        ];
      }
    }
    return $element;
  }

  public function refreshDeploymentTable($form, FormStateInterface $form_state) {
    return $form['latestCircleCiDeployments']['table'];
  }


}
