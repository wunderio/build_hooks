<?php

namespace Drupal\build_hooks;

/**
 * Class BuildHookDetails.
 *
 * Holds information to make the call to an external service for a build hook.
 */
class BuildHookDetails {

  /**
   * @var string
   *
   * The url to call.
   */
  protected $url;

  /**
   * @var string
   *
   * The method to use (POST,GET,...)
   */
  protected $method;

  /**
   * @var array
   *
   * The body of the request.
   */
  protected $body;

  /**
   *
   */
  public function __construct() {
    $this->url = '';
    $this->body = [];
    $this->method = '';
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * @param string $url
   */
  public function setUrl(string $url): void {
    $this->url = $url;
  }

  /**
   * @return string
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * @param string $method
   */
  public function setMethod(string $method): void {
    $this->method = $method;
  }

  /**
   * @return array
   */
  public function getBody(): array {
    return $this->body;
  }

  /**
   * @param array $body
   */
  public function setBody(array $body): void {
    $this->body = $body;
  }

}
