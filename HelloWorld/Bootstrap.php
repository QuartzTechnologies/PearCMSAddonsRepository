<?php

/**
 *
 * Copyright (C) 2011 Quartz Technologies, Ltd.
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
 * @copyright	$Copyrights:$
 * @license		$License:$
 * @category		PearCMS
 * @package		PearCMS Addons
 * @author		$Author:$
 * @version		$Id:$
 * @link			$Link:$
 * @since		$Since:$
 */

if(! defined( "PEARCMS_SYSTEM" ) )
{
	print <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" ><head>    <title>Pear Content Management System - Unreachable page</title><style type="text/css">    body    {    	color:#000000;    	font-size:13pt;    	font-family:Arial, Times New Roman;    }    a    {    	color:#000000;    	font-weight:bold;    	text-decoration:none;    	font-style:italic;    }        a:hover    {    	text-decoration:underline;    }    #title    {    	font-size:22pt;    	font-style:italic;    	font-weight:300;    	padding:5px;    	padding-bottom:0px;    	margin-left:15px;    	margin-bottm:1px;    	font-family:Times New Roman;    }        #hr_break    {    	border:1px solid #e3e3e3;    	color:#e3e3e3;    	padding:2px;    	margin:2px;    	margin-bottom:10px;    	text-align:left;    	width:85%;    }</style></head><body>    <div id="title">Pear Content Management System</div>    <hr id="hr_break" />	Could not open the selected page, it may couse becuse you tried to accross the page directly.<br />	or you tried to go into a forbidden page.<br />	Thanks, <a href="http://pearservices.com/index.php">Quartz Technologies Ltd.</a></body></html>
EOF;
	exit();
}

/**
 * Addon bootstrap used to initialize and setup the HelloWorld addon.
 * The bootstrap class proposes is store and provide general information about the addon such as the addon UUID, key, name, description etc.
 * and run initialization and general code such as custom installation/uninstallation logic, initialization per request code (e.g. for registering event observers using PearNotificationsDispatcher etc.)
 * 
 * 	More information about addon development can be found at PearCMS Codex: http://codex.pearcms.com
 * 
 * @copyright	$C;Copyrights:$
 * @license		$C;License:$
 * @version		$C;Id:$
 * @link			$C;Link:$
 * @since		$C;Since:$
 * @access		Private
 */
class PearAddon_HelloWorld extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f7a2f9c-aa30-42b8-b667-0579f032a921";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Hello World";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This is an sample add-on that examine how to add an action to the AdminCP and the site and display a message in it.";
	
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
			//	Initialization code here...
			return true;
		}
		
		return false;
	}
	

	/**
	 * This method used to get the site action controllers that we wish to register with the PearRequestsDispatcher class.
	 * 
	 * In this example, we'll return only one action because we got only one controller.
	 * @return Array
	 */
	function getSiteActions()
	{
		/**
		 * Note that the URL that'll display this action is:
		 * ${baseUrl}/index.php?addon=${this-addon-key}&load=${query-string-param}
		 * 
		 * so in this case:
		 * http://localhost/dev/index.php?addon=HelloWorld&amp;load=index
		 * 
		 * Same as built-in actions, the rules of default actions is applyed here too, so if no "load" parameter is supplied
		 * the syste uses the default action that provided by the addon.
		 * There are three ways to get the default action:
		 * 	1. If the getSiteActions() method returned array with only one action - it will be the default and no load parameter need to be supplied.
		 * 			(which means in this example addon, we won't need to supply "load=index" since its the default action - its the ONLY action)
		 * 	2. If there're more than one action, the requests dispatcher try to get the default action from the addon getDefaultSiteAction() method,
		 * 		if it retuned a string contains a query-string param that exists in the getSiteActions() returned array - this will be the default action.
		 * 	3. In case the system could not determine what is the default action with the above actions - it uses the first array element.
		 */
		return array(
			// QueryString param => array(file name, class name, session location key)
			'index'				=> array('Index', 'Index', 'index')
		);
	}
	
	/**
	 * This method used to get the AdminCP= action controllers that we wish to register with the PearRequestsDispatcher class.
	 * 
	 * In this example, we'll return only one action because we got only one controller.
	 * @return Array
	 */
	function getCPActions()
	{
		/**
		 * Note that the rules regards URLs and default actions are just like in the site parameters:
		 * 
		 * URL:
		 * 	${baseUrl}/Admin/index.php?authsess=...&addon=${addon-key}&load=${query-string-param}
		 * 
		 * In this example addon - example URL:
		 * http://localhost/dev/Admin/index.php?authsess=...&addon=HelloWorld&load=index
		 * 
		 * NOTE THAT THE RULES ABOUT DEFAULT ACTIONS ARE THE SAME LIKE THE SITE ACTIONS, BUT INSTEAD OF getDefaultSiteAction USING getDefaultCPAction().
		 */
		return array(
			// QueryString param => array(file name, class name, session location key)
			'index'				=> array('Index', 'Index', 'index')
		);
	}
	
	/**
	 * Get the default site action key.
	 * As described in getSiteActions(), this method is relevant only if there're more than one action
	 * but we fill it anyway.
	 * @return String
	 */
	function getDefaultSiteAction()
	{
		return 'index';
	}
	
	/**
	 * Get the default control panel action key.
	 * As described in getCPActions(), this method is relevant only if there're more than one action
	 * but we fill it anyway.
	 * @return String
	 */
	function getDefaultCPAction()
	{
		return 'index';
	}
	

	/**
	 * This is a method that calls when the system install the addon. We shall use this method
	 * in order to execute specific logic to prepare the addon workspace, such as adding DB tables and rows, generating files and content etc.
	 * 
	 * In this example we'll use this table to add the "Hello CP" action to the AdminCP menu.
	 * Remember that although you created a ViewController it won't be displayed in the CP menu unless you add a record to the section_pages table
	 * @return Void
	 */
	function installAddon()
	{
		/**
		 * Step #1:
		 * 	The acp_sections_pages wish to get the section_id we wish the page to be related to.
		 * 	I want to insert the page into the "addons" (this is a section key) section, so I need to covert the section key to section id.
		 * 	I can do it by loading the "cp_sections_and_pages" cache which contains array of sections and pages
		 *  (in the CP it also available by $this->pearRegistry->admin->cpSections, but we can be in the Setup too so we can't rellay on that)
		 *  so we'll load it, and then take from it the section id.
		 */
		$sectionsData		= $this->cache->get('cp_sections_and_pages');
		$sectionId			= $sectionsData['addons']['section_id'];
		
		/**
		 * Step #2:
		 * 	We need to fetch the max page position in order to set our page at position + 1.
		 * 	We can use our cached array in order achieve that.
		 */
		$latestPageData		= end($sectionsData['addons']['section_pages']);
		$newPagePosition		= ( $latestPageData['page_position'] + 1 );
		
		/**
		 * Step #3:
		 * 	Now we got the section id, simply insert the record into the acp_sections_pages table using the insert() method.
		 * 	The standard DB driver shared instance variable is <code>$this->pearRegistry->db</code> but in PearAddon (and extended classes) we've created
		 *  a shortcut - <code>$this->db</code>, so we'll use it.
		 */
		$this->db->insert('acp_sections_pages', array(
			'section_id'				=>	$sectionId,
			'page_key'				=>	'hello-world',
			'page_title'				=>	'Hello CP',
			'page_description'		=>	'This is a simple dummy page that gives you to test addons ability.',
			'page_url'				=>	'addon=HelloWorld',
			'page_groups_access'		=>	'*',
			'page_indexed_in_menu'	=>	1,
			'page_position'			=>	$newPagePosition,
		));
		
		/**
		 * Step #4:
		 * 	Since the sysetm cache the CP sections and pages to reduce resources, YOU MUST REBUILD THE CACHE OTHERWISE YOU WON'T SEE YOUR PAGE.
		 * 	Each cache packet can simply be rebuilded by calling the PearCacheManager rebuild() method.
		 * 	The cache manager shared instance is <code>$this->pearRegistry->cache</code> but PearAddon contains a shortcut that we can use - <code>$this->cache</code>.
		 */
		$this->cache->rebuild('cp_sections_and_pages');
	}

	/**
	 * This is a method that fires when the addon shall be un-installed. You shall use this to reverse the actions you've done in the installAddon() method
	 * and while the addon was installed (e.g. if you've collected images, remove them from the disc etc.)
	 * 
	 * In this example we'll use it in order to remove our "Hello CP" page from the CP menu.
	 * @return Void
	 */
	function uninstallAddon()
	{
		/**
		 * Step #1:
		 * 	Lets remove the section from the database.
		 * 	Its strongly recommended to remove it both by section_id and page_key since it can be same page_key in two different sections.
		 * 	So first, just like we done in the installAddon() method, we need to fetch the section id - we'll do it using the cache.
		 */
		$sectionsData		= $this->cache->get('cp_sections_and_pages');
		$sectionId			= $sectionsData['addons']['section_id'];
		
		/**
		 * Step #2:
		 * 	Simply remove it from the database using the remove() method.
		 */
		$this->db->remove('acp_sections_pages', 'section_id = ' . $sectionId . ' AND page_key = "hello-world"');
		
		/**
		 * Step #3:
		 * 	Just like in the installAddon() method, we've done changes to the sections pages table, so we need
		 * 	to rebuild our cache otherwise the changes won't be applyed until a rebuild will be performed (by the user or the system).
		 */
		$this->cache->rebuild('cp_sections_and_pages');
	}
}