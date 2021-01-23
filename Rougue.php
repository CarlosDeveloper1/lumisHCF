<?php

namespace VitalHCF\player;

use VitalHCF\{Loader, Factions};
use VitalHCF\provider\YamlProvider;

use VitalHCF\API\InvMenu\Handler;

use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TE;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\item\{Item, ItemIds};

use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;

use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;

class Player extends \pocketmine\Player {

    const LEADER = "Leader", CO_LEADER = "Co_Leader", MEMBER = "Member";

    const FACTION_CHAT = "Faction", PUBLIC_CHAT = "Public", STAFF_CHAT = "Staff";

    /** @var Int */
    protected $bardEnergy = 0, $archerEnergy = 0;

    /** @var Int */
    protected $combatTagTime = 0;

    /** @var Int */
    protected $enderPearlTime = 0;

    /** @var Int */
    protected $stormBreakerTime = 0;

    /** @var Int */
    protected $antiTrapperTime = 0;

    /** @var Int */
    protected $snowballTime = 0;
    
    /** @var Int */
    protected $archerTagTime = 0;
    
    /** @var Int */
    protected $goldenAppleTime = 0;

    /** @var Int */
    protected $playerClaimCost = 0;

    /** @var Int */
    protected $movementTime = 0;

    /** @var Int */
    protected $teleportHomeTime = 0, $teleportStuckTime = 0, $logoutTime = 0;

    /** @var bool */
    protected $godMode = false;

    /** @var bool */
    protected $combatTag = false;

    /** @var bool */
    protected $enderPearl = false;

    /** @var bool */
    protected $stormBreaker = false;

    /** @var bool */
    protected $antiTrapper = false, $antiTrapperTarget = false;

    /** @var bool */
    protected $snowBall = false;
    
    /** @var bool */
    protected $archerTag = false;
    
    /** @var bool */
    protected $rogueTag = false;
    
    /** @var bool */
    protected $goldenApple = false;

    /** @var bool */
    protected $playerCanInteract = false;
    
    /** @var bool */
    protected $viewingMap = false;

    /** @var bool */
    protected $invitation = false;

    /** @var bool */
    protected $teleportHome = false, $teleportStuck = false, $logout = false;
    
    /** @var String */
    protected $currentInvite = null;

    /** @var String */
    protected $playerChat = null;
    
    /** @var String */
    protected $currentRegion = "Unknown";

    /** @var Array[] */
    protected $playerClass = [];
    
    /** @var Array[] */
    protected $inventoryHandler = [];

    /**
     * @return Int|null
     */
    public function getBardEnergy() : ?Int {
        return $this->bardEnergy;
    }

    /**
     * @param Int $bardEnergy
     */
    public function setBardEnergy(Int $bardEnergy){
        $this->bardEnergy = $bardEnergy;
    }
    
    /**
     * @return Int|null
     */
    public function getArcherEnergy() : ?Int {
        return $this->archerEnergy;
    }

    /**
     * @param Int $archerEnergy
     */
    public function setArcherEnergy(Int $archerEnergy){
        $this->archerEnergy = $archerEnergy;
    }
    
    /**
     * @param Int $itemId|null
     * @return Int
     */
    public function getBardEnergyCost(Int $itemId = null) : Int {
    	$energyCost = null;
    	switch($itemId){
    		case ItemIds::SUGAR:
    		$energyCost = 20;
    		break;
    		case ItemIds::IRON_INGOT:
    		$energyCost = 30;
    		break;
    		case ItemIds::BLAZE_POWDER:
    		$energyCost = 40;
    		break;
    		case ItemIds::GHAST_TEAR:
    		$energyCost = 35;
    		break;
    		case ItemIds::FEATHER:
    		$energyCost = 30;
    		break;
    		case ItemIds::DYE:
    		$energyCost = 30;
    		break;
    		case ItemIds::MAGMA_CREAM:
    		$energyCost = 25;
    		break;
    		case ItemIds::SPIDER_EYE:
    		$energyCost = 40;
    		break;
    	}
    	return $energyCost;
    }

    /**
     * @return bool
     */
    public function isGodMode() : bool {
        return $this->godMode;
    }

    /**
     * @param bool $godMode
     */
    public function setGodMode(bool $godMode){
        $this->godMode = $godMode;
    }

    /**
     * @return bool
     */
    public function isCombatTag() : bool {
        return $this->combatTag;
    }

    /**
     * @param bool $combatTag
     */
    public function setCombatTag(bool $combatTag){
        $this->combatTag = $combatTag;
    }
    
    /**
     * @param Int $combatTagTime
     */
    public function setCombatTagTime(Int $combatTagTime){
    	$this->combatTagTime = $combatTagTime;
    }
    
    /**
     * @return Int
     */
    public function getCombatTagTime() : Int {
    	return $this->combatTagTime;
    }

    /**
     * @return bool
     */
    public function isEnderPearl() : bool {
        return $this->enderPearl;
    }
    
    /**
     * @param bool $enderPearl
     */
    public function setEnderPearl(bool $enderPearl){
        $this->enderPearl = $enderPearl;
    }
    
    /**
     * @param Int $enderPearlTime
     */
    public function setEnderPearlTime(Int $enderPearlTime){
    	$this->enderPearlTime = $enderPearlTime;
    }
    
    /**
     * @return Int
     */
    public function getEnderPearlTime() : Int {
    	return $this->enderPearlTime;
    }

    /**
     * @return bool
     */
    public function isStormBreaker() : bool {
        return $this->stormBreaker;
    }
    
    /**
     * @param bool $stormBreaker
     */
    public function setStormBreaker(bool $stormBreaker){
        $this->stormBreaker = $stormBreaker;
    }
    
    /**
     * @param Int $stormBreakerTime
     */
    public function setStormBreakerTime(Int $stormBreakerTime){
    	$this->stormBreakerTime = $stormBreakerTime;
    }
    
    /**
     * @return Int
     */
    public function getStormBreakerTime() : Int {
    	return $this->stormBreakerTime;
    }

    /**
     * @return bool
     */
    public function isAntiTrapperTarget() : bool {
        return $this->antiTrapperTarget;
    }

    /**
     * @param bool $antiTrapperTarget
     */
    public function setAntiTrapperTarget(bool $antiTrapperTarget){
        $this->antiTrapperTarget = $antiTrapperTarget;
    }

    /**
     * @return bool
     */
    public function isAntiTrapper() : bool {
        return $this->antiTrapper;
    }
    
    /**
     * @param bool $antiTrapper
     */
    public function setAntiTrapper(bool $antiTrapper){
        $this->antiTrapper = $antiTrapper;
    }
    
    /**
     * @param Int $antiTrapperTime
     */
    public function setAntiTrapperTime(Int $antiTrapperTime){
    	$this->antiTrapperTime = $antiTrapperTime;
    }
    
    /**
     * @return Int
     */
    public function getAntiTrapperTime() : Int {
    	return $this->antiTrapperTime;
    }
    
    /**
     * @return bool
     */
    public function isArcherTag() : bool {
    	return $this->archerTag;
    }
    
    /**
     * @param bool $archerTag
     */
    public function setArcherTag(bool $archerTag){
    	$this->archerTag = $archerTag;
    }
    
    /**
     * @param Int $archerTagTime
     */
    public function setArcherTagTime(Int $archerTagTime){
    	$this->archerTagTime = $archerTagTime;
    }
    
    /**
     * @return Int
     */
    public function getArcherTagTime() : Int {
    	return $this->archerTagTime;
    }
    
    /**
     * @return bool
     */
    public function isRogueTag() : bool {
    	return $this->RogueTag;
    }
    
    /**
     * @param bool $rogueTag
     */
    public function setRogueTag(bool $rogueTag){
    	$this->rogueTag = $rogueTag;
    }
    
    /**
     * @param Int $rogueTagTime
     */
    public function setRogueTagTime(Int $rogueTagTime){
    	$this->rogueTagTime = $rogueTagTime;
    }
    
    /**
     * @return Int
     */
    public function getRogueTagTime() : Int {
    	return $this->rogueTagTime;
    }
    
    /**
     * @return bool
     */
    public function resetPvPTimer()
    {
        $this->pvp["enabled"] = (bool) 1; //true
        $this->pvp["timeleft"] = $this->time;
        $this->setNameTag(TextFormat::RESET . TextFormat::GREEN . "[PvPTimer] " . TextFormat::GRAY . $this->getName());
    }

    public function unsetPvPTimer()
    {
        $this->pvp["enabled"] = (bool) 0; //false
        $this->pvp["timeleft"] = 0;
        $this->setNameTag(TextFormat::RESET . TextFormat::GRAY . $this->getName());
    }

    /**
     * @return int
     */
    public function getPvPTimer(): int
    {
        return $this->pvp["timeleft"];
    }

    /**
     * @param int $time
     */
    public function setPvPTimer(int $time)
    {
        $this->pvp["timeleft"] = $time;
    }

    /**
     * @param bool $bool
     */
    public function setPvP(bool $bool)
    {
        $this->pvp["enabled"] = (bool) $bool;
    }

    /**
     * @return bool
     */
    public function isPvP(): bool
    {
        return (bool)$this->pvp["enabled"];
    }

    /**
     * @return bool
     */
    public function isSnowball() : bool {
        return $this->snowBall;
    }

    /**
     * @param bool $snowBall
     */
    public function setSnowball(bool $snowBall){
        $this->snowBall = $snowBall;
    }

    /**
     * @param Int $snowballTime
     */
    public function setSnowballTime(Int $snowballTime){
        $this->snowballTime = $snowballTime;
    }

    /**
     * @return Int
     */
    public function getSnowballTime() : Int {
        return $this->snowballTime;
    }
    
    /**
     * @return bool
     */
    public function isGoldenGapple() : bool {
    	return $this->goldenApple;
    }
    
    /**
     * @param bool $goldenApple
     */
    public function setGoldenApple(bool $goldenApple){
    	$this->goldenApple = $goldenApple;
    }
    
    /**
     * @param Int $goldenAppleTime
     */
    public function setGoldenAppleTime(Int $goldenAppleTime){
    	$this->goldenAppleTime = $goldenAppleTime;
    }
    
    /**
     * @return Int
     */
    public function getGoldenAppleTime() : Int {
    	return $this->goldenAppleTime;
    }

    /**
     * @return bool
     */
    public function isTeleportingHome() : bool {
        return $this->teleportHome;
    }

    /**
     * @param bool $teleportHome
     */
    public function setTeleportingHome(bool $teleportHome){
        $this->teleportHome = $teleportHome;
    }

    /**
     * @param Int $teleportHomeTime
     */
    public function setTeleportingHomeTime(Int $teleportHomeTime){
        $this->teleportHomeTime = $teleportHomeTime;
    }

    /**
     * @return Int
     */
    public function getTeleportingHomeTime() : Int {
        return $this->teleportHomeTime;
    }
    
    /**
     * @return bool
     */
    public function isLogout() : bool {
        return $this->logout;
    }

    /**
     * @param bool $logout
     */
    public function setLogout(bool $logout){
        $this->logout = $logout;
    }

    /**
     * @param Int $logoutTime
     */
    public function setLogoutTime(Int $logoutTime){
        $this->logoutTime = $logoutTime;
    }

    /**
     * @return Int
     */
    public function getLogoutTime() : Int {
        return $this->logoutTime;
    }
    
    /**
     * @return bool
     */
    public function isTeleportingStuck() : bool {
        return $this->teleportStuck;
    }

    /**
     * @param bool $teleportStuck
     */
    public function setTeleportingStuck(bool $teleportStuck){
        $this->teleportStuck = $teleportStuck;
    }

    /**
     * @param Int $teleportStuckTime
     */
    public function setTeleportingStuckTime(Int $teleportStuckTime){
        $this->teleportStuckTime = $teleportStuckTime;
    }

    /**
     * @return Int
     */
    public function getTeleportingStuckTime() : Int {
        return $this->teleportStuckTime;
    }

    /**
     * @param Int $movementTime
     */
    public function setMovementTime($movementTime){
        $this->movementTime = $movementTime;
    }

    /**
     * @return bool
     */
    public function isMovementTime() : bool {
        return (time() - $this->movementTime) < 0;
    }

    /**
     * @return bool
     */
    public function isInteract() : bool {
        return $this->playerCanInteract;
    }

    /**
     * @param bool $playerCanInteract
     */
    public function setInteract(bool $playerCanInteract){
        $this->playerCanInteract = $playerCanInteract;
    }
    
    /**
     * @return void
     */
    public function addTool() : void {
    	$item = Item::get(ItemIds::DIAMOND_HOE, 0, 1)->setCustomName(TE::DARK_PURPLE."Claim Tool")->setLore([TE::GRAY."Touch First Position, Touch Second Position!"]);
		$this->getInventory()->addItem($item);
    }
    
    /**
     * @return void
     */
    public function deleteTool() : void {
    	$player->getInventory()->removeItem(Item::get(ItemIds::DIAMOND_HOE, 0, 1));
    }

    /**
     * @return Int
     */
    public function getClaimCost() : Int {
        return $this->playerClaimCost;
    }

    /**
     * @param Int $playerClaimCost
     */
    public function setClaimCost(Int $playerClaimCost){
        $this->playerClaimCost = $playerClaimCost;
    }
    
    /**
     * @return String|null
     */
    public function getRegion() : ?String {
    	return $this->currentRegion;
    }
    
    /**
     * @param String $currentRegion|null
     */
    public function setRegion(?String $currentRegion){
    	$this->currentRegion = $currentRegion;
    }
    
    /**
     * @return String
     */
    public function getCurrentRegion() : ?String {
    	if(Factions::isSpawnRegion($this)){
    		return "Spawn";
    	}else{
    		return Factions::getRegionName($this) ?? "Wilderness";
    	}
    }

    /**
     * @param String $playerChat
     */
    public function setChat(String $playerChat){
        $this->playerChat = $playerChat;
    }

    /**
     * @return String
     */
    public function getChat() : ?String {
        return $this->playerChat;
    }
    
    /**
     * @return bool
     */
    public function isViewingMap() : bool {
    	return $this->viewingMap;
    }
    
    /**
     * @param bool $viewingMap
     */
    public function setViewingMap(bool $viewingMap){
    	$this->viewingMap = $viewingMap;
    }
    
    /**
     * @return String
     */
    public function getCurrentInvite() : String {
    	return $this->currentInvite;
    }
    
    /**
     * @param String $currentInvite
     */
    public function setCurrentInvite(String $currentInvite){
    	$this->currentInvite = $currentInvite;
    }

    /**
     * @return bool
     */
    public function isInvited() : bool {
        return $this->invitation;
    }

    /**
     * @param bool $invitation
     */
    public function setInvite(bool $invitation){
        $this->invitation = $invitation;
    }
    
    /**
     * @param Handler $inventoryHandler
     */
    public function setInventoryHandler(Handler $inventoryHandler){
    	$this->inventoryHandler[$this->getName()] = $inventoryHandler;
    }
    
    /**
     * @return Handler
     */
    public function getInventoryHandler() : ?Handler {
    	return $this->inventoryHandler[$this->getName()] ?? null;
    }
    
    /**
     * @return void
     */
    public function unsetInventoryHandler() : void {
    	unset($this->inventoryHandler[$this->getName()]);
    }
    
    /**
     * @return bool
     */
    public function isInventoryHandler() : bool {
    	if(isset($this->inventoryHandler[$this->getName()])){
    		return true;
    	}else{
    		return false;
    	}
    	return false;
    }
    
    /**
     * @return Int
     */
    public function getBalance() : Int {
    	return YamlProvider::getMoney($this->getName());
    }
    
    /**
     * @param Int $balance
     */
    public function setBalance(Int $balance){
    	YamlProvider::setMoney($this->getName(), $balance);
    }

    /**
     * @param Int $balance
     */
    public function reduceBalance(Int $balance){
        YamlProvider::reduceMoney($this->getName(), $balance);
    }

    /**
     * @param Int $balance
     */
    public function addBalance(Int $balance){
        YamlProvider::addMoney($this->getName(), $balance);
    }
    
    /**
     * @return void
     */
    public function addKills() : void {
    	YamlProvider::setKills($this->getName(), YamlProvider::getKills($this->getName()) + 1);
    }
    
    /**
     * @return Int|null
     */
    public function getKills() : ?Int {
    	return YamlProvider::getKills($this->getName());
    }

    /**
     * @param String $kitName
     */
    public function getKitTime(String $kitName){
        return YamlProvider::getKitTime($this->getName(), $kitName);
    }

    /**
     * @param String $kitName
     */
    public function resetKitTime(String $kitName){
        YamlProvider::reset($this->getName(), $kitName, time() + (4 * 3600));
    }

    /**
     * @return Int
     */
    public function getBrewerTime(){
        return YamlProvider::getBrewerTime($this->getName());
    }

    /**
     * @return void
     */
    public function resetBrewerTime() : void {
        YamlProvider::reset($this->getName(), "brewer_time", time() + (4 * 3600));
    }

    /**
     * @return Int
     */
    public function getReclaimTime(){
        return YamlProvider::getReclaimTime($this->getName());
    }

    /**
     * @return void
     */
    public function resetReclaimTime() : void {
        YamlProvider::reset($this->getName(), "reclaim_time", time() + (1 * 86400));
    }
    
    /**
     * @return bool
     */
    public function isBardClass() : bool {
    	if(!$this->isOnline()) return false;
		if($this->getArmorInventory()->getHelmet()->getId() === ItemIds::GOLD_HELMET && $this->getArmorInventory()->getChestplate()->getId() === ItemIds::GOLD_CHESTPLATE && $this->getArmorInventory()->getLeggings()->getId() === ItemIds::GOLD_LEGGINGS && $this->getArmorInventory()->getBoots()->getId() === ItemIds::GOLD_BOOTS){
			return true;
		}else{
			return false;
		}
		return false;
    }
    
    /**
     * @return bool
     */
    public function isArcherClass() : bool {
    	if(!$this->isOnline()) return false;
		if($this->getArmorInventory()->getHelmet()->getId() === ItemIds::LEATHER_HELMET && $this->getArmorInventory()->getChestplate()->getId() === ItemIds::LEATHER_CHESTPLATE && $this->getArmorInventory()->getLeggings()->getId() === ItemIds::LEATHER_LEGGINGS && $this->getArmorInventory()->getBoots()->getId() === ItemIds::LEATHER_BOOTS){
			return true;
		}else{
			return false;
		}
		return false;
    }
    
    /**
     * @return bool
     */
    public function isMinerClass() : bool {
    	if(!$this->isOnline()) return false;
		if($this->getArmorInventory()->getHelmet()->getId() === ItemIds::IRON_HELMET && $this->getArmorInventory()->getChestplate()->getId() === ItemIds::IRON_CHESTPLATE && $this->getArmorInventory()->getLeggings()->getId() === ItemIds::IRON_LEGGINGS && $this->getArmorInventory()->getBoots()->getId() === ItemIds::IRON_BOOTS){
			return true;
		}else{
			return false;
		}
		return false;
    }
    
    /**
     * @return bool
     */
    public function isRogueClass() : bool {
    	if(!$this->isOnline()) return false;
		if($this->getArmorInventory()->getHelmet()->getId() === ItemIds::CHAINMAIL_HELMET && $this->getArmorInventory()->getChestplate()->getId() === ItemIds::CHAINMAIL_CHESTPLATE && $this->getArmorInventory()->getLeggings()->getId() === ItemIds::CHAINMAIL_LEGGINGS && $this->getArmorInventory()->getBoots()->getId() === ItemIds::CHAINMAIL_BOOTS){
			return true;
		}else{
			return false;
		}
		return false;
    }
    
    /**
     * @return void
     */
    public function checkClass() : void {
        if($this->isBardClass()){
            //TODO:
            if(!isset($this->playerClass[$this->getName()]["Bard"])){
                $this->playerClass[$this->getName()]["Bard"] = $this;
            }
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 240, 1));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 240, 1));
        }elseif($this->isArcherClass()){
        	//TODO:
        	if(!isset($this->playerClass[$this->getName()]["Archer"])){
                $this->playerClass[$this->getName()]["Archer"] = $this;
            }
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 240, 1));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 240, 1));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 240, 2));
        }elseif($this->isMinerClass()){
        	//TODO:
        	if(!isset($this->playerClass[$this->getName()]["Miner"])){
                $this->playerClass[$this->getName()]["Miner"] = $this;
            }
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 240, 1));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE), 240, 2));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 240, 1));
            if($this->getY() < 40){
                //TODO:
                $this->addEffect(new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 240, 1));
            }}elseif($this->isRogueClass()){
        	//TODO:
        	if(!isset($this->playerClass[$this->getName()]["Rogue"])){
                $this->playerClass[$this->getName()]["Rogue"] = $this;
            }
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 240, 1));
            $this->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 240, 1));
        }else{
        	if(isset($this->playerClass[$this->getName()]["Bard"])){
        		$this->removeEffect(Effect::SPEED);
                $this->removeEffect(Effect::REGENERATION);
        		unset($this->playerClass[$this->getName()]["Bard"]);
        	}
        	if(isset($this->playerClass[$this->getName()]["Archer"])){
        		$this->removeEffect(Effect::SPEED);
                $this->removeEffect(Effect::REGENERATION);
                $this->removeEffect(Effect::FIRE_RESISTANCE);
        		unset($this->playerClass[$this->getName()]["Archer"]);
        	}
        	if(isset($this->playerClass[$this->getName()]["Miner"])){
                $this->removeEffect(Effect::HASTE);
                $this->removeEffect(Effect::NIGHT_VISION);
                $this->removeEffect(Effect::FIRE_RESISTANCE);
                unset($this->playerClass[$this->getName()]["Miner"]);
        	}
        	if(isset($this->playerClass[$this->getName()]["Rogue"])){
        		$this->removeEffect(Effect::SPEED);
                $this->removeEffect(Effect::REGENERATION);
        		unset($this->playerClass[$this->getName()]["Rogue"]);
            }
        }
    }

    /**
	 * @return void
	 */
	public function changeWorld() : void {
		Loader::getInstance()->getServer()->loadLevel(Loader::getDefaultConfig("FactionsConfig")["levelEndName"]);
		if($this->getLevel()->getFolderName() === Loader::getInstance()->getServer()->getDefaultLevel()->getName()){
			Loader::getInstance()->getServer()->loadLevel(Loader::getDefaultConfig("FactionsConfig")["levelEndName"]);
			$this->teleport(Loader::getInstance()->getServer()->getLevelByName(Loader::getDefaultConfig("FactionsConfig")["levelEndName"])->getSafeSpawn());
		}
		elseif($this->getLevel()->getFolderName() === Loader::getDefaultConfig("FactionsConfig")["levelEndName"]){
			$this->teleport(Loader::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
    }
    
    /**
     * @return void
     */
    public function showCoordinates() : void {
        $pk = new GameRulesChangedPacket();
        $pk->gameRules = ["showcoordinates" => [1, true]];
        $this->dataPacket($pk);
    }
}

?>