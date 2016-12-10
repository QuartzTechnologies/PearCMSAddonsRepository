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
 * @copyright   2012 Quartz Technologies, LTD.
 * @category        PearCMS
 * @package     PearCMS Addons
 * @license     Apache License Version 2.0  (http://www.apache.org/licenses/LICENSE-2.0)
 * @author      Quartz Technologies, LTD.
 * @version     1
 * @link            http://pearcms.com
 * @since       Tue, 20 Mar 2012 23:24:23 +0000
 */
 
class PearAddon_ACESyntaxEditor extends PearAddon
{
    /**
     * Addon UUID
     * @var String
     */
    var $addonUUID              =   "4f69118a-6c04-4532-aed1-0281ae2a151e";

    /**
     * The addon name
     * @var String
     */
    var $addonName              =   "Code Syntax Editor";
     
    /**
     * The addon description
     * @var String
     */
    var $addonDescription       =   "This add-on modify the built-in forms that receiving some program language input (such as HTML or PHP) and replace the standard \"textarea\" tag with a syntax editor field.";
     
    /**
     * The addon author
     * @var String
     */
    var $addonAuthor                =   "Quartz Technologies, LTD.";
     
    /**
     * The addon author website
     * @var String
     */
    var $addonAuthorWebsite     =   "http://pearcms.com";
     
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
        		$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_CONTENTMANAGER_RENDER_PAGE_FORM, array($this, 'contentManagerPageForm'));
            $this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_BLOCKSMANAGER_RENDER_MANAGE_FORM, array($this, 'blocksManagerForm'));
            $this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_CONTENTLAYOUTS_RENDER_MANAGE_FORM, array($this, 'contentLayoutsManageForm'));
            $this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_CP_NEWSLETTERS_RENDER_MANAGE_FORM, array($this, 'newsletterListForm'));
            return true;
        }
         
        return false;
    }
    
    /**
     * Content manager page form
     * @param Array $fields
     * @param PearNotification $notification
     * @return Array
     */
    function contentManagerPageForm($fields, $notification)
    {
    		$pageData			=	$notification->notificationArgs['page'];
    		if ( $pageData['page_type'] == 'html' OR $pageData['page_type'] == 'php' )
    		{
    			$fields['page_manage_tab_content']['fields'][1] .= $this->__includeEditor('page_content', $pageData['page_content'], $pageData['page_type']);
    		}
    		
    		return $fields;
    }
    
    /**
     * Blocks manager form
     * @param Array $fields
     * @param PearNotification $notification
     * @return Array
     */
    function blocksManagerForm($fields, $notification)
    {
	    	$blockData			=	$notification->notificationArgs['block'];
	    	if ( $blockData['block_type'] == 'html' OR $blockData['block_type'] == 'php' )
	    	{
	    		$fields[1] = $this->__includeEditor('block_content', $blockData['block_content'], $blockData['block_type']);
	    	}
	    	
	    	return $fields;
    }
    
    /**
     * Content layouts manage form
     * @param Array $fields
     * @param PearNotification $notification
     * @return Array
     */
    function contentLayoutsManageForm($fields, $notification)
    {
	    	$layoutData			=	$notification->notificationArgs['layout'];
		$fields[1] 			=	$this->__includeEditor('layout_content', $layoutData['layout_content'], 'php');
	    
	    	return $fields;
    }
    
    /**
     * Content layouts manage form
     * @param Array $fields
     * @param PearNotification $notification
     * @return Array
     */
    function newsletterListForm($fields, $notification)
    {
	    	$newsletterListData							=	$notification->notificationArgs['newsletter_list'];
    	    	$fields['newsletter_mail_template_field'] 	=	$this->__includeEditor('newsletter_mail_template', $newsletterListData['newsletter_mail_template'], 'html');
	    	 
	    	return $fields;
    }
    
    /**
     * Include the syntax editor
     * @param String $editorId
     * @param String $editorContent
     * @param String $editorLanguage
     * @return String
     */
    function __includeEditor($editorId, $editorContent, $editorLanguage)
    {
    		$editorContent = htmlspecialchars($editorContent);
		$editorContent = str_replace('&amp;#39;', '&quot;', $editorContent);
    		
    		
	    	$this->response->addJSFile( $this->absoluteUrl( '/ThirdParty/ace/ace-noconflict.js', 'addon_js') );
	    	$this->response->addJSFile( $this->absoluteUrl( '/ThirdParty/ace/mode-' . $editorLanguage . '-noconflict.js', 'addon_js') );
		
	    	$this->response->addJSFile( $this->absoluteUrl( '/PearACEEditorManager.js', 'addon_js') );
	    	
    		return  <<<EOF
 <style type="text/css" media="screen">
    #{$editorId} {
    		margin: 0px auto;
    		width: 100%;
    		height: 300px;
    		left: 0px;
    		right: 0px;
    		top: 0px;
    		bottom: 0px;
    		position: relative;
    		text-align: left !important;
    		direction: left !important;
    }
</style>
<script type="text/javascript">
//<![CDATA[
	PearACEEditorManager.register("{$editorId}", "{$editorLanguage}");
//]]>
</script>
EOF;
    }
}
