<?php
/**
 * @author 		Nox Nebula
 * @copyright	© 2011 Silex Bulletin Board - Team
 * @license		GNU GENERAL PUBLIC LICENSE - Version 3
 * @package		SilexBoard
 */

class Language implements LanguageInterface {
	/* Saves the Current Language */
	private static $Language;
	/* Saves all languageitems */
	private static $Items = array();
	/* Saves all available languages */
	private static $Languages = array();
	
	public static function Get($Key) {
		self::GetLanguage();
		return isset(self::$Items[$Key]) ? self::$Items[$Key] : $Key;
	}
	
	public static function Assign() {
		if(self::GetLanguage())
			return self::$Items;
		return false;
	}
	
	public static function LanguageList() {
		foreach(scandir(DIR_LANGUAGE) as $Lang) {
			if(is_dir(DIR_LANGUAGE.$Lang) && !($Lang == '.' || $Lang == '..')) {
				if(file_exists(DIR_LANGUAGE.$Lang.'/info.xml')) {
					$Name = XML::ReadElement(DIR_LANGUAGE.$Lang.'/info.xml', 'name');
					$List[$Lang] = ''.$Name[0];
					
					if(!SBB::SQL()->Table('language')->Exists()->Where('`Shortcut` = "'.$Lang.'"')->Execute()) {
						$Encoding = XML::ReadElement(DIR_LANGUAGE.$Lang.'/info.xml', 'encoding');
						SBB::SQL()->Insert(array('Shortcut' => $Lang, 'Encoding' => $Encoding[0]))->Execute();
					}
				}
			}
		}
		return $List;
	}
	
	public static function Change($Language) {
		// Code to change the Language
	}
	
	/**
	 * Search and include and returns (as string) the used language
	 */
	private static function GetLanguage() {
		if(!empty(self::$Language))
			return self::$Language;
		
		// SESCLEAR
		/* Find out, wich language should used */
		/*if(isset($_SESSION['UserID']))
			self::$Language = SBB::SQL()->Table('users')->Select('Language')->Where('`ID` = "'.Session::Read('UserID').'"')->Limit(1)->Execute()->FetchObject()->Language;
		else if(isset($_COOKIE['SBB_Lang']))
			self::$Language = $_COOKIE['SBB_Lang'];
		*/
		if(empty(self::$Language))
			self::$Language = SBB::SQL()->Table('language')->Select('Shortcut')->Where('`DefaultLanguage` = 1')->Limit(1)->Execute()->FetchObject()->Shortcut;
		
		/* Include the Languagefiles */
		if(!empty(self::$Language)) {
			$Dir = DIR_LANGUAGE.self::$Language.'/';
			if(is_dir($Dir)) {
				foreach(scandir($Dir) as $File) {
					if(is_file($Dir.$File) && strpos($Dir.$File, '.php') !== false) {
						require_once($Dir.$File);
					}
				}
			} else
				return false;
		}
		
		return empty(self::$Language) ? false : self::$Language;
	}
}
?>