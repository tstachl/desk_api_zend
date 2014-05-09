<?php

/**
 * @covers Desk_Configuration
 */
class Desk_ConfigurationTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var Configuration
   */
  private $config;

  public function setUp()
  {
    $this->config = new Desk_Configuration();
  }

  public function testSetEmptyEndpoint()
  {
    $this->assertInstanceOf('Desk_Configuration', $this->config->setEndpoint(''));
  }

  /**
   * @expectedException Desk_Exceptions_Configuration
   */
  public function testSetInvalidEndpoint()
  {
    $this->config->setEndpoint('thisshouldnotwork');
  }

  /**
   * @expectedException Desk_Exceptions_Configuration
   */
  public function testSetInvalidProtocolOnEndpoint()
  {
    $this->config->setEndpoint('http://devel.desk.com');
  }

  public function testConfigureUsingLambda()
  {
    $this->config->configure(function($client) {
      $client->setUsername('username');
      $client->setPassword('password');
      $client->setConsumerKey('consumer_key');
      $client->setConsumerSecret('consumer_secret');
      $client->setToken('token');
      $client->setTokenSecret('tokenSecret');
      $client->setEndpoint('https://devel.desk.com');
    });

    $this->assertEquals('username', $this->config->getUsername());
    $this->assertEquals('consumer_key', $this->config->getConsumerKey());
    $this->assertEquals('token', $this->config->getToken());
    $this->assertEquals('https://devel.desk.com', $this->config->getEndpoint());
  }

  public function testReset()
  {
    $this->config->setUsername('username');
    $this->config->reset();
    $this->assertEmpty($this->config->getUsername());
  }

  public function testHasCredentials()
  {
    $this->config->reset();
    $this->assertFalse($this->config->hasCredentials());
    $this->config->setUsername('username')->setPassword('password');
    $this->assertTrue($this->config->hasCredentials());
    $this->config->reset();
    $this->assertFalse($this->config->hasCredentials());
    $this->config->setConsumerKey('ck')
                 ->setConsumerSecret('cs')
                 ->setToken('to')
                 ->setTokenSecret('ts');
    $this->assertTrue($this->config->hasCredentials());
  }

  /**
   * @expectedException Desk_Exceptions_Configuration
   */
  public function testValidateCredentials()
  {
    $this->config->reset();
    $this->config->configure(function($client) {
      $client->setUsername('username');
      $client->setEndpoint('https://devel.desk.com');
    });
  }

  public function testGetConnectionOptions()
  {
    $this->config->reset();
    $options = $this->config->getConnectionOptions();
    $this->assertArrayHasKey('maxredirects', $options);
    $this->assertArrayHasKey('strict', $options);
  }
}
