<?php

class PearAddon_Calendar extends PearAddon
{
	/**
	 * The addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f08c255-2cb4-47a3-acb2-08aee3567d90";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Calendar";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This addon will add calendar abilities to your site.";
	
	/**
	 * The addon author
	 * @var String
	 */
	var $addonAuthor				=	"Quartz Technologies, Ltd.";
	
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
	 * Initialize the addon, this function is called
	 * each time the addon is being used, as an module, or as a action.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			//	Custom initialization code...
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get the site actions that the addon provides
	 * @return Array
	 */
	function getSiteActions()
	{
		return array(
			'calendar'			=>	array('calendar', 'Calendar', 'Calendar',)		
		);
	}
	
	
	/**
	 * Perform the steps in order to install the addon
	 * @return Void
	 */
	function installAddon()
	{
		
	}
	
	/**
	 * Perform the steps in order to uninstall the addon
	 * @return Void
	 */
	function uninstallAddon()
	{
		
	}
}

?>