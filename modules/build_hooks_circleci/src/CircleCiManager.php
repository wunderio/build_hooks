<?php

namespace Drupal\build_hooks_circleci;

use Drupal\build_hooks\BuildHookDetails;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;

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
   * @param array $config
   *   The plugin configuration array.
   *
   * @return string
   *   The url to call to trigger a deployment in the environment.
   */
  private function buildCirlceCiApiBuildUrlForEnvironment(array $config) {
    $circleCiConf = $this->configFactory->get('build_hooks_circleci.circleCiConfig');
    $apiKey = $circleCiConf->get('circleci_api_key');
    return $this->buildCircleciApiBasePathForEnvironment($config) . "build?circle-token=$apiKey";
  }

  /**
   * Build a url to call circle ci depending on the frontend environment config.
   *
   * @param array $config
   *   The configuration array from the plugin.
   *
   * @return string
   *   The url to call.
   */
  private function buildCircleCiApiBasePathForEnvironment(array $config) {
    $basePath = self::CIRCLECI_BASE_PATH;
    $platform = self::CIRCLECI_HOSTED_PLATFORM;
    $project = $config['project'];
    return "$basePath/project/$platform/$project/";
  }

  /**
   * Returns the build hooks details based on plugin configuration.
   *
   * @param array $config
   *   The plugin configuration array.
   *
   * @return \Drupal\build_hooks\BuildHookDetails
   *   Build hooks detail object with info about the request to make.
   */
  public function getBuildHookDetailsForPluginConfiguration(array $config) {
    $buildHookDetails = new BuildHookDetails();
    $buildHookDetails->setUrl($this->buildCirlceCiApiBuildUrlForEnvironment($config));
    $buildHookDetails->setMethod('POST');
    $buildHookDetails->setBody([
      'json' => [
        'branch' => $config['branch'],
      ],
    ]);
    return $buildHookDetails;
  }

  /**
   * Get the latest builds from Circle CI for and environment.
   *
   * @param array $settings
   *   The plugin settings array.
   * @param int $limit
   *   Number of desired builds to retrieve.
   *
   * @return array
   *   An array with info about the builds.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function retrieveLatestBuildsFromCicleciForEnvironment(array $settings, $limit = 1) {
    $url = $this->buildCirlceCiApiRetrieveBuildsUrl($settings, $limit);
    $options = [
      'headers' => [
        'Accept' => 'application/json',
      ],
    ];
    $response = $this->httpClient->request('GET', $url, $options);
    $payload = json_decode($response->getBody()->getContents(), TRUE);
    return $payload;
  }

  /**
   * Build the url to retrieve latest builds from circle ci for an environment.
   *
   * @param array $config
   *   The configuration array from the plugin.
   * @param int $limit
   *   Number of desired builds to retrieve.
   *
   * @return string
   *   The url to call to get the builds.
   */
  private function buildCirlceCiApiRetrieveBuildsUrl(array $config, $limit) {
    $circleCiConf = $this->configFactory->get('build_hooks_circleci.circleCiConfig');
    $apiKey = $circleCiConf->get('circleci_api_key');
    $branch = $config['branch'];
    return $this->buildCircleCiApiBasePathForEnvironment($config) . "tree/$branch?circle-token=$apiKey&limit=$limit";
  }

}
