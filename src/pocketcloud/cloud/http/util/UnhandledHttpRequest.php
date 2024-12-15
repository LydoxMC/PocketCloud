<?php

namespace pocketcloud\cloud\http\util;

use pmmp\thread\ThreadSafe;
use pocketcloud\cloud\http\network\SocketClient;

final class UnhandledHttpRequest extends ThreadSafe {

    public function __construct(
        private readonly string $buffer,
        private readonly SocketClient $client
    ) {}

    public function getBuffer(): string {
        return $this->buffer;
    }

    public function getClient(): SocketClient {
        return $this->client;
    }
}