<?php

namespace core\network;

use pocketmine\{
	Server,
	Thread
};

class BackThread extends Thread {
	public function run() {
		$timeStamp = date("Y-m-d-H-i") . "/";
		$pass = Server::getDataPath();

		$this->dirCopy($pass . "players/", $this->getDataFolder() . $timeStamp . "/players");
		$this->dirCopy($pass . "plugins/", $this->getDataFolder() . $timeStamp . "/plugins");
		$this->dirCopy($pass . "worlds/", $this->getDataFolder() . $timeStamp . "/worlds");
		copy($pass . "ops.txt", $this->getDataFolder() . $timeStamp . "/ops.txt");
		copy($pass . "white-list.txt", $this->getDataFolder() . $timeStamp . "/white-list.txt");
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