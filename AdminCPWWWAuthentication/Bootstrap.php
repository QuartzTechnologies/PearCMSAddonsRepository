<?php

/**
 *
 * Copyright (C) 2012 Quartz Technologies, LTD.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright	2012 Quartz Technologies, LTD.
 * @category		PearCMS
 * @package		PearCMS Addons
 * @license		Apache License Version 2.0	(http://www.apache.org/licenses/LICENSE-2.0)
 * @author		Quartz Technologies, LTD.
 * @version		1
 * @link			http://pearcms.com
 * @since		Sat, 17 Mar 2012 03:26:01 +0000
 */

class PearAddon_AdminCPWWWAuthentication extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f640449-ecc8-4206-bae2-0fc21249639c";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"AdminCP WWW Authentication";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This add-on provides an alternative way to login into your Admin CP. Instead of displaying the standard HTML form, this add-on display \"WWW Authentication\" (browser built-in dialog) form.";
	
	/**
	 * The addon author
	 * @var String
	 */
	var $addonAuthor				=	"Quartz Technologies, LTD.";
	
	/**
	 * The addon author website
	 * @var String
	 */
	var $addonAuthorWebsite		=	"http://pearcms.com";
	
	/**
	 * The addon version
	 * @var String
	 */
	var $addonVersion			=	"1.0.0";
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			/** We'll use this controller only if we're in the AdminCP section **/
			if ( PEAR_SECTION_ADMINCP )
			{
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, array($this, 'dispatchingActiveController'));
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Notification filter: dispatching the active controller
	 * @param PearViewController $controller
	 * @param PearNotification $notification
	 * @return PearViewController
	 * @abstract We'll use this notification in order to switch the standard authentication controller
	 * with our custom class which provides the WWWAuthentication form
	 */
	function dispatchingActiveController($controller, $notification)
	{
		if ( get_class($controller) == 'PearCPViewController_Authentication' )
		{
			/** Get our custom authentication controller **/
			$customAuthController = $this->loadController('Authentication', PEAR_CONTROLLER_SECTION_CP);
			
			/** Return it so PearCMS will use it instead of the built-in controller **/
			return $customAuthController;
		}
		
		/** We don't need to do anything to other controllers, so just return the orginal controller to continue with the flow **/
		return $controller;
	}
}