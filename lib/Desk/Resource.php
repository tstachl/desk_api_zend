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
 * @package    Desk_Resource
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Varien_Object
 */
#require_once 'Varien/Object.php';

/**
 * @see Desk_Exception
 */
#require_once 'Desk/Exception.php';

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * @see Zend_Uri
 */
#require_once 'Zend/Uri.php';

class Desk_Resource
{
  /**
   * Setter/Getter underscore transformation cache
   *
   * @var array
   */
  protected static $_underscoreCache = array();

  /**
   * The links
   *
   * @var Varien_Object
   */
  protected $_links;

  /**
   * The embedded
   *
   * @var Varien_Object
   */
  protected $_embedded;

  /**
   * The data
   *
   * @var Varien_Object
   */
  protected $_data;

  /**
   * The API client to be used.
   *
   * @var Desk_Client
   */
  private $_client = null;

  /**
   * Indicator of the load state for this resorce.
   *
   * @var boolean
   */
  private $_loaded = false;

  /**
   * Builds a self link array.
   *
   * @return array
   */
  public static function buildSelfLink($link)
  {
    if (is_string($link)) { $link = array('href' => $link); }
    return array('_links' => array('self' => $link));
  }

  /**
   * Unsets an arrays key after returning the value
   *
   * @param  array  $arr   array that'll be used
   * @param  string $key   the key to unset
   * @return mixed         the value to be returned
   */
  public static function arrayRemove(&$arr = array(), $key = null)
  {
    if (!isset($arr[$key])) return null;
    $value = $arr[$key];
    unset($arr[$key]);
    return $value;
  }

  /**
   * Constructor
   *
   * @param Desk_Client $client     the client to be used
   * @param array       $definition the resource definition
   * @param boolean     $loaded     is the current resource loaded
   */
  public function __construct(Desk_Client $client, $definition = array(), $loaded = false)
  {
    $this->_client    = $client;
    $this->_setupData($definition, $loaded);
  }

  /**
   * Creates a new resource from the currents resources base.
   *
   * @param  array  $params the payload for the new resource
   * @return Desk_Resource
   */
  public function create($params = array())
  {
    $baseUrl  = $this->_cleanBaseUrl();
    $response = $this->_client->post($baseUrl, $params);
    $body     = Zend_Json::decode($response->getBody());

    if ($response->isSuccessful()) {
      return new Desk_Resource($this->_client, $body, true);
    } else {
      throw new Desk_Exception($body['message'], $response->getStatus());
    }
  }

  /**
   * Allows you to update a resource.
   *
   * @param  array  $params changes that should be made to this resource
   * @return Desk_Resource
   */
  public function update($params = array())
  {
    $changes = $this->_filterUpdateActions($params);

    foreach ($params as $key => $value) {
      if ($this->_data->hasData($key)) {
        $this->_data->setData($key, $value);
      }
    }

    foreach ($this->_data->getData() as $key => $value) {
      if ($this->_data->getOrigData($key) !== $value) {
        $changes[$key] = $value;
      }
    }

    $response = $this->_client->patch($this->getHref(), $changes);
    $body     = Zend_Json::decode($response->getBody());

    if ($response->isSuccessful()) {
      return $this->_setupData($body);
    } else {
      throw new Desk_Exception($body['message'], $response->getStatus());
    }
  }

  /**
   * Allows you to destroy resources you no longer need.
   *
   * @return boolean
   */
  public function delete()
  {
    $response = $this->_client->delete($this->getHref());
    return $response->isSuccessful();
  }

  /**
   * Allows you to search resources.
   *
   * @param  mixed  $params search parameters
   * @return Desk_Resource
   */
  public function search($params)
  {
    if (is_string($params)) { $params = array('q' => $params); }

    $uri = Zend_Uri::factory($this->_client->getEndpoint() . $this->_cleanBaseUrl() . '/search');
    $uri->setQuery($params);
    $uri = self::buildSelfLink($uri->getPath() . '?' . $uri->getQuery());

    return new Desk_Resource($this->_client, $uri);
  }

  /**
   * Allows finding a resource by id
   *
   * @param  string $id
   * @param  array  $options
   * @return Desk_Resource
   */
  public function find($id, $params = array())
  {
    $baseUrl = $this->_cleanBaseUrl();
    $res = new Desk_Resource($this->_client, self::buildSelfLink("$baseUrl/$id"));
    if (array_key_exists('embed', $params)) {
      $res->embed($params['embed']);
    }
    return $res->_execute();
  }

  /**
   * Allows embedding resources
   *
   * @param  string|array $embeds
   * @return Desk_Resource
   */
  public function embed($embed)
  {
    if (is_string($embed)) { $embed = array($embed); }
    $this->queryParams(array('embed' => implode(',', $embed)));
    return $this;
  }

  /**
   * Returns a new resource based on the url.
   *
   * @param  string $url
   * @return Desk_Resource
   */
  public function byUrl($url)
  {
    return new Desk_Resource($this->_client, self::buildSelfLink($url));
  }

  /**
   * Return the self link
   *
   * @return string
   */
  public function getHref()
  {
    $self = $this->_links->getSelf();
    return $self['href'];
  }

  /**
   * Sets the self link
   *
   * @param  string $url
   * @return Desk_Resource
   */
  public function setHref($url)
  {
    $this->_links->setSelf(array('href' => $url));
    return $this;
  }

  /**
   * Returns the resource type if set
   *
   * @return mixed
   */
  public function getResourceType()
  {
    $self = $this->_links->getSelf();
    return $self['class'];
  }

  /**
   * Return the current page number
   *
   * @return number
   */
  public function getPage()
  {
    if (!array_key_exists('page', $this->queryParams())) {
      $this->_execute();
    }

    $query = $this->queryParams();

    return $query['page'];
  }

  /**
   * Set the current page number
   *
   * @param  number $page
   * @return Desk_Resource
   */
  public function setPage($page)
  {
    $this->queryParams(array('page' => $page));
    return $this;
  }

  /**
   * Return the current per page number
   *
   * @return number
   */
  public function getPerPage()
  {
    if (!array_key_exists('per_page', $this->queryParams())) {
      $this->_execute();
    }

    $query = $this->queryParams();

    return $query['per_page'];
  }

  /**
   * Set the per page query param
   *
   * @param  number $per_page
   * @return Desk_Resource
   */
  public function setPerPage($per_page)
  {
    $this->queryParams(array('per_page' => $per_page));
    return $this;
  }

  /**
   * Sets or returns the query params.
   *
   * @param  mixed $params
   * @return array
   */
  public function queryParams($params = null)
  {
    $uri = Zend_Uri::factory($this->_client->getEndpoint() . $this->getHref());

    if (is_null($params)) { return $uri->getQueryAsArray(); }

    $uri->addReplaceQueryParameters($params);
    $params = $uri->getQueryAsArray();

    $uri = $uri->getPath() . ($uri->getQuery() ? '?' . $uri->getQuery() : '');

    $this->setHref($uri);

    return $params;
  }

  /**
   * Reloads the current resource
   *
   * @return Desk_Resource
   */
  public function reload()
  {
    return $this->_execute(true);
  }

  /**
   * Set/Get attribute wrapper
   *
   * @param   string $method
   * @param   array $args
   * @return  mixed
   */
  public function __call($method, $args)
  {
    if (!$this->_loaded) $this->_execute();

    $key = $this->_underscore(substr($method,3));

    switch (substr($method, 0, 3)) {
      case 'get':
        if ($this->_embedded->hasData($key)) {
          return $this->_getEmbeddedResource($key);
        }

        if ($this->_links->hasData($key)) {
          return $this->_getLinkedResource($key);
        }

        if ($this->_data->hasData($key)) {
          return $this->_data->getData($key);
        }

      case 'set':
        if ($this->_data->hasData($key)) {
          return $this->_data->setData($key, isset($args[0]) ? $args[0] : null);
        }

      case 'has':
        return $this->_embedded->hasData($key) ||
               $this->_links->hasData($key) ||
               $this->_data->hasData($key);
    }

    return null;
  }

  protected function _getEmbeddedResource($key)
  {
    $value = $this->_embedded->getData($key);

    if (!isset($value['_links']) && !($value[0] instanceof Desk_Resource)) {
      foreach ($value as $k => $v) {
        $value[$k] = new Desk_Resource($this->_client, $v, true);
      }

      $this->_embedded->setData($key, $value);
    }

    if (isset($value['_links']) && !($value instanceof Desk_Resource)) {
      $value = new Desk_Resource($this->_client, $value, true);
      $this->_embedded->setData($key, $value);
    }

    return $value;
  }

  protected function _getLinkedResource($key)
  {
    $value = $this->_links->getData($key);

    if (!is_null($value) && !($value instanceof Desk_Resource)) {
      $value = new Desk_Resource($this->_client, self::buildSelfLink($value));
      $this->_links->setData($key, $value);
    }

    return $value;
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
   * Sets up links, embedded and data
   *
   * @param  array $data
   * @return Desk_Resource
   */
  protected function _setupData($data = array(), $loaded = true)
  {
    $this->_links    = new Varien_Object(self::arrayRemove($data, '_links'));
    $this->_embedded = new Varien_Object(self::arrayRemove($data, '_embedded'));
    $this->_data     = new Varien_Object($data);
    $this->_data->setOrigData();
    $this->_loaded   = $loaded;
    return $this;
  }

  /**
   * Cleans the current url to the main object and returns it.
   *
   * @return string base url of the current resource
   */
  private function _cleanBaseUrl()
  {
    $pattern = array('/\/search$/', '/\/\d+$/');
    $explode = explode('?', $this->getHref());
    return preg_replace($pattern, '', $explode[0]);
  }

  /**
   * Helper function that loads the current resource.
   *
   * @param  boolean $reload
   * @return Desk_Resource
   */
  private function _execute($reload = false)
  {
    if ($this->_loaded && !$reload) return $this;

    $response = $this->_client->get($this->getHref());
    $body     = Zend_Json::decode($response->getBody());

    if ($response->isSuccessful()) {
      return $this->_setupData($body);
    } else {
      throw new Desk_Exception($body['message'], $response->getStatus());
    }
  }

  /**
   * Filter update_action params
   *
   * @param  array $params
   * @return array
   */
  private function _filterUpdateActions($params)
  {
    $retval = array();
    foreach ($params as $key => $value) {
      if (strpos($key, 'update_action') !== false) {
        $retval[$key] = $value;
      }
    }
    return $retval;
  }
}
