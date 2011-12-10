<?php
/**
 * @author 		Cadillaxx
 * @copyright	© 2011 Silex Bulletin Board - Team
 * @license		GNU GENERAL PUBLIC LICENSE v3
 * @package		SilexBoard
 */

/* Diese Klasse verwaltet und gibt informationen über die Benutzer des Boards */
class User {		
	public static function Create($Username, $Password, $Email) {
		$Salt = self::EncryptSalt();
		$Password = self::EncryptPassword($Password, $Salt);
		$Key = substr(md5(base64_encode($Password.$Salt)), 0, 15);
		
		$Inserts = array('UserName' => $Username,
				'Password' => $Password,
				'Salt' => $Salt,
				'Email' => $Email,
				'ActivationKey' => $Key,
				'RegisterTime' => time());
		SBB::SQL()->Insert('users', $Inserts);
	}
	
	public static function CheckUpdate(array $Post) {
		$Error = array();
		if(!preg_match('/^https?\:\/\/[\w\.-]+\.[\w]{2,3}$/', $Post['Homepage'])) {
			$Error[] = Language::Get('com.sbb.profile.invalid_homepage');
		}
		if(strlen($Post['Signature'] > 500)) {
			$Error[] = Language::Get('com.sbb.profile.signaturelength');
		}
		
		if(count($Error) != 0) {
			return $Error;
		}
		return true;
	}
	
	public static function Update($Property, $Value = NULL, $ID = 0) {
		if(!is_int($ID)) {
			return false;
		}
		if($ID == 0) {
			$ID = self::GetUserID();
		}
		if(is_array($Property)) {
			SBB::SQL()->Update('users', $Property, 'ID = '.$ID);
		}
		else if(!is_string($Property)) {
			SBB::SQL()->Update('users', array($Property => $Value), 'ID = '.$ID);
		}
		else {
			return false;
		}
	}
	
	public static function GetUserID($Name = '') {
		if(!is_string($Name)) {
			return false;
		}
		if(!empty($Name)) {
			SBB::SQL()->Select('users', 'ID', 'Username=\''.$Name.'\'');
			return SBB::SQL()->GetObjects()->ID;
		}
		return Session::Read('UserID');
	}
	
	public static function Get($Data = '*', $ID = 0) {
		if(!is_int($ID)) {
			return false;
		}
		if($ID == 0) {
			$ID = Session::Read('UserID');
		}
		if($ID != 0) {
			if($Data = '*') {
				SBB::SQL()->Select('users', '*', 'ID = '.$ID);		
				$Data = SBB::SQL()->FetchObject();	
				return $Data;	
			}
			SBB::SQL()->Select('users', $Data, 'ID = '.$ID);
			$Data = SBB::SQL()->FetchObject()->$Data;	
			return $Data;
		}
		return false;
	}
	
	public static function LoggedIn() {
		return Session::Read('UserID') > 0;
	}
	
	public static function Login($UserID, $StayLoggedIn = false) {
		$Token = sha1(md5(mt_rand()).microtime().mt_rand());
		$Inserts = array(
			'ID' => session_id(),
			'UserID' => $UserID,
			'IPAddress' => $_SERVER['REMOTE_ADDR'],
			'UserAgent' => $_SERVER['HTTP_USER_AGENT'],
			'LastActivityTime' => time(),
			'Token' => $Token,
			'LoginHash' => 'NULL'
		);
		SBB::SQL()->Insert('session', $Inserts);
		if($StayLoggedIn) {
			$LoginHash = sha1(mt_rand().microtime().md5(mt_rand()));
			setcookie('sbb_LoginHash', $LoginHash, time()+60*60*24*365*10);
			SBB::SQL()->Update('session', array('LoginHash' => $LoginHash), 'UserID = \''.$UserID.'\'');
		}
		SBB::SQL()->Select('users', 'Username', 'ID = \''.$UserID.'\'', '', 1);
		$row = SBB::SQL()->FetchArray();
		Session::Set('UserID', $UserID);
		Session::Set('Username', $row['Username']);
	}
	
	public static function Logout() {
		if(self::LoggedIn()) {
			if(isset($_COOKIE['sbb_LoginHash'])) {
				setcookie('sbb_LoginHash', '', time()-60*60);
			}
			SBB::SQL()->Delete('session', 'UserID = \''.Session::Read('UserID').'\'');
			$_SESSION = array();
			session_destroy();
		}
		else {
			new MessageBox(Language::Get('com.sbb.logout.not_logged_in'), MessageBox::ERROR);
		}
	}
	
	// Password Stuff
	public static function EncryptPassword($Password, $Salt) {
		return sha1($Salt.md5($Salt.sha1($Password.md5($Salt))));
	}
	
	private static function EncryptSalt() {
		return sha1(md5(base64_encode(microtime())));
	}
}
?>