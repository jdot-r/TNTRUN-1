<?php

namespace game\tntrun;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\level\Position;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class PlayRoundTimeout extends PluginTask {
	public $plugin;
	
	public function __construct(TNTRun $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	public function onRun($ticks) {
			
		 if ( cont($this->plugin->livePlayers) > 0 ) {		
			$this->player->sendMessage("-----------------------------------");
			$this->player->sendMessage("| TIMES UP! - GAME OVER!!!        |");
			$this->player->sendMessage("-----------------------------------");
			$this->player->sendMessage("| stop and Try again?              ");
			$this->player->sendMessage("| /tntrun stop                   ");
			$this->player->sendMessage("| /tntrun create                 ");			
			$this->player->sendMessage("-----------------------------------");			
			$this->plugin->tntRunCommand->cleanUpArena( $this->player);			
		 }
	}
	
	public function onCancel() {

	}
}
