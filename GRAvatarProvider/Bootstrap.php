<?php

/**
 * Allow to use "GRAvatar" provider in case there's no avatar selected.
 * @author Yahav Gindi Bar
 * @copyright Yahav Gindi Bar
 * @since San, 12/19/2011 08:37:21 GMT
 */
class PearAddon_GRAvatarProvider extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4eeedbfb-9258-428b-81c4-097c60433636";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"GRAvatar avatar provider";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"Add \"GRAvatar\" avatars provider support to set the user avatar in case the user did not select any avatar.";
	
	/**
	 * The addon author
	 * @var String
	 */
	var $addonAuthor				=	"Yahav Gindi Bar";
	
	/**
	 * The addon author website
	 * @var String
	 */
	var $addonAuthorWebsite		=	"http://yahavgindibar.com";
	
	/**
	 * The addon version
	 * @var String
	 */
	var $addonVersion			=	"1.0.0.0";
	
	/**
	 * Initialize the addon. this function is called each time the addon is being used, as a module, or as an action.
	 * Used to register events, load classes etc.
	 * @return Void
	 */
	function initialize()
	{
		if ( parent::initialize() )
		{
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_SETUP_MEMBER_DATA, array($this, 'setupGRAvatar'));
			return true;
		}
		
		return false;
	}
	
	/**
	 * Event delegate: setup Gravatar
	 * @param Array $memberData
	 * @param PearNotification $notification
	 */
	function setupGRAvatar($memberData, $notification)
	{
		//----------------------------------
		//	Fallback for members who don't have avatar
		//----------------------------------
		if ( $memberData['member_id'] > 0 AND $memberData['member_avatar_type'] == 'default' )
		{
			//----------------------------------
			//	Gravatar provides us a nice SSL (https://) wrapper
			//	so we can use it in secure section. Lets select the relevant base url
			//----------------------------------
			$baseUrl						=	"http://www.gravatar.com";
			if ( $this->pearRegistry->getEnv('HTTPS') )
			{
				$baseUrl					=	"https://secure.gravatar.com";
			}
			
			//----------------------------------
			//	Set
			//----------------------------------
			/** Just navigate the URL to gravatar. The images provided by gavatar are squares,
			just like our default image, so any default size we've defined before are valid (@see PearResponse::initialize) **/
			$memberData['member_avatar']				= $baseUrl . '/avatar/' . md5( strtolower( $memberData['member_email'] ) ) . '?s=150&amp;d=' . urlencode($this->pearRegistry->response->imagesUrl . '/Icons/Profile/default-avatar.png');
			$memberData['member_avatar_type']		= 'gravatar';
			
			//----------------------------------
			//	Stop the events flow, since we don't have "no gravatar" fallback
			//----------------------------------
			
			$notification->cancelNotification();
		}
		
		return $memberData;
	}
}
