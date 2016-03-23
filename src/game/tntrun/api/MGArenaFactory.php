<?php

namespace game\api;

use game\tntrun\TNTArenaBuilder;


/**
 * Arena Player

/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */

final class MGArenaFactory {
	protected static $newInstance = null; // boolean
	private $plugin; // string
	private $yaml; // configuration
	private $timeHandle; // int
	private $arena; // object		
	/**
	 * Private Constructor
	 * 
	 * @param unknown $pg
	 * @param unknown $arena
	 * @param unknown $world
	 */
	private function __construct($pg) {
		$this->plugin = $pg;
	}

	/**
	 * Instance 
	 * 
	 * @param unknown $plugin
	 * @param unknown $arena
	 * @param unknown $world
	 */
	public static function createArenaFactory($pg) {
		if (static::$newInstance === null) {
			static::$newInstance = new static ($pg);
		}
		return static::$newInstance;
	}
	
	public function newArena() {
		//create game arena
		$this->arena = new MGArena($this->plugin);
		$this->arena->setBuilder( (new TNTArenaBuilder($this->plugin)));		
		return $this->arena;
	}
		
	public function getPlugIn() {
		return $this->plugin;
	}

	public function getArena() {
		return $this->arena;
	}

	public function addSpawn(Location $loc) {
	}
	public function deleteSpawn(Location $loc) {
	}
	public function setMinBound($x, $y, $z) {
	}
	public function setMaxBound($x, $y, $z) {
	}
	public function writeChange() {
	}
	public function isNewInstance() {
		return $newInstance == null ? null : $newInstance;
	}
	public function destroy() {
	}
}