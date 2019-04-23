<?php

declare(strict_types = 1);

namespace core\mcpe\level\generator\ender;

use core\mcpe\Addon;
use core\mcpe\level\generator\ender\populator\EnderPilar;

use pocketmine\utils\Random;

use pocketmine\math\Vector3;

use pocketmine\block\Block;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\biome\Biome;

class Ender extends Generator implements Addon {
	private static $GAUSSIAN_KERNEL = null;

	private static $SMOOTH_SIZE = 1;
	/** @var ChunkManager */
	protected $level;
	/** @var Random */
	protected $random;
	/** @var Populator[] */
	private $populators = [];

	private $waterHeight = 0, $emptyHeight = 24, $emptyAmplitude = 2, $density = 0;
	/** @var Populator[] */
	private $generationPopulators = [];
	/** @var Simplex */
	private $noiseBase;

	public function __construct(array $options = []) {
		if(self::$GAUSSIAN_KERNEL === null) {
			self::generateKernel();
		}
	}

	public function init(ChunkManager $level, Random $random) : void {
		$this->level = $level;
		$this->random = $random;

		$this->random->setSeed($this->level->getSeed());

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);

		$this->random->setSeed($this->level->getSeed());

		$pilar = new EnderPilar();

		$pilar->setBaseAmount(0);
		$pilar->setRandomAmount(0);

		$this->populators[] = $pilar;
	}

	public function getName() : string {
		return "Ender";
	}

	public function getWaterHeight() : int {
		return $this->waterHeight;
	}

	public function getSettings() : array {
		return [];
	}

	public function getSpawn() : Vector3 {
		return new Vector3(127.5, 128, 127.5);
	}

	private static function generateKernel() {
		self::$GAUSSIAN_KERNEL = [];

		$bellSize = 1 / self::$SMOOTH_SIZE;
		$bellHeight = 2 * self::$SMOOTH_SIZE;

		for($sx = -self::$SMOOTH_SIZE; $sx <= self::$SMOOTH_SIZE; ++$sx) {
			self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE] = [];

			for($sz = -self::$SMOOTH_SIZE; $sz <= self::$SMOOTH_SIZE; ++$sz) {
				$bx = $bellSize * $sx;
				$bz = $bellSize * $sz;

				self::$GAUSSIAN_KERNEL[$sx + self::$SMOOTH_SIZE][$sz + self::$SMOOTH_SIZE] = $bellHeight * exp(-($bx * $bx + $bz * $bz) / 2);
			}
		}
	}

	public function generateChunk(int $chunkX, int $chunkZ) : void {
		$this->random->setSeed(0xa6fe78dc ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		$noise = $this->noiseBase->getFastNoise3D(16, 128, 16, 4, 8, 4, $chunkX * 16, 0, $chunkZ * 16);
		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x) {
			for($z = 0; $z < 16; ++$z) {
				$biome = Biome::getBiome(self::END);

				$biome->setGroundCover([
					Block::get(Block::OBSIDIAN, 0),
				]);
				$chunk->setBiomeId($x, $z, $biome->getId());

				for($y = 0; $y < 128; ++$y) {
					$noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
					$noiseValue -= 1 - $this->density;
					$distance = new Vector3(0, 64, 0);
					$distance = $distance->distance(new Vector3($chunkX * 16 + $x, ($y / 1.3), $chunkZ * 16 + $z));

					if($noiseValue < 0 && $distance < 100 or $noiseValue < -0.2 && $distance > 400) {
						$chunk->setBlockId($x, $y, $z, Block::END_STONE);
					}
				}
			}
		}
		foreach($this->generationPopulators as $populator) {
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(int $chunkX, int $chunkZ) : void {
		$this->random->setSeed(0xa6fe78dc ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());

		foreach($this->populators as $populator){
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		$biome = Biome::getBiome($chunk->getBiomeId(7, 7));

		$biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
	}
}