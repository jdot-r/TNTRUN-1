<?php
namespace game\api;

/**
 * Round
 *
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class MGRound {
	protected $minPLayer;
	protected $maxPlayer;
	protected $prepareTime;
	protected $roundTime;
	protected $exitLocation;
	protected $plugin;
	protected $time;
	protected $stage;
	protected $world;
	protected $arena;
	protected $spawns;
	protected $minBound;
	protected $maxBound;
	protected $players;
	protected $timeHandler;
	protected $damage;
	protected $pvp;
	protected $rollback;
	
	public function __construct($start, $stop) {
		$this->roundTime = $start + $stop;
		$this->time = time ();
	}
	public function getPlugin() {
		return $this->plugin;
	}
	public function getMinigame() {
	}
	public function getArena() {
		return $this->arena;
	}
	public function getStage() {
		return $this->stage;
	}
	public function getTime() {
		return $this->time;
	}
	public function getRemainingTime() {
		return $this->roundTime - time;
	}
	public function getPreparationTime() {
		return $this->prepareTime;
	}
	public function getPlayingTime() {
		return $this->time;
	}
	public function setArena($a) {
		$this->arena = a;
	}
	public function setStage($s) {
		$this->stage = s;
	}
	public function setTime($t) {
		$this->time = $t;
	}
	public function setPreparationTime($t) {
		$this->prepareTime = t;
	}
	public function setPlayingTime($t) {
		$this->time = $t;
	}
	public function tick() {
	}
	public function subsctractTime($t) {
		$this->time - t;
	}
	public function addTime($t) {
		$this->time + t;
	}
	public function destroy() {
	}
	public function getPlayerList() {
	}
	public function getPlayers() {
	}
	public function getLivePlayers() {
	}
	public function getSpectatingPlayerList() {
	}
	public function getPlayerCount() {
	}
	public function getLivePlayerCount() {
	}
	public function getSpectatingPlayerCount() {
	}
	public function start() {
	}
	public function end($wait) {
	}
	public function getMinPlayers() {
	}
	public function setMinPlayers($c) {
	}
	public function getMaxPlayers() {
	}
	public function setMaxPlayers($c) {
	}
	public function addSign(Location $loc, LobbyType $lobby, $c) {
	}
	public function getExitLocation() {
	}
	public function setExitLocation(Location $loc) {
	}
	public function isPvPAllow() {
	}
	public function setPvPAllow($allow) {
	}
	public function isDamageAllow() {
	}
	public function setRollBack() {
	}
	public function getRollBack($b) {
	}
	public function brodcast($msg) {
	}
	public function getRollBackManager() {
	}
	public function getConfigManager() {
	}
}