<?php

/**
 * @covers Desk_Client
 */
class Desk_ClientTest extends \DeskTest_TestCase
{
  public function testInheritsFromConfiguration()
  {
    putenv("DESK_USERNAME=un");
    putenv("DESK_PASSWORD=pw");
    putenv("DESK_ENDPOINT=https://devel.desk.com");

    $client = new Desk_Client();
    $this->assertEquals('un', $client->getUsername());
    $this->assertEquals('https://devel.desk.com', $client->getEndpoint());

    putenv("DESK_USERNAME=");
    putenv("DESK_PASSWORD=");
    putenv("DESK_ENDPOINT=");
  }

  public function testOptionsOverrideDefaultsOnConstruct()
  {
    $client = new Desk_Client(['username' => 'un']);
    $this->assertEquals('un', $client->getUsername());
  }

  public function testOptionsOverrideDefaultsAfterConstruct()
  {
    $client = new Desk_Client();
    $client->configure(function($config) {
      $config->setUsername('un');
      $config->setPassword('pw');
    });

    $this->assertEquals('un', $client->getUsername());
  }

  public function testOptionsAllowsZendConfig()
  {
    $config = new Zend_Config(['username' => 'un']);
    $client = new Desk_Client($config);
    $this->assertEquals('un', $client->getUsername());
  }

  public function testGet()
  {
    $response = $this->getClient()->get('/api/v2/cases/3014');
    $this->assertEquals('Testing Quick Case', Zend_Json::decode($response->getBody())['subject']);
  }

  public function testPost()
  {
    $response = $this->getClient()->post('/api/v2/topics', ['name' => 'Test Topic']);
    $this->assertEquals('Test Topic', Zend_Json::decode($response->getBody())['name']);
  }

  public function testPatch()
  {
    $response = $this->getClient()->patch('/api/v2/topics/655433', ['name' => 'Test Updated Topic']);
    $this->assertEquals('Test Updated Topic', Zend_Json::decode($response->getBody())['name']);
  }

  public function testDelete()
  {
    $response = $this->getClient()->delete('/api/v2/topics/655433');
    $this->assertEquals(204, $response->getStatus());
  }

  public function testHead()
  {
    $response = $this->getClient()->head('/api/v2/cases/3014');
    $this->assertEquals('"92dbc7f07d262c8f9d50eadf704fc87c"', $response->getHeader('etag'));
  }

  public function testOauthGet()
  {
    $response = $this->getClient('oauth')->get('/api/v2/cases/3014');
    $this->assertEquals('Testing Quick Case', Zend_Json::decode($response->getBody())['subject']);
  }

  public function testOauthPost()
  {
    $response = $this->getClient('oauth')->post('/api/v2/topics', ['name' => 'Test Topic']);
    $this->assertEquals('Test Topic', Zend_Json::decode($response->getBody())['name']);
  }

  public function testOauthPatch()
  {
    $response = $this->getClient('oauth')->patch('/api/v2/topics/655435', ['name' => 'Test Updated Topic']);
    $this->assertEquals('Test Updated Topic', Zend_Json::decode($response->getBody())['name']);
  }

  public function testOauthDelete()
  {
    $response = $this->getClient('oauth')->delete('/api/v2/topics/655435');
    $this->assertEquals(204, $response->getStatus());
  }

  /**
   * Not really expected but that's what we get!?!
   *
   * @expectedException        Zend_Oauth_Exception
   * @expectedExceptionMessage Invalid method: HEAD
   */
  public function testOauthHead()
  {
    $response = $this->getClient('oauth')->head('/api/v2/cases/3014');
    $this->assertEquals('"92dbc7f07d262c8f9d50eadf704fc87c"', $response->getHeader('etag'));
  }

  public function test429Error()
  {
    $response = $this->getClient()->get('/api/v2/testing');
    $this->assertEquals(429, $response->getStatus());
  }

  public function testPatchMethodWithCurl()
  {
    $client = new Desk_Client([
      'username' => 'un',
      'password' => 'pw',
      'endpoint' => 'https://devel.desk.com'
    ]);
    $client->setConnectionOptions(array_merge(
      Desk_Defaults::$connectionOptions,
      ['adapter' => 'Zend_Http_Client_Adapter_Curl']
    ));

    $response = $client->patch('/api/v2/cases/3014', []);
    $this->assertEquals(401, $response->getStatus());
  }

  public function testMagic()
  {
    $cases = $this->getClient()->getCases(['page' => 1]);
    $this->assertInstanceOf('Desk_Resource', $cases);
    $this->assertEquals('/api/v2/cases?page=1', $cases->getHref());
    $this->assertEquals(1, $cases->getPage());
  }

  public function testMagicCache()
  {
    $cases = $this->getClient()->getCases(['page' => 1]);
    $this->assertInstanceOf('Desk_Resource', $cases);
  }

  /**
   * @expectedException        Desk_Exception
   * @expectedExceptionMessage Method `buxtehude' not supported.
   */
  public function testCallBuxtehude()
  {
    $this->getClient()->buxtehude();
  }
}
