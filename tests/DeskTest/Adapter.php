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
 * @package    DeskTest_Adapter
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see DeskTest_TestCase
 */
#require 'DeskTest/TestCase.php';

/**
 * @see Zend_Http_Client_Adapter_Socket
 */
#require 'Zend/Http/Client/Adapter/Socket.php';

/**
 * The test adapter creates and manages fixtures.
 */
class DeskTest_Adapter extends Zend_Http_Client_Adapter_Curl
{

  /**
   * Parameters array for connect
   *
   * @var array
   */
  protected $_connect_params = array();

  /**
   * The request method
   *
   * @var string
   */
  protected $_method;

  /**
   * The request uri
   *
   * @var Zend_Uri
   */
  protected $_uri;

  /**
   * The request headers
   *
   * @var array
   */
  protected $_headers;

  /**
   * Filename of the fixture
   *
   * @var string
   */
  protected $_fixture_filename;

  public static $test = 'Testing';

  /**
   * The fixture
   *
   * @var mixed
   */
  protected $_fixture;

  public function connect($host, $port = 80, $secure = false)
  {
    $this->_connect_params = array($host, $port, $secure);
  }

  public function write($method, $uri, $httpVersion = 1.1, $headers = array(), $body = '')
  {
    $this->_method  = $method;
    $this->_uri     = $uri;
    $this->_headers = $headers;

    $fixture = $this->getFixture();

    // check if we have this stored
    if (!is_null($fixture)) {
      return $fixture['request'];
    } else {
      parent::connect(
        $this->_connect_params[0],
        $this->_connect_params[1],
        $this->_connect_params[2]
      );

      $request = parent::write($method, $uri, $httpVersion, $headers, $body);

      $file = $this->getFixtureFilename();
      if (!file_exists(dirname($file))) { mkdir(dirname($file), 0777, true); }
      yaml_emit_file($file, [
        'request' => preg_replace('/^Authorization:.*\r?\n/m', '', $request),
        'response' => $this->_response
      ]);

      return $request;
    }
  }

  public function read()
  {
    $fixture = $this->getFixture();

    if (!is_null($fixture)) {
      return $fixture['response'];
    } else {
      return parent::read();
    }
  }

  protected function getFixtureFilename()
  {
    if (is_null($this->_fixture_filename)) {
      // remove auth header
      $headers = array_merge(array(), $this->_headers);
      foreach ($headers as $idx => $header) {
        if (strpos($header, 'Authorization') !== false) {
          unset($headers[$idx]);
        }

        $type = strpos($header, 'Basic') === false ? 'OAuth' : 'Basic';
        array_push($headers, "X-Authentication-Method: $type");
      }

      $this->_fixture_filename = DeskTest_TestCase::$fixturesPath . '/'
                               . $this->_method . $this->_uri->getPath()
                               . '/' . md5(implode($headers)) . '.yml';
    }
    return $this->_fixture_filename;
  }

  protected function getFixture()
  {
    if (is_null($this->_fixture) && file_exists($this->getFixtureFilename())) {
      $this->_fixture = yaml_parse_file($this->getFixtureFilename());
    }
    return $this->_fixture;
  }
}
