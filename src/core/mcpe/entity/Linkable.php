<?php

namespace core\mcpe\entity;

interface Linkable {
	public function getLink() : ?Linkable;

	public function setLink(Linkable $entity);
}