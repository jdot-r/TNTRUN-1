<?php

namespace game\tntrun;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\level\Explosion;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\block\Block;
use pocketmine\entity\FallingBlock;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\UpdateBlockPacket;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class FallingBlockCleanupTask extends PluginTask {
	
	private $pgin;
	private $fallingblock;
	private $gameEndTime;
	private $tntblock;
	public function __construct(TNTRun $pg, FallingBlock $fallingblock, $block) {
		$this->owner = $pg;
		$this->pgin = $pg;
		$this->fallingblock = $fallingblock;
		$this->tntblock = $block;
	}
	
	public function onRun($currentTick) {
		if ($this->fallingblock != null && count ( $this->pgin->livePlayers ) > 0) {
			try {
				//$players = $this->fallingblock->getLevel ()->getPlayers ();
				//optimized only in-game players
				$players = $this->pgin->livePlayers;
				foreach ( $players as $lp ) {
					$pk = new UpdateBlockPacket ();
					$pk->x = $this->tntblock->x;
					$pk->y = $this->tntblock->y;
					$pk->z = $this->tntblock->z;
					$pk->block = 0;
					$pk->meta = 0;
					$lp->dataPacket ( $pk );
					$lp->getLevel ()->setBlockIdAt ( $this->tntblock->x, $this->tntblock->y, $this->tntblock->z, 0 );

					$pos = new Position($this->tntblock->x, $this->tntblock->y, $this->tntblock->z);
					$block = $lp->getLevel()->getBlock($pos);
					$direct = true;
					$update = true;
					$lp->getLevel()->setBlock($pos, $block,$direct, $update);
				}				
			} catch ( \Exception $e ) {
				$this->pgin->getLogger ()->info ( "[TNTRun] cleanup error:" . $e->getMessage () );
				return;
			}
		}
	}
	
	
	public function cancel() {
		$this->getHandler ()->cancel ();
		$this->log ( "Cancel Tasks" );
	}
	
	
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}