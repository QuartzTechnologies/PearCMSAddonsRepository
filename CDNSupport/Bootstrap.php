<?php

/**
 * Set CDN support for images, stylesheets and javascript files
 * @author Yahav Gindi Bar
 * @copyright Yahav Gindi Bar
 * @since San, 10/16/2011 17:27:34 GMT
 */
class PearAddon_CDNSupport extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4ee5300d-717c-4f4c-892b-21103433cd8a";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"CDN Support";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"Gain support for CDN URL's in order to load themes and js files from external resources.";
	
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
			if ( PEAR_SECTION_SITE )
			{
				/** Do we really selected CDN url? only if we do register as observer (save resources) **/
				if ( ! empty($this->settings['cdn_url']) )
				{
					/** Register to the print response notification to replace the URL's with the CDN ones **/
					$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_PRINT_RESPONSE, array($this, 'convertUrlsToCDN'));
				}
			}
			else
			{
				/** Load language file **/
				$this->loadLanguageFile('lang_cp_cdnsupport');
				
				/** Observe for the system settings form **/
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_SETTINGS_RENDER_GENERAL_FORM, array($this, 'renderGeneralSettings'));
				$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_SETTINGS_SAVE_GENERAL_FORM, array($this, 'saveGeneralSettings'));
			}
			
			return true;
		}
		
		return false;
	}
	
	
	
	/**
	 * Notification: render the system general settings form
	 * @param Array $fields - the general settings form feilds
	 * @param PearNotification $notification
	 * @return Array - the general settings form fields
	 */
	function renderGeneralSettings($fields, $notification)
	{
		$controller = $notification->notificationSender;
		
		$fields['advance_setting_tab_title']['fields'] += array(
			$this->lang['cdn_url_field'] => $controller->view->textboxField('cdn_url', '')
		);
		
		return $fields;
	}
	
	/**
	 * Notification: save the general settings form
	 * @param Array $fields - the fields to save
	 * @param PearNotification $notification
	 * @return Array $fields - the general settings fields to save in "pear_settings" table
	 */
	function saveGeneralSettings($fields, $notification)
	{
		$fields['cdn_url'] = trim($this->request['cdn_url'], '/');
		return $fields;
	}
	
	
	
	/**
	 * Notification: Modify the site response content - convert URLs of images, css and js files in the output content into CDN urls
	 * @param String $outputContent - the site output content
	 * @param PearNotification $notification
	 * @return String
	 */
	function convertUrlsToCDN($outputContent, $notification)
	{
		$outputContent = preg_replace('@src=[\'"]' . preg_quote($this->baseUrl, '@') . '(SystemSources/Addons/.*/Themes|Themes)/([^/]*)/Images/(.*)[\'"]@isU', 'src="' . $this->settings['cdn_url'] . '/$1/$2/Images/$3"', $outputContent);
		$outputContent = preg_replace('@href=[\'"]' . preg_quote($this->baseUrl, '@') . '(SystemSources/Addons/.*/Themes|Themes)/([^/]*)/StyleSheets/(.*)[\'"]@isU', 'href="' . $this->settings['cdn_url'] . '/$1/$2/StyleSheets/$3"', $outputContent);
		$outputContent = preg_replace('@src=[\'"]' . preg_quote($this->baseUrl, '@') . '(SystemSources/Addons/.*/Client/JScripts|Client/Jscripts)/(.*)[\'"]@isU', 'src="' . $this->settings['cdn_url'] . '/$1/$2/$3"', $outputContent);
		
		return $outputContent;
	}
	
	
	function installAddon()
	{
		$this->db->query('ALTER TABLE pear_settings ADD cdn_url VARCHAR( 255 ) NOT NULL');
	}
	
	function uninstallAddon()
	{
		$this->db->query('ALTER TABLE pear_settings DROP cdn_url');
	}
}
