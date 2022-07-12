<?php

declare(strict_types = 1);

namespace scoreboard;

interface ScoreboardDisplaySlot {
	public const LIST = "list";
	public const SIDEBAR = "sidebar";
	public const BELOWNAME = "belowname";
}