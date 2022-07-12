<?php

declare(strict_types = 1);

namespace core\essential\permission;

use core\Core;
use core\database\Database;
use core\player\CoreUser;

use core\essential\EssentialManager;

use core\player\traits\PlayerCallTrait;

use pocketmine\permission\BanEntry;

class MuteList extends \pocketmine\permission\BanList {
	use PlayerCallTrait;

    private $type = "";

    public $list = [];

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function load() : void {
        Database::get()->executeSelect("sentences.get", [], function(array $rows) {
			foreach($rows as [
					"xuid" => $xuid,
					"username" => $name,
					"reason" => $reason,
					"expires" => $expires,
					"sentencer" => $source
			]) {
				$entry = new \core\essentials\permission\BanEntry($name);

				$entry->setReason($reason !== null ? $reason : $entry->getReason());
				$entry->setExpires($expires);
				$entry->setSource($source !== null ? $source : $entry->getSource());

				$this->list[$entry->getName()] = $entry;

				$this->removeExpired();
			}
        });
    }
	/**
	 * @return CoreUser[]
	 */
	public function getEntries() : array {
		return $this->list;
	}

	public function isBanned(string $name) : bool {
        $this->removeExpired();

        return isset($this->list[strtolower($name)]);
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new MuteEntry($target);

        $entry->setReason($reason !== null ? $reason : $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source !== null ? $source : $entry->getSource());

        $this->list[$entry->getName()] = $entry;

        $this->getCoreUser($target, function($user) use($source, $reason, $expires) {
			Core::getInstance()->getDatabase()->executeInsert("sentences.register", [
				"xuid" => $user->getXuid(),
				"registerDate" => date("m:d:y h:A"),
				"listType" => $this->type,
				"type" => EssentialManager::MUTE,
				"username" => $user->getName(),
				"sentencer" => $source,
				"reason" => $reason,
				"expires" => $expires->format('m-d-Y H:i') ?? null
			]);
		});
        return $entry;
    }

    public function remove(string $name) : void {
    	$this->getCoreUser($name, function($user) use ($name) {
			Core::getInstance()->getDatabase()->executeChange("sentences.delete", [
				"xuid" => $user->getXuid(),
				"listType" => $this->type,
				"type" => EssentialManager::MUTE
			]);

			$name = strtolower($name);

			if(isset($this->list[$name])) {
				unset($this->list[$name]);
			}
		});
    }

    public function removeExpired() : void {
        foreach($this->list as $name => $entry) {
            if($entry->hasExpired()) {
                $this->remove($name);
            }
        }
    }
}