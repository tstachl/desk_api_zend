<?php

/**
 * @covers Desk_Defaults
 */
class Desk_DefaultsTest extends PHPUnit_Framework_TestCase
{

  public function testGetOptions()
  {
    $defaults = Desk_Defaults::getOptions();
    $this->assertArrayHasKey('username', $defaults);
    $this->assertArrayHasKey('password', $defaults);
    $this->assertArrayHasKey('consumerKey', $defaults);
    $this->assertArrayHasKey('consumerSecret', $defaults);
    $this->assertArrayHasKey('token', $defaults);
    $this->assertArrayHasKey('tokenSecret', $defaults);
    $this->assertArrayHasKey('endpoint', $defaults);
    $this->assertArrayHasKey('connectionOptions', $defaults);
  }

  public function testGetUsername()
  {
    $username = 'username';
    putenv("DESK_USERNAME=$username");
    $this->assertEquals($username, Desk_Defaults::getUsername());
    $username = '';
    putenv("DESK_USERNAME=$username");
    $this->assertEquals($username, Desk_Defaults::getUsername());
  }

  public function testGetPassword()
  {
    $password = 'password';
    putenv("DESK_PASSWORD=$password");
    $this->assertEquals($password, Desk_Defaults::getPassword());
    $password = '';
    putenv("DESK_PASSWORD=$password");
    $this->assertEquals($password, Desk_Defaults::getPassword());
  }

  public function testGetConsumerKey()
  {
    $consumerKey = 'consumerKey';
    putenv("DESK_CONSUMER_KEY=$consumerKey");
    $this->assertEquals($consumerKey, Desk_Defaults::getConsumerKey());
    $consumerKey = '';
    putenv("DESK_CONSUMER_KEY=$consumerKey");
    $this->assertEquals($consumerKey, Desk_Defaults::getConsumerKey());
  }

  public function testGetConsumerSecret()
  {
    $consumerSecret = 'consumerSecret';
    putenv("DESK_CONSUMER_SECRET=$consumerSecret");
    $this->assertEquals($consumerSecret, Desk_Defaults::getConsumerSecret());
    $consumerSecret = '';
    putenv("DESK_CONSUMER_SECRET=$consumerSecret");
    $this->assertEquals($consumerSecret, Desk_Defaults::getConsumerSecret());
  }

  public function testGetToken()
  {
    $token = 'token';
    putenv("DESK_TOKEN=$token");
    $this->assertEquals($token, Desk_Defaults::getToken());
    $token = '';
    putenv("DESK_TOKEN=$token");
    $this->assertEquals($token, Desk_Defaults::getToken());
  }

  public function testGetTokenSecret()
  {
    $tokenSecret = 'tokenSecret';
    putenv("DESK_TOKEN_SECRET=$tokenSecret");
    $this->assertEquals($tokenSecret, Desk_Defaults::getTokenSecret());
    $tokenSecret = '';
    putenv("DESK_TOKEN_SECRET=$tokenSecret");
    $this->assertEquals($tokenSecret, Desk_Defaults::getTokenSecret());
  }

  public function testGetEndpoint()
  {
    $endpoint = getenv('DESK_ENDPOINT') || 'endpoint';
    putenv("DESK_ENDPOINT=$endpoint");
    $this->assertEquals($endpoint, Desk_Defaults::getEndpoint());
  }

  public function testGetConnectionOptions()
  {
    $connectionOptions = Desk_Defaults::getConnectionOptions();
    $this->assertArrayHasKey('maxredirects', $connectionOptions);
    $this->assertArrayHasKey('strict', $connectionOptions);
  }

  public function testChangeConnectionOptions()
  {
    Desk_Defaults::$connectionOptions = array('test' => 'test');
    $connectionOptions = Desk_Defaults::getConnectionOptions();
    $this->assertArrayHasKey('test', $connectionOptions);
  }

}
