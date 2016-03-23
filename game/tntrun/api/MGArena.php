<?php
namespace game\api;

use game\tntrun\TNTArenaBuilder;
use pocketmine\Player;
use pocketmine\level\format\PocketChunkParser;
use pocketmine\level\Position;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class MGArena {
	
	private $plugin; // string
	public $arenaName; // Arena object
	public $world; // string
	public $owner; //owner
	public $arenaDAO;

	public $builder;
	public $arenaPlayerDAO;
	
	public function __construct($pg) {
		$this->plugin = $pg;		
		$this->arenaDAO = new MGArenaDAO($pg);
		$this->arenaPlayerDAO = new MGArenaPlayerDAO($pg);
	}
	
	public function createRound(String $area) {
		//create round
	}
	
	public function getRound(String $area) {
		//get current round		
	}

	public function getArena(Player $p, $arenaName) {
		return $this->arenaDAO->retrieveArena($arenaName, $p->getLevel()->getName());
	}
	
	public function getOwnerArena(Player $p, String $arenaName) {
		return $this->arenaDAO->retrieveArenaByName($arenaName, $p->getName(), $p->getLevel()->getName());
	}
		
	public function setBuilder ($builder) {
		$this->builder = $builder;
	}
	
	public function buildArena($arenaName,$world, Player $owner) {				
		$arenaInfo = [];
				
		//store this in data store
		$this->world = $world;
		$this->arenaName = $arenaName;		
		$this->owner = $owner;		
		if ($this->builder == null) {
			$this->builder = new TNTArenaBuilder($this->plugin);			
		}	

		try {
			$pos = $owner->getPosition();			
			//get arena position
// 			$lx = $this->plugin->getConfig ()->get ( "arena_x" );
// 			$ly = $this->plugin->getConfig ()->get ( "arena_y" );
// 			$lz = $this->plugin->getConfig ()->get ( "arena_z" );
// 			if ($lx!=null && $ly!=null && $lz!=null) {
// 				$pos = new Position($lx,$ly,$lz);				
// 			}
									
			$arenaInfo = $this->builder->buildArena($owner, $pos);
		
			//save the record
			$entrance_x = $arenaInfo["entrance_x"];
			$entrance_y = $arenaInfo["entrance_y"];
			$entrance_z = $arenaInfo["entrance_z"];
			$exit_x = $arenaInfo["exit_x"];
			$exit_y = $arenaInfo["exit_y"];
			$exit_z = $arenaInfo["exit_z"];
			$size = 12;
			$capacity = 10;
			$level = 3;
			$isprivate = 0;
			$game = "TNTRUN";
			
			//upset record
			$this->arenaDAO->upsetArena($this->arenaName, $this->owner->getName(), $this->world, $entrance_x, $entrance_y, $entrance_z, $exit_x, $exit_y, $exit_z, $size, $capacity, $level, $isprivate, $game);
		} catch (\Exception $exp) {
			$this->log($exp->getMessage(), 0);					
		}		
		return $arenaInfo;				
	}

	/**
	 * join arena
	 * 
	 * @param unknown $pname
	 * @param unknown $arena
	 * @param unknown $world
	 * @param Player $player
	 */
	public function joinArena($pname, $arena, $world, $x, $y, $z) {
		return $this->arenaPlayerDAO->upsetArenaPlayer($pname, $arena, $world, $x, $y, $z, 0,0,"TNTRUN");		
	}
	
	public function unjoinArena($arena) {
		$this->arenaPlayerDAO->removeArena($arena);
	}
	
	public function deleteArena($arena, $owner, $world) {
		return $this->arenaDAO->removeArena($arena, $owner, $world);
	}
	
	public function getPlayers($arenaName) {
		if ($this->arenaName==null) {
			$this->arenaName = $arenaName;
		}
		//get number of players inside the arena
		return $this->arenaPlayerDAO->retrieveArenaPlayers($arenaName);
	}
	
	public function log(String $message, integer $level) {
		$this->plugin->getLogger()->info($message);
	}
}
