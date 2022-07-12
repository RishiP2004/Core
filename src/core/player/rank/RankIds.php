<?php

declare(strict_types = 1);

namespace core\player\rank;

interface RankIds {
    const DONATOR_RANK = 2;
    const STAFF_RANK = 3;

    public const PLAYER = 0;

    // DONATOR
    public const HEXCITE = 1;
	public const OG = 2;

	public const EONIVE = 3;
	public const UNIVERSAL = 4;
	public const PIXELATED = 5;
	public const ATHENER = 6;

	// STAFF
	public const STAFF = 1;
	public const ADMINISTRATOR = 2;
	public const MANAGER = 3;
	public const OWNER = 4;
	public const YOUTUBE = 5;
}