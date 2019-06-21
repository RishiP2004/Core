<?php

declare(strict_types = 1);

namespace core\essence;

use core\Core;

use core\essence\floatingText\{
    FloatingText,
    LobbyGreetings,
    Parkour,
	TopVoter,
	TopVoter2
};

use core\essence\npc\{
    NPC,
    Athie,
    Factions,
    Lobby
};

class Essence implements EssenceData {
    private $core;

    private $NPCs = [], $floatingTexts = [];

    public function __construct(Core $core) {
        $this->core = $core;

        $this->initFloatingText(new LobbyGreetings());
        $this->initFloatingText(new Parkour());
		$this->initFloatingText(new TopVoter());
		$this->initFloatingText(new TopVoter2());
        $this->initNPC(new Athie());
        $this->initNPC(new Factions());
        $this->initNPC(new Lobby());
    }

    public function getMaxDistance() : int {
        return self::MAX_DISTANCE;
    }

    public function getDefaultSkin() : string {
        return self::DEFAULT_SKIN;
    }

    public function initFloatingText(FloatingText $floatingText) {
        $this->floatingTexts[$floatingText->getName()] = $floatingText;
    }
    /**
     * @return FloatingText[]
     */
    public function getFloatingTexts() : array {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $floatingText) : ?FloatingText {
        $lowerKeys = array_change_key_case($this->floatingTexts, CASE_LOWER);

        if(isset($lowerKeys[strtolower($floatingText)])) {
            return $lowerKeys[strtolower($floatingText)];
        }
        return null;
    }

    public function initNPC(NPC $NPC) {
        $this->NPCs[$NPC->getName()] = $NPC;
    }
    /**
     * @return NPC[]
     */
    public function getNPCs() : array {
        return $this->NPCs;
    }

    public function getNPC(string $NPC) : ?NPC {
        $lowerKeys = array_change_key_case($this->NPCs, CASE_LOWER);

        if(isset($lowerKeys[strtolower($NPC)])) {
            return $lowerKeys[strtolower($NPC)];
        }
        return null;
    }
}