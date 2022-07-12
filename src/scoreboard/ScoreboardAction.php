<?php

declare(strict_types = 1);

namespace scoreboard;

interface ScoreboardAction {
	public const CREATE = 0;
	public const MODIFY = 1;
}