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
 * @package    Desk_Defaults
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Desk_Configuration
 */
#require_once 'Desk/Configuration.php';

/**
 * This is just some default information to reset clients.
 *
 * Contains:
 *  - user credentials
 *  - endpoint
 *  - ...
 */
class Desk_Defaults
{
  /**
   * @var Default connection options
   */
  public static $connectionOptions = [
    'maxredirects' => 10,
    'strict' => true,
    'strictredirects' => true,
    'useragent' => 'Desk.com API Client',
    'timeout' => 10,
    'httpversion' => '1.1',
    'adapter' => 'Zend_Http_Client_Adapter_Socket',
    'keepalive' => false,
    'storeresponse' => false,
    'encodecookies' => true
  ];

  /**
   * Returns an array of options.
   *
   * @return array
   */
  public static function getOptions()
  {
    $options = [];

    foreach (Desk_Configuration::$configurationOptions as $option) {
      $method = 'get' . ucfirst($option);
      if (is_callable('Defaults', $method)) {
        $options[$option] = self::$method();
      }
    }

    return $options;
  }

  /**
   * Returns the default username.
   *
   * @return string
   */
  public static function getUsername()
  {
    return getenv('DESK_USERNAME');
  }

  /**
   * Returns the default password.
   *
   * @return string
   */
  public static function getPassword()
  {
    return getenv('DESK_PASSWORD');
  }

  /**
   * Returns the consumer key.
   *
   * @return string
   */
  public static function getConsumerKey()
  {
    return getenv('DESK_CONSUMER_KEY');
  }

  /**
   * Returns the consumer secret.
   *
   * @return string
   */
  public static function getConsumerSecret()
  {
    return getenv('DESK_CONSUMER_SECRET');
  }

  /**
   * Returns the access token.
   *
   * @return string
   */
  public static function getToken()
  {
    return getenv('DESK_TOKEN');
  }

  /**
   * Returns the access token secret.
   *
   * @return string
   */
  public static function getTokenSecret()
  {
    return getenv('DESK_TOKEN_SECRET');
  }

  /**
   * Returns the endpoint.
   *
   * @return string
   */
  public static function getEndpoint()
  {
    return getenv('DESK_ENDPOINT');
  }

  /**
   * Returns the connection options.
   *
   * @return array
   */
  public static function getConnectionOptions()
  {
    return self::$connectionOptions;
  }
}
