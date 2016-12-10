<?php

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
 * @package		PearCMS Admin CP Controllers
 * @author		$Author:$
 * @version		$Id:$
 * @link			$Link:$
 * @since		$Since:$
 */

if(! defined( "PEARCMS_SYSTEM" ) )
{
	print <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" ><head>    <title>Pear Content Management System - Unreachable page</title><style type="text/css">    body    {    	color:#000000;    	font-size:13pt;    	font-family:Arial, Times New Roman;    }    a    {    	color:#000000;    	font-weight:bold;    	text-decoration:none;    	font-style:italic;    }        a:hover    {    	text-decoration:underline;    }    #title    {    	font-size:22pt;    	font-style:italic;    	font-weight:300;    	padding:5px;    	padding-bottom:0px;    	margin-left:15px;    	margin-bottm:1px;    	font-family:Times New Roman;    }        #hr_break    {    	border:1px solid #e3e3e3;    	color:#e3e3e3;    	padding:2px;    	margin:2px;    	margin-bottom:10px;    	text-align:left;    	width:85%;    }</style></head><body>    <div id="title">Pear Content Management System</div>    <hr id="hr_break" />	Could not open the selected page, it may couse becuse you tried to accross the page directly.<br />	or you tried to go into a forbidden page.<br />	Thanks, <a href="http://pearservices.com/index.php">Quartz Technologies Ltd.</a></body></html>
EOF;
	exit();
}

/**
 * Custom controller used to authenticate user as administrator
 * @see			PearCPViewController_Authentication
 * @copyright	$C;Copyrights:$
 * @license		$C;License:$
 * @version		$C;Id:$
 * @link			$C;Link:$
 * @since		$C;Since:$
 * @access		Private
 */
class PearAddonCPViewController_AdminCPWWWAuthentication_Authentication extends PearAddonCPViewController
{
	/**
	 * The orginal authentication controller
	 * @var PearCPViewController_Authentication
	 */
	var $authenticationController			=	null;
	
	function execute()
	{
		//-----------------------------------------
		//	What shall we do?
		//-----------------------------------------
		switch ( $this->request['do'] )
		{
			case 'do-logout':			//	This is the logout action as specified in {@link PearCPViewController_Authentication}
				$this->doLogout();
				break;
			default:
				$this->authenticationForm();
				break;
		}
	}
	
	function authenticationForm()
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		$memberEmail									=	$this->pearRegistry->parseAndCleanValue($this->pearRegistry->getEnv('PHP_AUTH_USER'));
		$memberPassword								=	$this->pearRegistry->parseAndCleanValue($this->pearRegistry->getEnv('PHP_AUTH_PW'));
		$this->request['member_email']				=	$memberEmail;
		$this->request['member_password']			=	$memberPassword;
		
		if (! $this->__authenticate($memberEmail, $memberPassword) )
		{
			/** Load lang_error for "no_permissions" language bit **/
			$this->localization->loadLanguageFile('lang_error');
			
			/** Send "WWW-Authenticate" header **/
			header('WWW-Authenticate: Basic realm="PearCMS Admin Control Panel"');
			
			/** Send 401 status header **/
			$this->response->setHeaderStatus(401);
			
			/** Force nocache headers **/
			$this->response->sentHeaders = false;
			$this->response->sendNocacheHeaders = true;
			
			/** Render the authentication failed screen **/
			$this->response->printRawContent($this->render(array(), 'authenticationFailedScreen', true));
		}
	}
	
	function doLogout()
	{
		//--------------------------------------
		//	If this is not a member, we can't perform this action
		//--------------------------------------
		
		if ( $this->member['member_id'] < 1 )
		{
			$this->pearRegistry->response->silentTransfer($this->pearRegistry->admin->rootUrl, 401);
		}
		
		//--------------------------------------
		//	Kill session in database
		//--------------------------------------
		
		$this->db->remove('admin_login_sessions', 'session_id = "' . $this->request['authsession'] . '"');
		
		//--------------------------------------
		//	Remove cookies
		//--------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_CPAuthToken', "", false, -1);
		$this->pearRegistry->setCookie('PearCMS_CPAuthTokenSalt', "", false, -1);
		
		//--------------------------------------
		//	And redirect
		//--------------------------------------
		
		/*
		 * Since basic authentication does not supports logout action we'll simulate it by redirecting to
		 * the site name with auth name but without auth port.
		 * Basic URL pattern: <protocol>://<user>:<pass>@<host>:<port>/<path>/<script>.
		 * so for example, we'll redirecto to https://yahav.g.b%40pearcms.com:@localhost/dev/Admin/
		 * 
		 * @see http://php.net/manual/en/features.http-auth.php
		 */
		$this->response->redirectionScreen('logout_success', preg_replace('@^(http|https)://@i', '$1://' . urlencode($this->member['member_email']) . ':@', $this->pearRegistry->admin->rootUrl));
	}
	
	/**
	 * Authenticate member by email and password
	 * @param String $memberEmail
	 * @param String $memberPass
	 * @return Boolean
	 */
	function __authenticate($memberEmail, $memberPass)
	{
		//-----------------------------------------
		//	Init
		//-----------------------------------------
		
		$memberEmail				=	str_replace( '|', '&#124;', $memberEmail);
		$queryString				=	trim(urldecode($this->pearRegistry->queryStringSafe));
		
		//-----------------------------------------
		//	Empty fields?
		//-----------------------------------------
		
		if ( empty($memberEmail) )
		{
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_email_empty' ));
			return false;
		}
		else if ( ! $this->pearRegistry->verifyEmailAddress($memberEmail) )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_email_invalid' ));
			return false;
		}
		
		if ( empty($memberPass) )
		{
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_pass_empty' ));
			return false;
		}
		
		//-----------------------------------------
		//	Try to grab the account
		//-----------------------------------------
		
		$memberPass = md5( md5( md5( $memberPass ) ) );
		$this->db->query('SELECT m.*, g.group_access_cp FROM pear_members m, pear_groups g WHERE m.member_email = "' . $memberEmail . '" AND m.member_password = "' . $memberPass . '" AND m.member_group_id = g.group_id');
		
		if ( ($memberData = $this->db->fetchRow() ) === FALSE )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_details_not_match' ));
			return false;
		}
		
		//-----------------------------------------
		//	Can we access this page?
		//-----------------------------------------
		
		if ( intval($memberData['group_access_cp']) != 1 )
		{
			$this->pearRegistry->admin->addLoginAttempt( FALSE );
			$this->postNotification(PEAR_EVENT_CP_FAILED_LOGIN, $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'reason' => 'member_lack_cp_permissions' ));
			return false;
		}
		
		//-----------------------------------------
		//	Fix up query string
		//-----------------------------------------
		
		if ( is_string($queryString) AND ! empty($queryString) )
		{
			$queryString				=	preg_replace('@' . preg_quote($this->pearRegistry->admin->rootUrl, '@') . 'index.php?@i', '', $queryString);
			$queryString				=	preg_replace('@admin.php@i', '', $queryString);
			$queryString				=	preg_replace('@authsession=([a-z0-9]){32}@i', '', $queryString);
			$queryString				=	preg_replace('@load=authentication((&|&amp;)do=do-auth)?@i', '', $queryString);
		}
		
		//-----------------------------------------
		//	Remove old sessions
		//-----------------------------------------
		
		$this->db->query("DELETE FROM pear_admin_login_sessions WHERE member_id = " . $memberData['member_id']);
		
		//-----------------------------------------
		//	Initialize new session
		//-----------------------------------------
		
		$insertTime				=	time();
		$sessionID				=	md5( uniqid('PearCP_' . microtime()) );
		$memberLoginKey			=	md5( $sessionID . ':' . $this->request['IP_ADDRESS'] . ':' . md5($this->pearRegistry->config['database_password'] . ';' . $this->pearRegistry->config['database_user_name'] ) );
		$memberLoginKeySalt		=	$this->pearRegistry->generateRandomString( rand(5, 10) );
		$hashedLoginKey			=	md5( $memberLoginKey . ':' . $insertTime . ':' . $memberLoginKeySalt );
		
		//-----------------------------------------
		//	Set data in DB
		//-----------------------------------------
		
		$this->db->insert('admin_login_sessions', array(
				'session_id'				=>	$sessionID,
				'member_ip_address'		=>	$this->request['IP_ADDRESS'],
				'member_id'				=>	$memberData['member_id'],
				'member_login_key'		=>	$hashedLoginKey,
				'member_at_zone'			=>	PEAR_CP_DEFAULT_ACTION,
				'session_login_time'		=>	$insertTime,		//	Must use the exact same time as the hashed login key
				'session_running_time'	=>	time()
		));
		
		//-----------------------------------------
		//	Set data
		//-----------------------------------------
		
		$this->pearRegistry->setCookie('PearCMS_CPAuthToken', $memberLoginKey, false);
		$this->pearRegistry->setCookie('PearCMS_CPAuthTokenSalt', $memberLoginKeySalt, false);
		$this->request['authsession'] = $sessionID;
		
		//-----------------------------------------
		//	Redirect
		//-----------------------------------------
		
		$this->pearRegistry->admin->addLoginAttempt( TRUE );
		$this->postNotification(PEAR_EVENT_CP_SUCCESS_LOGIN , $this, array( 'member_email' => $memberEmail, 'member_password' => $memberPass, 'member_data' => $memberData, 'session_id' => $sessionID, 'member_login_key' => $memberLoginKey, 'member_login_salt' => $memberLoginKeySalt, 'query_string' => $queryString ));
		
		$this->response->redirectionScreen('auth_success_redirect', $this->absoluteUrl( 'authsession=' . $this->request['authsession'] . '&amp;' . $queryString, 'cp_root'));
		
		/** We can return true, but it doesn't matther since the redirectionScreen() method halt the program. **/
		return true;
	}
}