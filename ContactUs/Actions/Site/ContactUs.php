<?php

class PearAddonSiteViewController_ContactUs_Index extends PearAddonSiteViewController
{
	function execute()
	{
		switch ( $this->request['do'] )
		{
			case 'submit':
				break;
			default:
				return $this->contactUsForm();
				break;
		}
	}
	
	function contactUsForm()
	{
		return $this->absoluteUrl('/ContactUs.js', 'addon_js');
	}
}