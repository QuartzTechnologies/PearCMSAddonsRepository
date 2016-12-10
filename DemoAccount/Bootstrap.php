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
 * @since		Sat, 17 Mar 2012 03:27:20 +0000
 */
class PearAddon_DemoAccount extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f640498-f544-449f-b7a3-0fc196bc279a";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Demo Account";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This add-on gives you to declare member(s) as \"demo account\", which gives them readonly permissions in the site and AdminCP (based on their group permissions).";
	
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
	 * Array contains allowed actions of forms
	 * @var Array
	 */
	var $allowedForms			=	array(
		'admincp'					=>	array(
		),
		'site'						=>	array(
		),
	);
	
	/**
	 * Array of allowed actions
	 * @var Array
	 */
	var $allowedActions			=	array(
		'admincp'					=>	array(
		),
		'site'						=>	array(
		),
	);
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			//----------------------------------
			//	Are we dealing with the site action? if so, we got to action only if this is a demo account
			//----------------------------------
			if ( PEAR_SECTION_SITE )
			{
				if ( $this->member['member_is_demo_account'] )
				{
					$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, array($this, 'dispatchingActiveController'), 1);
					$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_PRINT_RESPONSE, array($this, 'printResponse'), 1);
					return true;
				}
				
				return false;
			}
			
			//----------------------------------
			//	If we're here, we're in the AdminCP, so first lets check if
			//	we're dealing with demo account
			//----------------------------------
			if ( $this->member['member_is_demo_account'] )
			{
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_DISPATCHING_ACTIVE_CONTROLLER, array($this, 'dispatchingActiveController'), 1);
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_PRINT_RESPONSE, array($this, 'printResponse'), 1);
			}
			
			//----------------------------------
			//	Load resources
			//----------------------------------
			
			$this->loadLanguageFile('lang_demoaccount');
			
			//----------------------------------
			//	Register event to append the yes/no switch in the members form
			//----------------------------------
			
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_MEMBERS_RENDER_MANAGE_FORM, array($this, 'membersRenderManageForm'), 1);
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_MEMBERS_SAVE_MANAGE_FORM, array($this, 'membersSaveManageForm'), 1);
				
			/** Finish **/
			return true;
		}
		
		return false;
	}
	
	/**
	 * Notification filter: dispatching the active controller
	 * @param PearViewController $controller
	 * @param PearNotification $notification
	 * @return PearViewController
	 * @abstract We'll use this event to check if the demo account trying to access restricted area which he or she can not access
	 * and if so, displaying error
	 */
	function dispatchingActiveController($controller, $notification)
	{
		//----------------------------------
		//	We'll disable all incoming post requests except of specified requests
		//----------------------------------
		if ( $this->pearRegistry->getEnv('REQUEST_METHOD') == 'POST' )
		{
			$userSection			=	(PEAR_SECTION_ADMINCP ? 'admincp' : 'site');
			if (! isset($this->allowedActions[ $userSection ][ $this->request['load'] ]) OR ! in_array($this->request['do'], $this->allowedActions[ $userSection ][ $this->request['load'] ]) )
			{
				$this->response->raiseError('demoaccount_no_permissions', 401);
			}
		}
		
		//----------------------------------
		//	Now we have to make sure that we don't access a restricted link.
		//	Its kind of hacky, but lets try to search for specific words
		//----------------------------------
		
		if ( preg_match('@(^|\-)(remove|delete|install|uninstall|exec|execute|shell|bash|move|reorder|rearrange|backup|download|toggle|rebuild|debug)($|\-)@i', $this->request['do']) )
		{
			$this->response->raiseError('demoaccount_no_permissions', 401);
		}
		
		return $controller;
	}
	
	/**
	 * Notification filter: print the response content
	 * @param String $output
	 * @param PearNotification $notification
	 * @return String
	 * @abstract We'll use this method to turn all the inputs in the document into readonly, in order to disable the user (visually) to edit them.
	 */
	function printResponse($output, $notification)
	{
		$userSection			=	(PEAR_SECTION_ADMINCP ? 'admincp' : 'site');
		if (! isset($this->allowedActions[ $userSection ][ $this->request['load'] ]) OR ! in_array($this->request['do'], $this->allowedActions[ $userSection ][ $this->request['load'] ]) )
		{
			$output = preg_replace('@<input(.*?)>@', '<input disabled="disabled" $1>', $output);
			$output = preg_replace('@<select(.*?)>@', '<select disabled="disabled" $1>', $output);
			$output = preg_replace('@<textarea(.*?)>@', '<textarea disabled="disabled" $1>', $output);
		}
		
		return $output;
	}
	
	
	/**
	 * Notification filter: modify the members manage form
	 * @param Array $fields
	 * @param PearNotification $notification
	 * @return Array
	 * @abstract We'll use this method in order to add the "Member is demo account" field to the members form
	 */
	function membersRenderManageForm($fields, $notification)
	{
		$memberData = $notification->notificationArgs['member'];
		$controller = $notification->notificationSender;
		
		$fields['manage_member_tab_general']['fields'][ $this->lang['member_is_demo_account_field'] ]
					= $controller->view->yesnoField('member_is_demo_account', $memberData['member_is_demo_account']);
		
		return $fields;
	}
	
	/**
	 * Notification filter: save the manage form in the DB
	 * @param Array $fields
	 * @param PearNotification $notification
	 * @return Array
	 * @abstract We'll use this method in order to add the form we've added in the render form to the fields to save in the DB
	 */
	function membersSaveManageForm($fields, $notification)
	{
		$fields['member_is_demo_account'] = intval($this->request['member_is_demo_account']);
		return $fields;
	}
	
	
	
	/**
	 * Install the addon
	 * @return Void
	 */
	function installAddon()
	{
		$this->db->query('ALTER TABLE pear_members ADD member_is_demo_account TINYINT( 1 ) NOT NULL');
	}
	
	/**
	 * Uninstall the addon
	 * @return Void
	 */
	function uninstallAddon()
	{
		$this->db->query('ALTER TABLE pear_members DROP member_is_demo_account');
	}
}