<?php

namespace pocketcloud\cloud\network\packet\impl\type;

use pocketcloud\cloud\util\enum\EnumTrait;

/**
 * @method static DisconnectReason CLOUD_SHUTDOWN()
 * @method static DisconnectReason SERVER_SHUTDOWN()
 */
final class DisconnectReason {
    use EnumTrait;

    protected static function init(): void {
        self::register("cloud_shutdown", new DisconnectReason("CLOUD_SHUTDOWN"));
        self::register("server_shutdown", new DisconnectReason("SERVER_SHUTDOWN"));
    }

    public static function getReasonByName(string $name): ?DisconnectReason {
        self::check();
        return self::$members[strtoupper($name)] ?? null;
    }

    public function __construct(private readonly string $name) {}

    public function getName(): string {
        return $this->name;
    }
}