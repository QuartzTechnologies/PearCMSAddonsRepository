<?php

/**
 *
 * Copyright (C) 2012 Quartz Technologies, Ltd.
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
 * @copyright	2012 Quartz Technologies, Ltd.
 * @category		PearCMS
 * @package		PearCMS Addons
 * @license		Apache License Version 2.0	(http://www.apache.org/licenses/LICENSE-2.0)
 * @author		Quartz Technologies, Ltd.
 * @version		1
 * @link			http://pearcms.com
 * @since		Fri, 03 Aug 2012 18:44:09 +0000
 */

class PearAddon_APCacheCLayer extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"501c1bf9-c388-449e-95d3-032dd3ec3145";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"APC Caching Layer";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"Provide an alternative to the built-in caching layer that using the APC extension.";
	
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
	 * Cache package TTL (time-to-live)
	 * @var Integer
	 */
	var $CACHE_PACK_TTL			=	0;
	
	/**
	 * Cache packets prefix
	 * @var String
	 */
	var $CACHE_PREFIX			=	"PearCMS_";
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			/* Register cache notifications */
			
			#	Setting cache values
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CACHE_SET_VALUE, array($this, 'setCache'));
			
			#	Removing cache values
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CACHE_REMOVE_VALUE, array($this, 'removeCache'));
			
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * This method is invoked before the addon is installed.
	 * You may override this method to do preproccessing actions and check for requirements.
	 * @return Boolean|Array - you may return TRUE to start the installation process, otherwise return array contains error(s) string(s) or just FALSE.
	 * @see PearAddon::canInstallAddon()
	 */
	function canInstallAddon()
	{
		if (! extension_loaded('apc') )
		{
			return array('no_apc_extension');
		}
		
		return true;
	}

	/**
	 * Set cache value
	 * @param PearNotification $notification
	 */
	function setCache($notification)
	{
		/* Remove the old cache */
		$this->__removeCache($notification->notificationArgs['key']);
		
		/* Create new packet */
		$this->__createCache($notification->notificationArgs['key'], $notification->notificationArgs['value'], $this->CACHE_PACK_TTL);
	}
	
	/**
	 * Remove cache value
	 * @param PearNotification $notification
	 */
	function removeCache($notification)
	{
		/* Remove the old cache */
		$this->__removeCache($notification->notificationArgs['key']);
	}
	
	
	/**
	 * Internal function: remove cache value from the APC cache layer
	 * @param String $key
	 */
	function __removeCache($key)
	{
		apc_delete(md5($this->CACHE_PREFIX . $key));
	}
	
	function __createCache($key, $value, $ttl = 0)
	{
		$ttl = intval( $ttl ) > 0 ? $ttl : 0;
		return apc_store( md5( $this->CACHE_PREFIX . $key ), $value, $ttl );
	}
}