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
 * @package		PearCMS Admin CP JS
 * @author		$Author:$
 * @version		$Id:$
 * @link			$Link:$
 * @since		$Since:$
 */

/**
 * Utilities object used to provide basic ACE editor interaction
 * 
 * @copyright	$C;Copyrights:$
 * @license		$C;License:$
 * @version		$C;Id:$
 * @link			$C;Link:$
 * @since		$C;Since:$
 * @access		Private
 */

var PearACEEditorManager =
{
	registeredACEEditors:			$A(),
	
	initialize:						function() {
		PearACEEditorManager.registeredACEEditors.each(function(editorPack) {
			//----------------------------------------
			//	First, store the value that indicates if the textarea
			//	is disabled, if so, we'll disable the editor too
			//----------------------------------------
			
			var disabled = $(editorPack.editorId).readAttribute('disabled');
			
			//----------------------------------------
			//	ACE editor wish to get a <pre></pre> tag,
			//	so lets make it happy
			//----------------------------------------
			
			/** Create a <pre> tag with a temp id **/
			var pre = new Element('PRE')
						.writeAttribute('id', editorPack.editorId + '-pre')
						.writeAttribute('disabled', disabled)
						.update($(editorPack.editorId).innerHTML);
			$(editorPack.editorId).insert({before: pre});
			
			/** Remove the textarea from the DOM **/
			$(editorPack.editorId).remove();
			
			/** Rename the pre to the textarea id **/
			$(editorPack.editorId + '-pre').writeAttribute('id', editorPack.editorId);
			
			//----------------------------------------
			//	Now, INIT THE EDITOR!
			//----------------------------------------
			
			var editor = ace.edit(editorPack.editorId);
			
			//----------------------------------------
			//	If the textarea was disabled, make the editor
			//	read only (this is the closest API in ACE)
			//----------------------------------------
			
			if ( disabled )
			{
				editor.setReadOnly(true);
			}
			
			//----------------------------------------
			//	Declare the editing mode (e.g. PHP, JS, CSS)
			//----------------------------------------
			
	   		var mode = ace.require("ace/mode/" + editorPack.language).Mode;
	    		editor.getSession().setMode(new mode());
	    		
	    		if ( Object.isFunction(editorPack.delegate) )
    			{
	    			editorPack.delegate( editor );
    			}
	    		
	    		//----------------------------------------
			//	The editor make don't save the given values,
	    		//	and we even don't have any form element to store them in
	    		//	right now (remember? we've removed the textarea from the DOM)
	    		//	so we'll observe the form submit and do some magic
	    		//----------------------------------------
			
	    		$(editorPack.editorId).up("FORM").observe("submit", function(e) {
	    			//----------------------------------------
	    			//	Format the written value
	    			//----------------------------------------
	    			
	    			var value = editor.getSession()
	    							.getValue()
	    							.replace( /&([#\w\d]+);/, '&amp;$1;')		//	Fix HTML entities
	    							.replace( /^[\s\n]+/, '' )					//	ltrim
	    							.replace( /[\s\n]+$/, '' );					//	rtrim
	    			
	    			//----------------------------------------
	    			//	Create a hidden textarea to store the actual value
	    			//----------------------------------------
	    			
	    			var textarea = new Element("TEXTAREA")
	    						.addClassName('input-textarea')
	    						.setStyle('display: none;')
	    						.writeAttribute('name', editorPack.editorId);
	    			
	    			/** We won't use prototype's update() method since it evaluates script tags instead of appending them like a plain string **/
	    			textarea.innerHTML = value;
	    			
	    			/** Append it secretly */
	    			$(editorPack.editorId).up('FORM').insert(textarea);
	    		});
		});
	},
	
	register:				function(editorId, editorProgrammingLanguage, callback) {
		PearACEEditorManager.registeredACEEditors.push( { editorId: editorId, language: editorProgrammingLanguage, delegate: callback } );
	}
};

/** Register the class inititalize method as observer. Note that it won't do anything unless editor(s) will be registered **/
Event.observe(window, 'load', PearACEEditorManager.initialize);