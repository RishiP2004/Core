<?php

namespace core\essentials\permission;

use pocketmine\permission\BanEntry;

class BlockList extends BanList {
    /**
     * @var BlockEntry[]
     */
    public $list = [];

    public function __construct(string $file) {
        parent::__construct($file);
    }

    public function load() {

    }

    public function isBanned(string $name) : bool {
        $this->removeExpired();

        return isset($this->list[$name]);
    }

    public function add(BanEntry $entry) {
        if($entry instanceof BlockEntry) {
            throw new \InvalidArgumentException();
        }
        $this->list[$entry->getName()] = $entry;
        $this->save();
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new BlockEntry($target);

        $entry->setReason($reason ?? $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source ?? $entry->getSource());

        $this->list[$entry->getName()] = $entry;

        $this->save();
        return $entry;
    }

    public function remove(string $name) {
        $name = strtolower($name);

        if(isset($this->list[$name])) {
            unset($this->list[$name]);
            $this->save();
        }
    }

    public function removeExpired() {
        foreach($this->list as $name => $entry){
            if($entry->hasExpired()){
                unset($this->list[$name]);
            }
        }
    }

    public function save(bool $writeHeader = true) {

    }
}