<?php
/**
 * Desk
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/MIT
 *
 * @category   Desk
 * @package    Desk_Configuration
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Zend_Uri
 */
#require_once 'Zend/Uri.php';

/**
 * @see Desk_Defaults
 */
#require_once 'Desk/Defaults.php';

/**
 * @see Desk_Exceptions_Configuration
 */
#require_once 'Desk/Exceptions/Configuration.php';

/**
 * Configuration stores the clients configuration options.
 *
 * Those configuration options might be:
 *  - authentication credentials
 *  - endpoint to call
 *  - middleware to use
 */
class Desk_Configuration
{
  /**
   * @var string Consumer key for OAuth authentication
   */
  protected $consumerKey;

  /**
   * @var string Consumer secret for OAuth authentication
   */
  protected $consumerSecret;

  /**
   * @var string Access token for OAuth authentication
   */
  protected $token;

  /**
   * @var string Access token secret for OAuth authentication
   */
  protected $tokenSecret;

  /**
   * @var string Username for basic authentication
   */
  protected $username;

  /**
   * @var string Password for basic authentication
   */
  protected $password;

  /**
   * @var string|Zend_Uri Endpoint to call "https://devel.desk.com"
   */
  protected $endpoint;

  /**
   * @var array Connection options like default headers
   */
  protected $connectionOptions = [];

  /**
   * @var array List of configuration options
   */
  public static $configurationOptions = [
    'consumerKey',
    'consumerSecret',
    'token',
    'tokenSecret',
    'username',
    'password',
    'endpoint',
    'connectionOptions'
  ];

  /**
   * Returns the current consumer key.
   *
   * @return string
   */
  public function getConsumerKey()
  {
    return $this->consumerKey;
  }

  /**
   * Sets the consumer key.
   *
   * @param string $consumerKey
   * @return Configuration
   */
  public function setConsumerKey($consumerKey)
  {
    $this->consumerKey = $consumerKey;
    return $this;
  }

  /**
   * Sets the consumer secret.
   *
   * @param string $consumerSecret
   * @return Configuration
   */
  public function setConsumerSecret($consumerSecret)
  {
    $this->consumerSecret = $consumerSecret;
    return $this;
  }

  /**
   * Returns the current access token.
   *
   * @return string
   */
  public function getToken()
  {
    return $this->token;
  }

  /**
   * Sets the access token.
   *
   * @param string $token
   * @return Configuration
   */
  public function setToken($token)
  {
    $this->token = $token;
    return $this;
  }

  /**
   * Sets the access token secret.
   *
   * @param string $tokenSecret
   * @return Configuration
   */
  public function setTokenSecret($tokenSecret)
  {
    $this->tokenSecret = $tokenSecret;
    return $this;
  }

  /**
   * Returns the current username.
   *
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Sets the username.
   *
   * @param string $username
   * @return Configuration
   */
  public function setUsername($username)
  {
    $this->username = $username;
    return $this;
  }

  /**
   * Sets the password.
   *
   * @param string $password
   * @return Configuration
   */
  public function setPassword($password)
  {
    $this->password = $password;
    return $this;
  }

  /**
   * Returns the current endpoint.
   *
   * @return string
   */
  public function getEndpoint()
  {
    return $this->endpoint;
  }

  /**
   * Sets the endpoint.
   *
   * @param string $endpoint
   * @return Configuration
   */
  public function setEndpoint($endpoint)
  {
    if (is_string($endpoint) && $endpoint !== '') {
      // make sure we have a valid url
      if (filter_var($endpoint, FILTER_VALIDATE_URL) === false) {
        throw new Desk_Exceptions_Configuration('`' . $endpoint . '` is not a valid url.');
      }

      // make sure we are using https protocol
      if (strpos($endpoint, 'https') !== 0) {
        throw new Desk_Exceptions_Configuration('`' . $endpoint . '` has to use https as protocol.');
      }

      $this->endpoint = Zend_Uri::factory($endpoint);
    }

    return $this;
  }

  /**
   * Return the connection options
   *
   * @return array
   */
  public function getConnectionOptions()
  {
    return $this->connectionOptions;
  }

  /**
   * Return the connection options
   *
   * @return array
   */
  public function setConnectionOptions($options)
  {
    $this->connectionOptions = $options;
    return $this;
  }


  /**
   * Allow configuration with a lambda
   *
   * @param function $lambda
   * @return Configuration
   */
  public function configure($lambda)
  {
    if (is_callable($lambda)) {
      $lambda($this);
    }
    $this->validateCredentials();
    return $this;
  }

  /**
   * Allow to reset the client.
   *
   * @return Configuration
   */
  public function reset()
  {
    $options = Desk_Defaults::getOptions();
    foreach (self::$configurationOptions as $key) {
      $method = 'set' . ucfirst($key);
      if (method_exists($this, $method) && array_key_exists($key, $options)) {
        $this->$method($options[$key]);
      }
    }

    return $this;
  }

  /**
   * Check if we have user credentials.
   *
   * @return boolean
   */
  public function hasCredentials()
  {
    return $this->hasOAuthCredentials() ||
            $this->hasBasicAuthCredentials();
  }

  /**
   * Returns true if OAuth configuration is set up
   *
   * @return bool
   */
  public function hasOAuthCredentials()
  {
    return self::arrayAll($this->getOAuthCredentials());
  }

  /**
   * Returns true if Basic Auth configuration is set up
   *
   * @return bool
   */
  public function hasBasicAuthCredentials()
  {
    return self::arrayAll($this->getBasicAuthCredentials());
  }

  /**
   * Returns the oauth credentials.
   *
   * @return array
   */
  protected function getOAuthCredentials()
  {
    return [
      'consumer_key' => $this->consumerKey,
      'consumer_secret' => $this->consumerSecret,
      'token' => $this->token,
      'token_secret' => $this->tokenSecret
    ];
  }

  /**
   * Returns the basic auth credentials.
   *
   * @return array
   */
  protected function getBasicAuthCredentials()
  {
    return [
      'username' => $this->username,
      'password' => $this->password
    ];
  }

  /**
   * Validates credentials are set.
   *
   * @throws DeskApi\Exceptions\ConfigurationException
   * @return void
   */
  protected function validateCredentials()
  {
    if (!$this->hasCredentials()) {
      throw new Desk_Exceptions_Configuration('Some or all credentials are missing.');
    }
  }

  /**
   * Checks if all the values in the array are not empty values.
   *
   * @return boolean
   */
  public static function arrayAll($arr)
  {
    foreach ($arr as $key => $value) {
      if (empty($value)) {
        return false;
      }
    }
    return true;
  }
}
