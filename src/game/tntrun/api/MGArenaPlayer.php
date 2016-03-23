<?php

namespace game\api;

use pocketmine\Player;

/**
 * Arena Player

 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */

class MGArenaPlayer {
	
	public $playerName;
	public $player;

	public $areaneOwner;
	public $arenaName;
	public $arena; 
	
	public $arena_x;
	public $arena_y;
	public $arena_z;
		
	public $home_x;
	public $home_y;
	public $home_z;

	public $lobby_x;
	public $lobby_y;
	public $lobby_z;
		
	public $currentStatus;	
	public $rating;
	public $isAllow;
	public $game;
			
	public function __construct(Player $p) {
		$this->player = $p;
		$this->playerName = $p->getName();
	}

	/* @TODO
	 * teleport player outside when player touch the ground
	*/		
}