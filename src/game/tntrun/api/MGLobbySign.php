<?php
namespace game\api;


/**
 * Lobby Sign
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */

class MGLobbySign {
	private $x;
	private $y;
	private $z; 
	
	private $plugin;
	private $world;
	private $arena;
	private $number;
	private $type;
	private $index;
	
	public function __construct($x,$y,$z,$plugin, $world, $arena, $idx,$lobbytype) {

	}
	
	public function getX() {
		return $x;
	}	
	
	public function getY() {
		return $y;
	}
	
	public function getZ() {
		
	}
	public function getWorld() {
		
	}
	public function setWorld() {
		
	}
	
	public function getArena() {
		
	}
	
	public function setArena($a) {
		$this->arena = $a;
	}
	
	public function getNumber() {
		return $this->number;
	}
	
	public function setNumber($n) {
		$this->number = $n;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($lobbyType) {
		$this->type=$lobbyType;
	}
	
	public function getWorld($w) {
		$this->world = $w;		
	}
	
}