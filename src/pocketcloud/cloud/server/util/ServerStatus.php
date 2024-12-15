<?php

namespace pocketcloud\cloud\server\util;

use pocketcloud\cloud\util\enum\EnumTrait;

/**
 * @method static ServerStatus STARTING()
 * @method static ServerStatus ONLINE()
 * @method static ServerStatus FULL()
 * @method static ServerStatus IN_GAME()
 * @method static ServerStatus STOPPING()
 * @method static ServerStatus OFFLINE()
 */
final class ServerStatus {
    use EnumTrait;

    protected static function init(): void {
        self::register("starting", new ServerStatus("STARTING", "§2STARTING"));
        self::register("online", new ServerStatus("ONLINE", "§aONLINE"));
        self::register("full", new ServerStatus("FULL", "§eFULL"));
        self::register("in_game", new ServerStatus("IN_GAME", "§6INGAME"));
        self::register("stopping", new ServerStatus("STOPPING", "§4STOPPING"));
        self::register("offline", new ServerStatus("OFFLINE", "§cOFFLINE"));
    }

    public function __construct(
        private readonly string $name,
        private readonly string $display
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getDisplay(): string {
        return $this->display;
    }
}