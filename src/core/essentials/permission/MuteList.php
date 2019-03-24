<?php

namespace core\essentials\permission;

use core\Core;

use core\CoreUser;

use pocketmine\permission\BanEntry;

class MuteList extends \pocketmine\permission\BanList {
    private $type = "";

    public $list = [];

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function load() {
        Core::getInstance()->getDatabase()->executeSelect("sentences.get", [], function(array $rows) {
            $users = [];

            foreach($rows as [
                    "xuid" => $xuid,
            ]) {
                $coreUser = new CoreUser($xuid);
                $users[$xuid] = $coreUser;
            }
            $this->list[] = $users;
        });
    }
    /**
     * @return CoreUser[]
     */
    public function getSentences() : array {
        return $this->list;
    }

    public function getCoreUser(string $name) : ?CoreUser {
        foreach($this->getSentences() as $coreUser) {
            if($coreUser->getName() === $name) {
                return $coreUser;
            }
        }
        return null;
    }

    public function getCoreUserXuid(string $xuid) : ?CoreUser {
        foreach($this->getSentences() as $coreUser) {
            if($coreUser->getXuid() === $xuid) {
                return $coreUser;
            }
        }
        return null;
    }

    public function isBanned(string $name) : bool {
        $this->removeExpired();

        return isset($this->list[$name]);
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new MuteEntry($target);

        $entry->setReason($reason !== null ? $reason : $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source !== null ? $source : $entry->getSource());

        $player = Core::getInstance()->getServer()->getPlayer($target);
        $this->list[$entry->getName()] = $entry;

        Core::getInstance()->getDatabase()->executeInsert("sentences.register", [
            "xuid" => $player->getXuid(),
            "registerDate" => date("m:d:y h:A"),
            "listType" => "mute",
            "type" => $this->type,
            "username" => $player->getName(),
            "sentencer" => $source,
            "reason" => $reason,
            "expires" => $expires
        ]);
        return $entry;
    }

    public function remove(string $name) {
        $user = Core::getInstance()->getStats()->getCoreUser($name);

        Core::getInstance()->getDatabase()->executeChange("sentences.delete", [
            "xuid" => $user->getXuid()
        ]);

        $name = strtolower($name);

        if(isset($this->list[$name])) {
            unset($this->list[$name]);
        }
    }

    public function removeExpired() {
        foreach($this->list as $name => $entry) {
            if($entry->hasExpired()) {
                $this->remove($name);
            }
        }
    }
}