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
 * @category   DeskTest
 * @package    DeskTest_TestCase
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Desk_Client
 */
#require 'Desk/Client.php';

/**
 * @see PHPUnit_Framework_TestCase
 */
#require 'PHPUnit/Framework/TestCase.php';

/**
 * Base testcase class for all DeskApi testcases.
 */
abstract class DeskTest_TestCase extends PHPUnit_Framework_TestCase
{

  /**
   * The path to store fixtures at.
   *
   * @var string
   */
  public static $fixturesPath;

  /**
   * The config for the clients to be set up.
   *
   * @var array
   */
  protected static $config;

  /**
   * The clients array
   *
   * @var array
   */
  public static $clients = array();

  /**
   * Returns the specified client.
   *
   * @param string client name
   * @return GuzzleHttp\Client
   */
  public function getClient($name = 'default')
  {
    if ($name === 'default') {
      $name = isset(self::$config['default_client']) ? self::$config['default_client'] : 'default';
    }

    if (!isset(self::$clients[$name])) {
      if (isset(self::$config['clients'][$name])) {
        self::$clients[$name] = new Desk_Client(self::$config['clients'][$name]);
        self::$clients[$name]->setConnectionOptions(array_merge(
          Desk_Defaults::$connectionOptions,
          array('adapter' => 'DeskTest_Adapter')
        ));
      } else {
        self::$clients[$name] = new Desk_Client();
      }
    }

    return self::$clients[$name];
  }

  /**
   * Sets the fixtures base path.
   *
   * @param string path
   */
  public static function setFixturesPath($path)
  {
    self::$fixturesPath = $path;
  }

  /**
   * Sets the config
   *
   * @param string path
   */
  public static function setConfig($path)
  {
    self::$config = yaml_parse_file($path);
  }
}
