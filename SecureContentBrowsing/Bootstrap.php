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
 * @since		Thu, 15 Mar 2012 00:25:24 +0000
 */

class PearAddon_SecureContentBrowsing extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f6136f4-9940-4071-86f1-2a4fd912bedd";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Secure Content Browsing";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This add-on gives to to define \"force-ssl\" pages which are pages that PearCMS forces the browser to load with SSL (https:// protocol)";
	
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
			//------------------------------
			//	Make sure secure sections setting is ON
			//------------------------------
			if (! $this->settings['allow_secure_sections_ssl'] )
			{
				//return false;
			}
			
			//------------------------------
			//	Load resources
			//------------------------------
			$this->loadLanguageFile('lang_content_secure_browsing');
			
			//------------------------------
			//	Observe carefully...
			//------------------------------
			if ( PEAR_SECTION_SITE )
			{
				/** Modify URL's with the right protocol **/
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_ROUTE_CONTENT_PAGE_BASE_URL, array($this, 'routePageUrl'));
				
				/** Transfer invalid requests **/
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_DISPLAYING_PAGE, array($this, 'displayingPage'));
			}
			else
			{
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_CONTENTMANAGER_RENDER_PAGE_FORM, array($this, 'contentManagerRenderPageManageForm'));
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_CONTENTMANAGER_SAVE_PAGE_FORM, array($this, 'contentManagerSavePageManageForm'));
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Notification filter: route content page base URL
	 * @param String $baseUrl
	 * @param PearNotification $notification
	 * @return Arrya
	 * @abstract We have to check if the routed content page is using SSL, and if so, switch the protocol
	 */
	function routePageUrl($baseUrl, $notification)
	{
		$pageData			=	$notification->notificationArgs['pageData'];
		
		if ( $pageData['page_securebrowsing_force_ssl'] )
		{
			return str_replace('http:/', 'https:/', $baseUrl);
		}
		
		return $baseUrl;
	}
	
	/**
	 * Notification filter: displaying page
	 * @param Array $pageData
	 * @param PearNotification $notification
	 * @return Array
	 * @abstract We want to make sure that the user won't access this page without https:// protocol
	 * 	this filter event fires before validating the member permissions to view this page and in general
	 * 	want to give us the option to modify the page data array, but we can use this notification to make sure that we're in the right protocol
	 * and if not, transfer the user to https.
	 */
	function displayingPage($pageData, $notification)
	{
		//------------------------------
		//	This page requires SSL?
		//------------------------------
		if ( $pageData['page_securebrowsing_force_ssl'] )
		{
			//------------------------------
			//	We're in SSL?
			//------------------------------
			if (! $this->pearRegistry->getEnv('HTTPS') )
			{
				$uri = rtrim( $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $this->pearRegisry->getEnv('REQUEST_URI'), '/' );
				$this->pearRegistry->response->silentTransfer('https://' . $_SERVER['HTTP_HOST'] . $uri, 101);
			}
		}
		
		return $pageData;
	}
	
	/**
	 * Notification filter: display the page manage form in the content manager
	 * @param Array $fields - the form fields
	 * @param PearNotification $notification
	 */
	function contentManagerRenderPageManageForm($fields, $notification)
	{
		$pageData		= $notification->notificationArgs['page'];
		$controller		= $notification->notificationSender;
		
		$fields['page_manage_tab_display']['fields'][ $this->lang['page_securebrowsing_force_ssl_field'] ]
						= $controller->view->yesnoField('page_securebrowsing_force_ssl', $pageData['page_securebrowsing_force_ssl']);
	
		return $fields;
	}
	
	/**
	 * Notification filter: save the page manage form in the DB
	 * @param Array $fields - the form fields
	 * @param PearNotification $notification
	 */
	function contentManagerSavePageManageForm($fields, $notification)
	{
		$fields['page_securebrowsing_force_ssl'] = ( intval($this->request['page_securebrowsing_force_ssl']) === 1 );
		return $fields;
	}
	
	
	function canInstallAddon()
	{
		//------------------------------
		//	Check if we allowed secure sections
		//------------------------------
		if (! $this->settings['allow_secure_sections_ssl'] )
		{
			return array(
				$this->lang['install_error_must_allow_secure_sections']
			);
		}
		
		return true;
	}
	
	function installAddon()
	{
		$this->db->query('ALTER TABLE pear_pages ADD page_securebrowsing_force_ssl TINYINT( 1 ) NOT NULL');
	}
	
	function uninstallAddon()
	{
		$this->db->query('ALTER TABLE pear_pages DROP page_securebrowsing_force_ssl');
	}
}