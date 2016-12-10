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
 * @since		Wed, 18 Apr 2012 16:06:43 +0000
 */

class PearAddon_HTML5IESupport extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4f8ee693-2f8c-4473-8e71-03f653f31629";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"HTML 5 Internet Explorer Support";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"This add-on brings support to HTML 5 element in Internet Explorer 8 and lower using the \"html5shiv\" JS library.";
	
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
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_PRINT_RESPONSE, array($this, 'healIEBaddnessLikeABoss'));
			return true;
		}
		
		return false;
	}
	
	function healIEBaddnessLikeABoss($value, $notification)
	{
		$browserData = $this->pearRegistry->endUserBrowser();
		if ( $browserData['browser'] == 'ie' )
		{
			if ( version_compare($browserData['version'], '9', '<') )
			{
				$value = preg_replace('@</head>@i', '<script type="text/javascript" src="' . $this->absoluteUrl('/html5shiv/html5.js') . '"></script>' . PHP_EOL . '</head>', $value);
			}
		}
		
		return $value;
	}
}