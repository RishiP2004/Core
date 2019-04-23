<?php

declare(strict_types = 1);

namespace core\vote;

use core\Core;
use core\CoreUser;

class Vote implements Data {
    private $core;
    
    public $queue = [], $lists = [];
    
    public function __construct(Core $core) {
        $this->core = $core;

        if(!is_dir($core->getDataFolder() . "/vote")) {
            mkdir($core->getDataFolder() . "/vote");
        }
        $this->lists = [];

        foreach(scandir($core->getDataFolder() . "/vote") as $file) {
            $ext = explode(".", $file);
            $ext = (count($ext) > 1 && isset($ext[count($ext) - 1]) ? strtolower($ext[count($ext) - 1]) : "");

            if($ext === "vrc") {
                $this->lists[] = json_decode(file_get_contents($core->getDataFolder() . "/Data/Vote/$file"), true);
            }
        }
        if(trim($this->getAPIKey()) !== "") {
            file_put_contents($core->getDataFolder() . "/Data/Vote/minecraftpocket-servers.com.vrc", "{\"website\":\"http://minecraftpocket-servers.com/\",\"check\":\"http://minecraftpocket-servers.com/api-vrc/?object=votes&element=claim&key=" . $this->getAPIKey() . "&username={USERNAME}\",\"claim\":\"http://minecraftpocket-servers.com/api-vrc/?action=post&object=votes&element=claim&key=" . $this->getAPIKey() . "&username={USERNAME}\"}");
        }
        $core->getServer()->getCommandMap()->register("vote", new \vote\command\Vote($core));
    }

    public function getAPIKey() : string {
        return self::API_KEY;
    }
    
    public function getItemsPerVote() : int {
		return self::ITEMS_PER_VOTE;
    }

    public function getItems() : array {
        return self::ITEMS;
    }
    
    public function getCommands() : array {
        return self::COMMANDS;
    }
	
	public function getLists() : array {
		return $this->lists;
	}
	
	public function addToQueue(CoreUser $user) {
		$this->queue = array_merge($this->queue, $user->getName());
	}
}