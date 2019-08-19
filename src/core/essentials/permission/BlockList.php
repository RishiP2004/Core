<?php

declare(strict_types = 1);

namespace core\essentials\permission;

use core\Core;
use core\CoreUser;

use core\essentials\Essentials;

use pocketmine\permission\BanEntry;

class BlockList extends \pocketmine\permission\BanList {
    private $type = "";

    public $list = [];

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function load() {
		Core::getInstance()->getDatabase()->executeSelect("sentences.get", [], function(array $rows) {
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
    public function getSentences() : array {
        return $this->list;
    }

    public function isBanned(string $name) : bool {
        $this->removeExpired();

        return isset($this->list[$name]);
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new BlockEntry($target);

        $entry->setReason($reason !== null ? $reason : $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source !== null ? $source : $entry->getSource());

        $this->list[$entry->getName()] = $entry;

        Core::getInstance()->getStats()->getCoreUser($target, function($user) use($source, $reason, $expires) {
			Core::getInstance()->getDatabase()->executeInsert("sentences.register", [
				"xuid" => $user->getXuid(),
				"registerDate" => date("m:d:y h:A"),
				"listType" => $this->type,
				"type" => Essentials::BLOCK,
				"username" => $user->getName(),
				"sentencer" => $source,
				"reason" => $reason,
				"expires" => $expires
			]);
		});
        return $entry;
    }

    public function remove(string $name) {
		Core::getInstance()->getStats()->getCoreUser($name, function($user) use ($name) {
			Core::getInstance()->getDatabase()->executeChange("sentences.delete", [
				"xuid" => $user->getXuid(),
				"listType" => $this->type,
				"type" => Essentials::BLOCK
			]);

			$name = strtolower($name);

			if(isset($this->list[$name])) {
				unset($this->list[$name]);
			}
		});
    }

    public function removeExpired() {
        foreach($this->list as $name => $entry) {
            if($entry->hasExpired()) {
                $this->remove($name);
            }
        }
    }
}