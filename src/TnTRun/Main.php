<?php
namespace TNTRun;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\tile\Sign;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
class Main extends PluginBase implements Listener {

public function onEnable() {
    $this->getLogger()->info("TnTRun Enabled.");
