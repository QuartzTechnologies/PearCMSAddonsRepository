<?php
 
/**
 *
 * Copyright (C) 2012 Yahav Gindi Bar
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
 * @copyright   $Copyrights:$
 * @category    PearCMS
 * @package     PearCMS Addons
 * @license     Apache License Version 2.0  (http://www.apache.org/licenses/LICENSE-2.0)
 * @author      Yahav Gindi Bar
 * @version     1
 * @link			http://yahavgindibar.com
 * @since       Mon, 27 Feb 2012 22:10:07 +0000
 */
 
class PearAddon_ContactUs extends PearAddon
{
    /**
     * Addon UUID
     * @var String
     */
    var $addonUUID              =   "4f4b7d70-c740-48cf-bb11-01b96988f59b";
     
    /**
     * The addon name
     * @var String
     */
    var $addonName              =   "Contact Us";
     
    /**
     * The addon description
     * @var String
     */
    var $addonDescription       =   "";
     
    /**
     * The addon author
     * @var String
     */
    var $addonAuthor            =   "Yahav Gindi Bar";
     
    /**
     * The addon author website
     * @var String
     */
    var $addonAuthorWebsite     =   "http://yahavgindibar.com";
     
    /**
     * The addon version
     * @var String
     */
    var $addonVersion           =   "1.0.0";
     
    /**
     * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
     * Used to register events, load classes etc.
     * @return Void
     */
    function initialize()
    {
        if ( parent::initialize() )
        {
            //  Initialization code here...
            return true;
        }
         
        return false;
    }
	
    
    /**
     * Get the available site actions
     * @return Array
     */
    function getSiteActions()
	{
	    	return array(
			// QueryString param => array(file name, class name, session location key)
			'index'		=>	array('ContactUs', 'Index', 'contactus')	
	    );
    }
    
    /**
     * Get the available control panel actions
     * @return Array
     */
    function getCPActions()
    {
	    	return array(
	    			// QueryString param => array(file name, class name, session location key)
	    	);
    }
    
    /**
     * Get the default site action key
     * @return String
     */
    function getDefaultSiteAction()
    {
 	   	return '';
    }
    
    /**
     * Get the default control panel action key
     * @return String
     */
    function getDefaultCPAction()
    {
    		return '';
    }
    
}