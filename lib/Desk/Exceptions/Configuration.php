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
 * @package    Desk_Exceptions_Configuration
 * @copyright  Copyright (c) 2013 Salesforce.com Inc. (http://www.salesforce.com)
 */

/**
 * @see Desk_Exception;
 */
#require_once 'Desk/Exception.php';

/**
 * This exception gets throw in case of exceptions in the configuration.
 *
 * Possible exceptions:
 *  - endpoint is not a valid url
 *  - user credentials are not valid
 */
class Desk_Exceptions_Configuration extends Desk_Exception
{}
