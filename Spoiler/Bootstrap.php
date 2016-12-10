<?php

/**
 * Provide a spoiler ([spoiler][/spoiler] bbcode) tag to the WYSIWYG editor
 * @author Yahav Gindi Bar
 * @copyright Yahav Gindi Bar
 * @since San, 10/16/2011 17:27:34 GMT
 */
class PearAddon_Spoiler extends PearAddon
{
	/**
	 * Addon UUID
	 * @var String
	 */
	var $addonUUID				=	"4ebcee0d-305c-47df-a6d3-018fd4b91d0f";
	
	/**
	 * The addon name
	 * @var String
	 */
	var $addonName				=	"Spoiler";
	
	/**
	 * The addon description
	 * @var String
	 */
	var $addonDescription		=	"Allow to turn [spoiler][/spoiler] tag in the WYSIWYG editor into spoiler coved area.";
	
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
			/** We could listen instead to PEAR_EVENT_PARSE_RTE_CONTENT_FOR_DISPLAY, but this would limit us to WYSIWYG-only content generation. **/
			$this->pearRegistry->notificationsDispatcher->addObserver(PEAR_EVENT_RENDER_CONTENT_PAGE, array($this, 'parseContent'));
			return true;
		}
		
		return false;
	}
	
	/**
	 * Parse the content before display it
	 * @param String $t
	 * @param PearNotification $notification
	 * @return String
	 */
	function parseContent($t, $notification)
	{
		/** Load the editor class **/
		$this->pearRegistry->loadLibrary('PearRTEParser', 'editor');
		
		/** Parse using the BBCode apply method **/
		return $this->pearRegistry->loadedLibraries['editor']->__applyCallbackOnBBcode('spoiler', $t, array($this, '__parseSpoilerTag'));
	}
	
	/**
	 * Parse spoiler tags recursivly
	 * @param String $tag
	 * @param String $tagContent
	 * @param String $tagAttributes
	 * @param String $tagReplacement
	 * @return String
	 */
	function __parseSpoilerTag( $tag, $tagContent, $tagAttributes, $tagReplacement )
	{
		static $i			=	0;
		$i++; /** Each spoiler section get its specific index, so the show/hide javascript won't be cause conflict with other spoiler container. **/
		$tagContent			=	preg_replace('@^<br([^>]*)>@i', '', trim($tagContent));
		$tagContent			=	preg_replace('@<br([^>]*)>$@i', '', $tagContent);
		
		return <<<EOF
<style type="text/css">
.spoiler-content-container
{
	width: 90%;
	background: #ffffff;
	color: #000000;
	border: 1px outset #0a0a0a;
	padding: 5px;
}
</style>
<div class="SpoilerWrapper">
	<input type="button" class="spoiler-tag input-submit" id="PearSpoilerTag_{$i}_Toggle" value="Show Spoiler" />
	<div id="PearSpoilerTag_{$i}_Container" class="spoiler-content-container" style="display: none;">
		{$tagContent}
	</div>
</div>

<script type="text/javascript" language="javascript">
//<![CDATA[
	$('PearSpoilerTag_{$i}_Toggle').observe('click', function() {
		new Effect.toggle('PearSpoilerTag_{$i}_Container', 'slide', {
			duration: 0.7,
			afterFinish: function() {
				$("PearSpoilerTag_{$i}_Toggle").value = ($("PearSpoilerTag_{$i}_Container").getStyle('display') == 'none' ? 'Show Spoiler' : 'Hide Spoiler');
			}
		});
	});
//]]>
</script>
EOF;
	}
}