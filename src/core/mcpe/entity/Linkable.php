<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

interface Linkable {
	public function getLink() : ?Linkable;

	public function setLink(?Linkable $entity) : Linkable;

	public function unlink() : bool;
}