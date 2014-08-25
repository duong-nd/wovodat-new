<?php
	defined('_SECURITY') or die("Access denied.");

	class Loader {

		private static function loadFolder($path) {
			$files = scandir($path);
			foreach($files as $file) {
				if ($file == '.' || $file == '..') continue;
				require_once($path . $file);
			}
		}

		public static function loadRepository() {
			self::loadFolder("Repository/");
		}

		public static function loadUtility() {
			self::loadFolder("Utility/");
		}

		public static function loadController() {
			self::loadFolder("Controller/");
		}

		public static function loadRouting() {
			$path = "Routing/";
			$files = scandir($path);
			
			foreach($files as $file) {
				if ($file == '.' || $file == '..') continue;
				$file_content = file_get_contents($path . $file);
				$object = json_decode($file_content,true);
				Routing::register($object);
			}
		}

		public static function loadDatabase() {
			$host = Configuration::MYSQL_HOST;
			$user = Configuration::MYSQL_USER;
			$pass = Configuration::MYSQL_PASSWORD; 
			$db = Configuration::MYSQL_DATABASE;
			return new Database($host, $user, $pass, $db);
		}

		public static function setJSONHeader() {
			header('Content-Type: application/json');
		}
	}