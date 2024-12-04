<?php

namespace pocketcloud\cloud\player;

use pocketcloud\cloud\event\impl\player\PlayerConnectEvent;
use pocketcloud\cloud\event\impl\player\PlayerDisconnectEvent;
use pocketcloud\cloud\network\packet\impl\normal\PlayerSyncPacket;
use pocketcloud\cloud\terminal\log\CloudLogger;
use pocketcloud\cloud\util\SingletonTrait;

final class CloudPlayerManager {
    use SingletonTrait;

    /** @var array<CloudPlayer> */
    private array $players = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function add(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));

        $this->players[$player->getName()] = $player;
        PlayerSyncPacket::create($player, false)->broadcastPacket();

        (new PlayerConnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();
    }

    public function remove(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));

        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);
        (new PlayerDisconnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();

        $player->setCurrentServer(null);
        $player->setCurrentProxy(null);

        PlayerSyncPacket::create($player, true)->broadcastPacket();
    }

    public function get(string $name): ?CloudPlayer {
        return $this->players[$name] ?? null;
    }

    public function getByUniqueId(string $uniqueId): ?CloudPlayer {
        return array_filter($this->players, fn(CloudPlayer $player) => $player->getUniqueId() == $uniqueId)[0] ?? null;
    }

    public function getByXboxId(string $xboxUserId): ?CloudPlayer {
        return array_filter($this->players, fn(CloudPlayer $player) => $player->getXboxUserId() == $xboxUserId)[0] ?? null;
    }

    public function getAll(): array {
        return $this->players;
    }
}