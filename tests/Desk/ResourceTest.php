<?php

/**
 * @covers Desk_Resource
 */
class Desk_ResourceTest extends \DeskTest_TestCase
{
  public function testBuildSelfLink()
  {
    $this->assertEquals('/momo', Desk_Resource::buildSelfLink('/momo')['_links']['self']['href']);
  }

  public function testArrayRemove()
  {
    $array = ['test' => 'test'];
    Desk_Resource::arrayRemove($array, 'test');
    $this->assertEquals([], $array);
  }

  public function testConstructor()
  {
    $res = new Desk_Resource($this->getClient(), Desk_Resource::buildSelfLink('/test'));
    $this->assertInstanceOf('Desk_Resource', $res);
  }

  public function testCreate()
  {
    $topic = $this->getClient()->getTopics()->create(['name' => 'Test Topic']);
    $this->assertEquals('Test Topic', $topic->getName());
  }

  /**
   * @expectedException        Desk_Exception
   * @expectedExceptionMessage Validation Failed
   */
  public function testCreateFailed()
  {
    $topic = $this->getClient()->getTopics()->create(['subject' => 'Test Topic']);
  }

  public function testUpdate()
  {
    $topic = $this->getClient()->getTopics()->find(655433)->update(['name' => 'Test Updated Topic']);
    $this->assertEquals('Test Updated Topic', $topic->getName());
  }

  /**
   * @expectedException        Desk_Exception
   * @expectedExceptionMessage Resource Not Found
   */
  public function testUpdateFailed()
  {
    $topic = $this->getClient()->getTopics()->find(655433)->update(['name' => 'Test Topic']);
  }

  public function testDestroy()
  {
    $this->assertTrue($this->getClient()->getTopics()->find(655433)->delete());
  }

  public function testSearch()
  {
    $search = $this->getClient()->getCases()->search(['status' => 'pending']);
    $this->assertInstanceOf('Desk_Resource', $search);
    $this->assertEquals('/api/v2/cases/search?status=pending', $search->getHref());
  }

  public function testSearchQ()
  {
    $search = $this->getClient()->getCases()->search('pending');
    $this->assertEquals('/api/v2/cases/search?q=pending', $search->getHref());
  }

  public function testFind()
  {
    $find = $this->getClient()->getTopics()->find(655433);
    $this->assertEquals('/api/v2/topics/655433', $find->getHref());
  }

  public function testFindEmbed()
  {
    $find = $this->getClient()->getCases()->find(3015, ['embed' => 'customer']);
    $this->assertEquals('Thomas', $find->getCustomer()->getFirstName());
  }

  public function testEmbed()
  {
    $embed = $this->getClient()->getCases()->embed(['customer', 'assigned_user']);
    $this->assertEquals('/api/v2/cases?embed=customer%2Cassigned_user', $embed->getHref());
    $embed = $this->getClient()->getCases()->embed('customer');
    $this->assertEquals('/api/v2/cases?embed=customer', $embed->getHref());
  }

  public function testByUrl()
  {
    $url = $this->getClient()->getCases()->byUrl('/api/v2/topics/655433');
    $this->assertEquals('/api/v2/topics/655433', $url->getHref());
  }

  public function testGetResourceType()
  {
    $ticket = $this->getClient()->getCases()->find(3015);
    $this->assertEquals('case', $ticket->getResourceType());
  }

  public function testGetPage()
  {
    $cases = $this->getClient()->getCases();
    $this->assertEquals(1, $cases->getPage());
  }

  public function testSetPage()
  {
    $cases = $this->getClient()->getCases()->setPage(2);
    $this->assertEquals(2, $cases->getPage());
  }

  public function testGetPerPage()
  {
    $cases = $this->getClient()->getCases();
    $this->assertEquals(50, $cases->getPerPage());
  }

  public function testSetPerPage()
  {
    $cases = $this->getClient()->getCases()->setPerPage(100);
    $this->assertEquals(100, $cases->getPerPage());
  }

  public function testGetLinkedResource()
  {
    $customer = $this->getClient()->getCases()->find(3014)->getCustomer();
    $this->assertInstanceOf('Desk_Resource', $customer);
    $this->assertEquals('/api/v2/customers/176573942', $customer->getHref());
  }

  public function testSetData()
  {
    $topic = $this->getClient()->getTopics()->find(655433);
    $topic->setName('Test Updated Topic');
    $this->assertEquals('Test Updated Topic', $topic->getName());
  }

  public function testHasData()
  {
    $topic = $this->getClient()->getTopics()->find(655433);
    $this->assertTrue($topic->hasName());
    $this->assertFalse($topic->hasBuxtehude());
  }

  public function testCallNull()
  {
    $this->assertNull($this->getClient()->getCases()->thisFunctionDoesntExist());
  }

  public function testGetEntries()
  {
    $cases = $this->getClient()->getCases()->getEntries();
    foreach ($cases as $ticket) {
      $this->assertInstanceOf('Desk_Resource', $ticket);
    }
  }

  /**
   * @expectedException        Desk_Exception
   * @expectedExceptionMessage Resource Not Found
   */
  public function testExecuteThrowsError()
  {
    $this->getClient()->getTopics()->find(1);
  }

  public function testFilterUpdateAction()
  {
    $customer = $this->getClient()->getCustomers()->getEntries()[0];
    $count    = count($customer->getPhoneNumbers());
    $number   = ['type' => 'home', 'value' => '(415) 555-1234'];

    $customer->update([
      'phone_numbers' => [$number],
      'phone_numbers_update_action' => 'append'
    ]);

    $new_count = count($customer->reload()->getPhoneNumbers());

    $this->assertEquals($count + 1, $new_count);
  }
}
