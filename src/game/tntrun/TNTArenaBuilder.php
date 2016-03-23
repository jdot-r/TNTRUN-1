<?php

namespace game\tntrun;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\utils\Cache;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use game\util\PacketMonitor;
use pocketmine\entity\FallingBlock;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class TNTArenaBuilder {
	// board size
	public $boardsize = 22;
	public $boardheight = 36;
	public $boardBlocksTypes = [ ];
	
	// plug-in
	private $pgin;
	public function __construct(TNTRun $pg) {
		if (! is_null ( $pg )) {
			$this->pgin = $pg;
		} else {
			$this->log ( TextFormat::RED . " TNTRun - construct error (missing plugin)" );
		}
		
		$this->initBoardBlockType ();
	}
	private function initBoardBlockType() {
		$this->boardBlocksTypes = array (
				"stone1" => "42",
				"stone2" => "42",
				"stone3" => "42",
				"stone4" => "42",
				"stone5" => "42",
				"stone6" => "42",
				"stone7" => "42",
				"stone8" => "50",
				"GLOWSTONE_BLOCK" => "89",
				"Torch" => "50" 
		);
	}
	
	/**
	 * Build Arena
	 *
	 * @param Player $player        	
	 */
	public function buildArena(Level $level, Position $lobbyloc) {
		// $pos = $player->getPosition ();
		$px = $lobbyloc->x;
		$py = $lobbyloc->y;
		$pz = $lobbyloc->z;
		
		if ($lobbyloc != null) {
			$exit_x = $lobbyloc->x;
			$exit_y = $lobbyloc->y;
			$exit_z = $lobbyloc->z;
		} else {
			$exit_x = $px;
			$exit_y = $py;
			$exit_z = $pz;
		}
		
		$bsize = $this->boardsize;
		$bheight = $this->boardheight;
		
		// built a glass tower wrap around the building
		$this->buildHoloWallByType ( $level, $bsize + 2, ($py + $bsize + 30), $px, $py, $pz, 20 );
		
		// water tank floor
		$this->buildWaterTank ( $level, $bsize, $bsize, $px, ($py + 1), $pz, 8 );
		// bottom floor 1
		$this->buildFloor ( $level, $px, ($py + 16), $pz, $bsize );
		// bottom floor 2
		$this->buildFloor ( $level, $px, ($py + 26), $pz, $bsize );
		// bottom floor 3
		$this->buildFloor ( $level, $px, ($py + 36), $pz, $bsize );
		// $this->buildGlassTop($player, round($px/2), ($py+46), round($pz/2), $bsize);
		// bottom floor 4
		// $this->buildFloor($level, $px, ($py+46), $pz, $bsize);
		// bottom floor 5
		// $this->buildFloor($level, $px, ($py+56), $pz, $bsize);
		$this->addGameButtonsOnTopFloor ( $level );
		
		$pos = new Position ();
		$lobbyloc->x = $px + 2;
		$lobbyloc->y = ($py + $bsize + $bheight);
		$lobbyloc->z = $pz + 2;
		// $player->teleport( $pos, 332, 334);
		
		$arenaInfo = array (
				"entrance_x" => $pos->x,
				"entrance_y" => $pos->y,
				"entrance_z" => $pos->z,
				"exit_x" => $exit_x,
				"exit_y" => $exit_y,
				"exit_z" => $exit_z 
		);
		
		return $arenaInfo;
	}
	
	/**
	 * Build Floor
	 *
	 * @param Player $player        	
	 */
	public function buildFloor(Level $level, $px, $py, $pz, $size) {
		// build walls
		$this->buildWall ( $level, $size + 1, $size + 1 - 5, $px, $py - 1, $pz, 1 );
		// add bottom layer stone
		$this->buildBoardLayer ( $level, $px, $py, $pz, 46, $size );
		// add middle layer
		$this->buildBoardLayer ( $level, $px, $py + 1, $pz, 12, $size );
		// add top layer --98
		$this->buildBoardLayer ( $level, $px, $py + 2, $pz, 44, $size );
	}
	
	/**
	 * Build Arena
	 *
	 * @param Player $player        	
	 */
	public function resetArenaBuilding(Level $level, Position $lobbyloc) {
		// $pos = $player->getPosition ();
		$px = $lobbyloc->x;
		$py = $lobbyloc->y;
		$pz = $lobbyloc->z;
		
		if ($lobbyloc != null) {
			$exit_x = $lobbyloc->x;
			$exit_y = $lobbyloc->y;
			$exit_z = $lobbyloc->z;
		} else {
			$exit_x = $px;
			$exit_y = $py;
			$exit_z = $pz;
		}
		
		$bsize = $this->boardsize;
		
		// water tank floor
		// $this->buildWaterTank($player, $bsize, $bsize, $px, ($py+1), $pz, 8) ;
		// bottom floor 1
		$this->ResetFloor ( $level, $px, ($py + 16), $pz, $bsize );
		// bottom floor 2
		$this->ResetFloor ( $level, $px, ($py + 26), $pz, $bsize );
		// bottom floor 3
		$this->ResetFloor ( $level, $px, ($py + 36), $pz, $bsize );
		// $this->buildGlassTop($player, round($px/2), ($py+46), round($pz/2), $bsize);
		// bottom floor 4
		// $this->ResetFloor($level, $px, ($py+46), $pz, $bsize);
		// bottom floor 5
		// $this->ResetFloor($level, $px, ($py+56), $pz, $bsize);
		
		$this->addGameButtonsOnTopFloor ( $level );
		
		$pos = new Position ();
		$lobbyloc->x = $px + 2;
		$lobbyloc->y = ($py + $bsize + 56);
		$lobbyloc->z = $pz + 2;
		// $player->teleport( $pos, 332, 334);
		
		$arenaInfo = array (
				"entrance_x" => $pos->x,
				"entrance_y" => $pos->y,
				"entrance_z" => $pos->z,
				"exit_x" => $exit_x,
				"exit_y" => $exit_y,
				"exit_z" => $exit_z 
		);
		
		return $arenaInfo;
	}
	public function addGameButtonsOnTopFloor(Level $level) {
		$greenX = $this->pgin->getConfig ()->get ( "tntrun_start_button_x" );
		$greenY = $this->pgin->getConfig ()->get ( "tntrun_start_button_y" );
		$greenZ = $this->pgin->getConfig ()->get ( "tntrun_start_button_z" );
		$bgreen = $level->getBlock ( new Position ( $greenX, $greenY, $greenZ ) );
		// emeral block = 133
		$this->resetBlockByType ( $bgreen, $level, 133 );
		// add a torch on top
		$bgreen = $level->getBlock ( new Position ( $greenX, $greenY + 1, $greenZ ) );
		// emeral block = 133
		$this->resetBlockByType ( $bgreen, $level, 50 );
		
		$yellowX = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_x" );
		$yellowY = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_y" );
		$yellowZ = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_z" );
		$byellow = $level->getBlock ( new Position ( $yellowX, $yellowY, $yellowZ ) );
		// gold block = 41
		$this->resetBlockByType ( $byellow, $level, 41 );
		// add a torch on top
		$byellow = $level->getBlock ( new Position ( $yellowX, $yellowY + 1, $yellowZ ) );
		// emeral block = 133
		$this->resetBlockByType ( $byellow, $level, 50 );
		
		$gexitX = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_x" );
		$gexitY = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_y" );
		$gexitZ = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_z" );
		$bgexit = $level->getBlock ( new Position ( $gexitX, $gexitY, $gexitZ ) );
		// gold block = 41
		$this->resetBlockByType ( $bgexit, $level, 41 );
		// add a torch on top
		$bgexit = $level->getBlock ( new Position ( $gexitX, $gexitY + 1, $gexitZ ) );
		// emeral block = 133
		$this->resetBlockByType ( $bgexit, $level, 50 );
	}
	
	/**
	 * Build Floor
	 *
	 * @param Player $player        	
	 */
	public function ResetFloor(Level $level, $px, $py, $pz, $size) {
		// build walls
		// $this->buildWall($player, $size+1, $size+1-5, $px, $py-1, $pz, 1);
		// add bottom layer stone
		$this->resetBoardLayer ( $level, $px, $py, $pz, 46, $size );
		// add middle layer
		$this->resetBoardLayer ( $level, $px, $py + 1, $pz, 12, $size );
		// add top layer --98
		$this->resetBoardLayer ( $level, $px, $py + 2, $pz, 44, $size );
	}
	
	/**
	 * Reset board
	 *
	 * @param Level $level        	
	 * @param unknown $px        	
	 * @param unknown $py        	
	 * @param unknown $pz        	
	 * @param unknown $btype        	
	 * @param unknown $bsize        	
	 * @return multitype:
	 */
	public function resetBoardLayer(Level $level, $px, $py, $pz, $btype, $bsize) {
		$ret = [ ];
		// $level = $p->getLevel();
		$fx = $px;
		$fy = $py;
		$fz = $pz;
		for($rx = 0; $rx < $bsize; $rx ++) {
			// item = nulll can break anything
			$x = $fx + $rx;
			$y = $fy;
			$z = $fz;
			for($rz = 0; $rz < $bsize; $rz ++) {
				$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
				$this->resetBlockByType ( $rb, $level, $btype );
				// $this->log (TextFormat::BLUE . "boardlayer b: ".$rb->getID()." " . $x . " " . $y . " " . $z );
				$z ++;
			}
		}
		return $ret;
	}
	
	/**
	 * build board layer
	 *
	 * @param Player $p        	
	 * @param unknown $px        	
	 * @param unknown $py        	
	 * @param unknown $pz        	
	 * @param unknown $btype        	
	 * @return multitype:\pocketmine\block\Block
	 */
	public function buildBoardLayer(Level $level, $px, $py, $pz, $btype, $bsize) {
		$ret = [ ];
		// $level = $p->getLevel();
		$fx = $px;
		$fy = $py;
		$fz = $pz;
		for($rx = 0; $rx < $bsize; $rx ++) {
			// item = nulll can break anything
			$x = $fx + $rx;
			$y = $fy;
			$z = $fz;
			for($rz = 0; $rz < $bsize; $rz ++) {
				$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
				$this->renderBlockByType ( $rb, $level, $btype );
				// $this->log (TextFormat::BLUE . "boardlayer b: ".$rb->getID()." " . $x . " " . $y . " " . $z );
				// $ret [] = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
				$z ++;
			}
		}
		return $ret;
	}
	
	/**
	 * Render Wall
	 *
	 * @param Player $player        	
	 * @param Block $block        	
	 */
	public function renderWall(Level $level, $width, $height, $x, $y, $z, $wallType) {
		// $this->log ( TextFormat::RED . " render wall " );
		if ($wallType == null) {
			$wallType = 2;
		}
		$this->buildWall ( $level, $width, $height, $x, $y, $z, $wallType );
		// update player location
	}
	
	/**
	 * Render Water Tank
	 *
	 * @param Player $player        	
	 * @param unknown $radius        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildWaterTank(Level $level, $radius, $height, $dataX, $dataY, $dataZ, $wallType) {
		// $this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			// $level = $player->getLevel ();
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						// if ($rz > round($height/2)) {
						// $this->renderBlockByType ( $rb, $player, 0);
						// } else {
						$this->renderBlockByType ( $rb, $level, 0 );
						// }
						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($radius - 1) || $rz == ($radius - 1) || $rx == 0 || $rz == 0 || $ry == ($radius - 1) || $ry == 0) {
							// $this->renderBlockByType ( $rb, $player, $wallType );
							if ($rx == 2 && $ry > 0 && $ry < ($radius - 1)) {
								// KEEP DOOR OPEN
								if (($z + $rz) == $rb->z) {
									// $this->log ( TextFormat::BLUE . "door blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
									if ($doorExist < 2) {
										$this->renderBlockByType ( $rb, $level, 42 );
										$doorExist ++;
									} else {
										if ($ry < 3) {
											$this->renderBlockByType ( $rb, $level, 42 );
										} else {
											$this->renderBlockByType ( $rb, $level, 46 );
										}
									}
								} else {
									$this->renderBlockByType ( $rb, $level, 46 );
								}
								// $this->renderBlockByType ( $rb, $player,89);
							} else if ($ry == 0) {
								// $this->log ( TextFormat::BLUE . "floor blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->renderBlockByType ( $rb, $level, 24 );
							} else if ($ry == ($radius - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->renderBlockByType ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->renderRandomBlocks ( $rb, $level );
							} else if ($rx == ($radius - 1)) {
								$this->renderBlockByType ( $rb, $level, 46 );
							} else {
								$this->renderBlockByType ( $rb, $level, 89 );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	public function buildGlassTop(Level $level, $radius, $height, $dataX, $dataY, $dataZ, $wallType) {
		// $this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			// $level = $player->getLevel ();
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						// if ($rz > round($height/2)) {
						// $this->renderBlockByType ( $rb, $player, 0);
						// } else {
						$this->renderBlockByType ( $rb, $level, 0 );
						// }
						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($radius - 1) || $rz == ($radius - 1) || $rx == 0 || $rz == 0 || $ry == ($radius - 1) || $ry == 0) {
							// $this->renderBlockByType ( $rb, $player, $wallType );
							if ($rx == 2 && $ry > 0 && $ry < ($radius - 1)) {
								$this->renderBlockByType ( $rb, $level, 50 );
							} else if ($ry == 0) {
								// $this->log ( TextFormat::BLUE . "floor blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->renderBlockByType ( $rb, $level, 20 );
							} else if ($ry == ($radius - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->renderBlockByType ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->renderBlockByType ( $rb, $level, 20 );
							} else if ($rx == ($radius - 1)) {
								$this->renderBlockByType ( $rb, $level, 20 );
							} else {
								$this->renderBlockByType ( $rb, $level, 50 );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * Render Wall
	 *
	 * @param Player $player        	
	 * @param unknown $radius        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $wallType        	
	 * @return boolean
	 */
	public function buildWall(Level $level, $radius, $height, $dataX, $dataY, $dataZ, $wallType) {
		// $this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			// $level = $player->getLevel ();
			for($rx = 0; $rx < $radius; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $radius; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->removeBlocks ( $rb, $level );
						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($radius - 1) || $rz == ($radius - 1) || $rx == 0 || $rz == 0 || $ry == ($radius - 1) || $ry == 0) {
							// $this->renderBlockByType ( $rb, $player, $wallType );
							if ($rx == 2 && $ry > 0 && $ry < ($radius - 1)) {
								$this->renderRandomBlocks ( $rb, $level );
							} else if ($ry == 0) {
								// $this->log ( TextFormat::BLUE . "floor blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								// $this->renderBlockByType ( $rb, $player, 0 );
							} else if ($ry == ($radius - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->renderBlockByType ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->renderRandomBlocks ( $rb, $level );
							} else if ($rx == ($radius - 1)) {
								$this->renderRandomBlocks ( $rb, $level );
							} else {
								$this->renderRandomBlocks ( $rb, $level );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * build wall by type
	 *
	 * @param Level $level        	
	 * @param unknown $width        	
	 * @param unknown $height        	
	 * @param unknown $dataX        	
	 * @param unknown $dataY        	
	 * @param unknown $dataZ        	
	 * @param unknown $blockType        	
	 * @return boolean
	 */
	public function buildHoloWallByType(Level $level, $width, $height, $dataX, $dataY, $dataZ, $blockType) {
		// $this->log ( TextFormat::BLUE . "build Player location : " . $player->x . " " . $player->y . " " . $player->z );
		$status = false;
		try {
			$doorExist = 0;
			$x = $dataX;
			// $level = $player->getLevel ();
			for($rx = 0; $rx < $width; $rx ++) {
				$y = $dataY;
				for($ry = 0; $ry < $height; $ry ++) {
					$z = $dataZ;
					for($rz = 0; $rz < $width; $rz ++) {
						$rb = $level->getBlock ( new Vector3 ( $x, $y, $z ) );
						$this->removeBlocks ( $rb, $level );
						// $this->log ( TextFormat::BLUE . "+ remove X blocks: " . $x . " " . $y . " " . $z );
						// build the wall at edge - $ry control the roof and base
						if ($rx == ($width - 1) || $rz == ($width - 1) || $rx == 0 || $rz == 0 || $ry == ($width - 1) || $ry == 0) {
							// $this->renderBlockByType ( $rb, $player, $wallType );
							if ($rx == 2 && $ry > 0 && $ry < ($width - 1)) {
								$this->resetBlock ( $rb, $level, $blockType );
							} else if ($ry == 0) {
								// $this->log ( TextFormat::BLUE . "floor blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								// $this->renderBlockByType ( $rb, $player, 0 );
							} else if ($ry == ($width - 1)) {
								// $this->log ( TextFormat::BLUE . "roof blocks: " . $rb->x . " " . $rb->y . " " . $rb->z );
								$this->resetBlock ( $rb, $level, 0 );
							} else if ($rx == 0 || $rz == 0) {
								$this->resetBlock ( $rb, $level, $blockType );
							} else if ($rx == ($width - 1)) {
								$this->resetBlock ( $rb, $level, $blockType );
							} else {
								$this->resetBlock ( $rb, $level, 46 );
							}
						}
						$z ++;
					}
					$y ++;
				}
				$x ++;
			}
			// update status
			$status = true;
		} catch ( \Exception $e ) {
			$this->log ( "Error:" . $e->getMessage () );
		}
		return $status;
	}
	
	/**
	 * remove blocks
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 */
	public function removeBlocks(Block $block, Level $level) {
		$this->updateBlock ( $block, $level, 0 );
	}
	
	/**
	 * optimized block removal for ingame players only
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 */
	public function removeBlockForInGamePlayers(Block $block, Player $p, $blockType) {
		$pk = new UpdateBlockPacket ();
		$pk->x = $block->getX ();
		$pk->y = $block->getY ();
		$pk->z = $block->getZ ();
		$pk->block = $blockType;
		$pk->meta = 0;
		$p->dataPacket ( $pk );
		$p->getLevel ()->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $pk->block );
		
		$pos = new Position ( $block->x, $block->y, $block->z );
		$block = $p->getLevel ()->getBlock ( $pos );
		$direct = true;
		$update = true;
		$p->getLevel ()->setBlock ( $pos, $block, $direct, $update );
	}
	
	/**
	 * remove floor blocks
	 *
	 * @param unknown $topblock        	
	 * @param unknown $tntblock        	
	 */
	public function removeUpdateBlock($topblock, $tntblock) {
		foreach ( $this->pgin->livePlayers as $livep ) {
			// if ($livep instanceof MGArenaPlayer) {
			// $this->pgin->arenaBuilder->removeBlocks($topblock, $livep->player);
			// $this->pgin->arenaBuilder->removeBlocks($tntblock, $livep->player);
			// } else {
			// BEFORE-------------
			// $this->pgin->arenaBuilder->removeBlocks($topblock, $livep);
			// $this->pgin->arenaBuilder->removeBlocks($tntblock, $livep);
			// BEFORE --------------
			// }
			// $this->pgin->arenaBuilder->renderBlockByType ( $sandblock, $livep, 0);
			// this reduce number of update packets
			$this->removeBlockForInGamePlayers ( $topblock, $livep, 0 );
			$this->removeBlockForInGamePlayers ( $tntblock, $livep, 0 );
		}
	}
	
	/**
	 * remove blocks
	 *
	 * @param array $blocks        	
	 * @param Player $p        	
	 */
	public function removeEntityBlock(FallingBlock $entityblock, Level $level) {
		$pk = new UpdateBlockPacket ();
		$pk->x = $entityblock->getX ();
		$pk->y = $entityblock->getY ();
		$pk->z = $entityblock->getZ ();
		$pk->block = 0;
		$pk->meta = 0;
		$entityblock->dataPacket ( $pk );
		$entityblock->getLevel ()->setBlockIdAt ( $entityblock->getX (), $entityblock->getY (), $entityblock->getZ (), 0 );
		
		$pos = new Position ( $block->x, $block->y, $block->z );
		$block = $level->getBlock ( $pos );
		$direct = true;
		$update = true;
		$level->setBlock ( $pos, $block, $direct, $update );
	}
	
	/**
	 * reset block
	 *
	 * @param Block $block        	
	 * @param Level $level        	
	 * @param unknown $blockType        	
	 */
	public function resetBlock(Block $block, Level $level, $blockType) {
		$players = $level->getPlayers ();
		foreach ( $players as $p ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blockType;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			$p->getLevel ()->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $pk->block );
			
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $p->getLevel ()->getBlock ( $pos );
			$direct = true;
			$update = true;
			$p->getLevel ()->setBlock ( $pos, $block, $direct, $update );
		}
	}
	public function updateBlock(Block $block, Level $level, $blockType) {
		$players = $level->getPlayers ();
		foreach ( $players as $p ) {
			$pk = new UpdateBlockPacket ();
			$pk->x = $block->getX ();
			$pk->y = $block->getY ();
			$pk->z = $block->getZ ();
			$pk->block = $blockType;
			$pk->meta = 0;
			$p->dataPacket ( $pk );
			$p->getLevel ()->setBlockIdAt ( $block->getX (), $block->getY (), $block->getZ (), $pk->block );
			
			$pos = new Position ( $block->x, $block->y, $block->z );
			$block = $p->getLevel ()->getBlock ( $pos );
			$direct = true;
			$update = true;
			$p->getLevel ()->setBlock ( $pos, $block, $direct, $update );
		}
	}
	
	/**
	 * render random blocks
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 */
	public function renderRandomBlocks(Block $block, Level $level) {
		$b = array_rand ( $this->boardBlocksTypes );
		$blockType = $this->boardBlocksTypes [$b];
		// randomly place a mine
		$this->updateBlock ( $block, $level, $blockType );
	}
	
	/**
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 * @param unknown $blockType        	
	 */
	public function renderBlockByType(Block $block, Level $level, $blockType) {
		// randomly place a mine
		$this->updateBlock ( $block, $level, $blockType );
	}
	
	/**
	 *
	 * @param Block $block        	
	 * @param Player $p        	
	 * @param unknown $blockType        	
	 */
	public function resetBlockByType(Block $block, Level $level, $blockType) {
		// randomly place a mine
		$this->resetBlock ( $block, $level, $blockType );
	}
	
	/**
	 * remove arena
	 *
	 * @param unknown $player        	
	 * @param unknown $xx        	
	 * @param unknown $yy        	
	 * @param unknown $zz        	
	 */
	public function removeArena(Level $level, $xx, $yy, $zz) {
		// $wallheighSize = $this->pgin->getConfig ()->get ( "wallheight" );
		$bsize = $this->boardsize;
		$bheight = $this->boardheight;
		
		$wallheighSize = $yy + $bsize + $bheight;
		$bsize = $this->boardsize;
		$xmax = $this->boardsize + 3;
		$ymax = $this->boardsize;
		
		For($z = 0; $z <= $xmax; $z ++) {
			For($x = 0; $x <= $xmax; $x ++) {
				For($y = 0; $y <= $wallheighSize; $y ++) {
					$mx = $xx + $x;
					$my = $yy + $y;
					$mz = $zz + $z;
					$bk = $level->getBlock ( new Vector3 ( $mx, $my, $mz ) );
					// $this->log ( TextFormat::GREEN . ".removed: " . $bk . " at " . $bk->x . " " . $bk->y . " " . $bk->z );
					$this->removeBlocks ( $bk, $level );
				}
			}
		}
	}
	public function removeGlassTop($size, Level $level, $xx, $yy, $zz) {
		// $wallheighSize = $this->pgin->getConfig ()->get ( "wallheight" );
		$wallheighSize = 70;
		$bsize = $size;
		$xmax = $size + 3;
		$ymax = $size;
		
		For($z = 0; $z <= $xmax; $z ++) {
			For($x = 0; $x <= $xmax; $x ++) {
				For($y = 0; $y <= $wallheighSize; $y ++) {
					$mx = $xx + $x;
					$my = $yy + $y;
					$mz = $zz + $z;
					$bk = $level->getBlock ( new Vector3 ( $mx, $my, $mz ) );
					// $this->log ( TextFormat::GREEN . ".removed: " . $bk . " at " . $bk->x . " " . $bk->y . " " . $bk->z );
					$this->renderBlockByType ( $bk, $level, 0 );
				}
			}
		}
	}
	
	/**
	 * Load World
	 *
	 * @param Player $sender        	
	 * @param Level $plevel        	
	 * @return NULL|\pocketmine\level\Level
	 */
	public function loadWorldLevel(Level $plevel, $tntrunhome) {
		$homelevel = null;
		
		if (! $plevel->getServer ()->isLevelGenerated ( $tntrunhome )) {
			$this->pgin->getLogger ()->info ( "generating new world :" . $tntrunhome );
			$plevel->getServer ()->generateLevel ( $tntrunhome );
		}
		
		if (! $plevel->getServer ()->isLevelLoaded ( $tntrunhome )) {
			$this->pgin->getLogger ()->info ( "loading world :" . $tntrunhome );
			$plevel->getServer ()->loadLevel ( $tntrunhome );
		}
		
		$this->pgin->getLogger ()->info ( "getting world -" . $tntrunhome );
		$homelevel = $sender->getServer ()->getLevelByName ( $tntrunhome );
		
		if ($homelevel == null) {
			$this->pgin->getLogger ()->info ( "Unable to get world: [" . $tntrunhome . "] please contact server administrator!" );
			return null;
		}
		$this->pgin->getLogger ()->info ( "found world -" . $tntrunhome );
		return $homelevel;
}
	
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}