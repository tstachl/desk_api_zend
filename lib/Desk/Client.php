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
 * @package    Desk_Client
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
* @see Zend_Config
*/
#require_once 'Zend/Config.php';

/**
 * @see Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Oauth_Token_Access
 */
#require_once 'Zend/Oauth/Token/Access.php';

/**
 * @see Zend_Http_Client_Adapter_Exception
 */
#require_once 'Zend/Http/Client/Adapter/Exception.php';

/**
 * @see Zend_Uri
 */
#require_once 'Zend/Uri.php';

/**
 * @see Desk_Configuration
 */
#require_once 'Desk/Configuration.php';

/**
 * @see Desk_Resource
 */
#require_once 'Desk/Resource.php';

/**
 * @see Desk_Exception
 */
#require_once 'Desk/Exception.php';

/**
 * @category    Desk
 * @package     Desk_Client
 * @copyright   Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */
class Desk_Client extends Desk_Configuration
{
  /**
   * Setter/Getter underscore transformation cache
   *
   * @var array
   */
  protected static $_underscoreCache = [];

  /**
   * HTTP Client to be used
   *
   * @var Zend_Http_Client|Zend_Oauth_Client
   */
  protected $client;

  /**
   * Constructor
   *
   * @param  array $options options array
   */
  public function __construct($options = [])
  {
    // start with a clean slate
    $this->reset();

    if ($options instanceof Zend_Config) {
      $options = $options->toArray();
    }

    foreach ($options as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (is_callable([$this, $method])) {
        $this->$method($value);
      }
    }
  }

  /**
   * Perform an HTTP GET request
   *
   * @param string path to fetch from
   * @return Zend_Http_Response
   */
  public function get($path)
  {
    return $this->_request('GET', $path);
  }

  /**
   * Perform an HTTP POST request
   *
   * @param string path to post to
   * @param string|array payload to post
   * @return Zend_Http_Response
   */
  public function post($path, $params = [])
  {
    return $this->_request('POST', $path, $params);
  }

  /**
   * Perform an HTTP PATCH request
   *
   * @param string path to post to
   * @param string|array payload to post
   * @return Zend_Http_Response
   */
  public function patch($path, $params = [])
  {
    return $this->_request('PATCH', $path, $params);
  }

  /**
   * Perform an HTTP DELETE request
   *
   * @param string path to post to
   * @return Zend_Http_Response
   */
  public function delete($path)
  {
    return $this->_request('DELETE', $path);
  }

  /**
   * Perform an HTTP HEAD request
   *
   * @param string path to fetch from
   * @return Zend_Http_Response
   */
  public function head($path)
  {
    return $this->_request('HEAD', $path);
  }

  /**
   * Makes the magic happen!
   *
   * @param  string $name
   * @param  mixed  $arguments
   * @return Desk_Resource
   */
  public function __call($method, $args)
  {
    if (substr($method, 0, 3) === 'get') {
      $key = $this->_underscore(substr($method,3));
      $res = new Desk_Resource($this, Desk_Resource::buildSelfLink("/api/v2/$key"));
      if (count($args) > 0) {
        $res->queryParams($args[0]);
      }
      return $res;
    }

    throw new Desk_Exception("Method `$method' not supported.");
  }

  /**
   * Converts field names for setters and geters
   *
   * $this->setMyField($value) === $this->setData('my_field', $value)
   * Uses cache to eliminate unneccessary preg_replace
   *
   * @param string $name
   * @return string
   */
  protected function _underscore($name)
  {
    if (isset(self::$_underscoreCache[$name])) {
      return self::$_underscoreCache[$name];
    }
    $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    self::$_underscoreCache[$name] = $result;
    return $result;
  }

  /**
   * Creates and returns a client
   *
   * @return Zend_Http_Client
   */
  protected function _client()
  {
    if ($this->hasOAuthCredentials()) {
      $creds = $this->getOAuthCredentials();
      $token = new Zend_Oauth_Token_Access();
      $this->client = $token->setToken($creds['token'])
                            ->setTokenSecret($creds['token_secret'])
                            ->getHttpClient([
                              'consumerKey' => $creds['consumer_key'],
                              'consumerSecret' => $creds['consumer_secret']
                            ])
                            ->setConfig($this->getConnectionOptions());
    } else {
      $creds = $this->getBasicAuthCredentials();
      $this->client = new Zend_Http_Client(null, $this->getConnectionOptions());
      $this->client->setAuth($creds['username'], $creds['password']);
    }

    // reset just in case
    $this->client->resetParameters();
    return $this->client;
  }

  /**
   * Send off the actual request to the API
   *
   * @param  string $method  GET|POST|PATCH|DELETE
   * @param  string $path    the api path (/api/v2/cases)
   * @param  mixed  $payload an array or string to be sent as payload
   * @throws Desk_Exception
   * @return Zend_Http_Response
   */
  protected function _request($method, $path, $payload = null, $tries = 5)
  {
    $client = $this->_client();
    $client->setMethod($method);
    $client->setUri(Zend_Uri::factory($this->getEndpoint() . $path));

    if (isset($payload) && $payload !== '') {
      if (!is_string($payload)) {
        $payload = Zend_Json::encode($payload);
      }
      $client->setRawData($payload, 'application/json');
    }

    try {
      $response = $client->request();
    } catch (Zend_Http_Client_Adapter_Exception $e) {
      // this get's thrown if you use the curl adapter because
      // currently doesn't support PATCH
      $client->setMethod('POST')->setHeaders('X-HTTP-Method-Override', $method);
      $response = $client->request();
    }

    if ($response->getStatus() === 429 && $tries > 0) {
      $reset = $response->getHeader('X-Rate-Limit-Reset');
      sleep(intval(is_array($reset) ? $reset[0] : $reset));
      return $this->_request($method, $path, $payload, --$tries);
    }

    return $response;
  }
}
