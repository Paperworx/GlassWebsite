<?php
require_once(realpath(dirname(__FILE__) . '/DatabaseManager.php'));

class BuildManager {
	private static $objectCacheTime = 180; //3 minutes, enough time for someone to preview the build and then download it
	private static $userBuildsCacheTime = 60;

	public static function getFromID($id, $resource = false) {
		$buildObject = apc_fetch('buildObject_' . $id, $success);

		if($success === false) {
			if($resource !== false) {
				$buildObject = new BuildObject($resource);
			} else {
				$database = new DatabaseManager();
				BuildManager::verifyTable($database);
				$resource = $database->query("SELECT * FROM `build_builds` WHERE `id` = '" . $database->sanitize($id) . "' LIMIT 1");

				if(!$resource) {
					throw new Exception("Database error: " . $database->error());
				}

				if($resource->num_rows == 0) {
					$buildObject = false;
				} else {
					$buildObject = new BuildObject($resource->fetch_object());
				}
				$resource->close();
			}
			apc_store('buildObject_' . $id, $buildObject, BuildManager::$objectCacheTime);
		}
		return $buildObject;
	}

	public static function getBuildsFromBLID($id) {
		$userBuilds = apc_fetch('userBuilds_' . $id, $success);

		if($success === false) {
			$database = new DatabaseManager();
			BuildManager::verifyTable($database);
			$resource = $database->query("SELECT * FROM `build_builds` WHERE `blid` = '" . $database->sanitize($id) . "'");

			if(!$resource) {
				throw new Exception("Database error: " . $database->error());
			}
			$userBuilds = [];

			while($row = $resource->fetch_object()) {
				$userBuilds[] = BuildManager::getFromID($row->id, $row);
			}
			$resource->close();
			apc_store('userBuilds_' . $id, $userBuilds, BuildManager::$userBuildsCacheTime);
		}
		return $userBuilds;
	}

	public static function verifyTable($database) {		*/
		if($database->debug()) {
			require_once(realpath(dirname(__FILE__) . '/UserManager.php'));
			require_once(realpath(dirname(__FILE__) . '/AddonManager.php'));
			UserManager::verifyTable($database); //we need users table to exist before we can create this one
			AddonManager::verifyTable($database);

			if(!$database->query("CREATE TABLE IF NOT EXISTS `build_builds` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`blid` INT NOT NULL,
				`name` VARCHAR(16) NOT NULL,
				`filename` TEXT NOT NULL,
				`bricks` INT NOT NULL DEFAULT 0,
				`description` TEXT,
				FOREIGN KEY (`blid`)
					REFERENCES users(`blid`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				PRIMARY KEY (`id`))")) {
				throw new Exception("Error creating builds table: " . $database->error());
			}

			//to do: probably should move this to another class, maybe make dependencyManager more general
			if(!$database->query("CREATE TABLE IF NOT EXISTS `build_dependency` (
				`od` INT NOT NULL AUTO_INCREMENT,
				`bid` INT NOT NULL,
				`aid` INT NOT NULL,
				FOREIGN KEY (`bid`)
					REFERENCES build_builds(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				FOREIGN KEY (`aid`)
					REFERENCES addon_addons(`id`)
					ON UPDATE CASCADE
					ON DELETE CASCADE,
				PRIMARY KEY (`id`))")) {
				throw new Exception("unable to create build dependency table");
			}
		}
	}
}
?>