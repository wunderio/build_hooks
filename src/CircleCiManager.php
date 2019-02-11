<?php

namespace Drupal\build_hooks;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\build_hooks\Entity\FrontendEnvironment;

/**
 * Class CircleCiManager.
 */
class CircleCiManager {

  const CIRCLECI_BASE_PATH = 'https://circleci.com/api/v1.1';
  const CIRCLECI_HOSTED_PLATFORM = 'github';

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;


  /**
   * Constructs a new CircleCiManager object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
  }

  /**
   * Build the url to trigger a circle ci build depending on the environment.
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   *
   * @return string
   */
  private function buildCirlceCiApiBuildUrl(FrontendEnvironment $environment) {
    $circleCiConf = $this->configFactory->get('build_hooks.circleci');
    $apiKey = $circleCiConf->get('circleciapikey');
    return $this->buildCircleciApiBasePathForEnvironment($environment) . "build?circle-token=$apiKey";
  }

  /**
   * Triggers a build on Circle ci for an environment
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   *
   * @return bool
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function runCircleciWorkflowOnEnvironment(FrontendEnvironment $environment) {
    $response = $this->httpClient->request('POST', $this->buildCirlceCiApiBuildUrl($environment), [
      'json' => [
        'branch' => $environment->getBranch(),
      ],
    ]);
    return $response->getStatusCode();
  }

  /**
   * Triggers a build on Circle ci for an environment
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   *
   */


  /**
   * Get the latest x builds from Cicle ci for an environment
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   * @param int $limit
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function retrieveLatestBuildsFromCicleciForEnvironment(FrontendEnvironment $environment, $limit = 1) {
    $url = $this->buildCirlceCiApiRetrieveBuildsUrl($environment, $limit);
    $options = ['headers' => [
      'Accept'     => 'application/json',
      ]
    ];
    $response = $this->httpClient->request('GET', $url, $options);
    $payload = json_decode($response->getBody()->getContents(), TRUE);
    return $payload;
  }

  /**
   *  Build the url to retrieve latest builds from circle ci for an environment.
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   * @param int $limit
   *
   * @return string
   */
  private function buildCirlceCiApiRetrieveBuildsUrl(FrontendEnvironment $environment, $limit) {
    $circleCiConf = $this->configFactory->get('build_hooks.circleci');
    $apiKey = $circleCiConf->get('circleciapikey');
    $branch = $environment->getBranch();
    return $this->buildCircleciApiBasePathForEnvironment($environment) . "tree/$branch?circle-token=$apiKey&limit=$limit";
  }

  /**
   * Build the url to call circle ci depending on the environment.
   *
   * @param \Drupal\build_hooks\Entity\FrontendEnvironment $environment
   *
   * @return string
   */
  private function buildCircleciApiBasePathForEnvironment(FrontendEnvironment $environment) {
    $basePath = self::CIRCLECI_BASE_PATH;
    $platform = self::CIRCLECI_HOSTED_PLATFORM;
    $project = $environment->getProject();
    return "$basePath/project/$platform/$project/";
  }


}
