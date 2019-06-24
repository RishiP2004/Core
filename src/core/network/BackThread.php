<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use pocketmine\{
	Server,
	Thread
};

class BackThread extends Thread {
	public function run() {
		$timeStamp = date("Y-m-d-H-i") . "/";
		$pass = Server::getInstance()->getDataPath();

		$this->dirCopy($pass . "players/", Core::getInstance()->getDataFolder() . $timeStamp . "/players");
		$this->dirCopy($pass . "plugins/", Core::getInstance()->getDataFolder() . $timeStamp . "/plugins");
		$this->dirCopy($pass . "worlds/", Core::getInstance()->getDataFolder() . $timeStamp . "/worlds");
		copy($pass . "ops.txt", Core::getInstance()->getDataFolder() . $timeStamp . "/ops.txt");
		copy($pass . "white-list.txt", Core::getInstance()->getDataFolder() . $timeStamp . "/white-list.txt");
	}

	public function dirCopy($dirName, $newDir) {
		if(!is_dir($newDir)) {
			mkdir($newDir, 0744, true);
		}
		if(is_dir($dirName)) {
			if($dh = opendir($dirName)) {
				while($file = readdir($dh) !== false) {
					$findPass = strpos($dirName, "ServerBackUp");
					$findPass2 = strpos($dirName, "ServerBackUp2");

					if($findPass === false and $findPass2 === false) {
						if($file === "." or $file === "..") {
							continue;
						}
						if(is_dir($dirName . "/" . $file)) {
							$this->dirCopy($dirName . "/" . $file, $newDir . "/" . $file);
						} else {
							copy($dirName . "/" . $file, $newDir . "/" . $file);
						}
					}
				}
				closedir($dh);
			}
		}
	}
}