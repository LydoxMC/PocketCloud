<?php

namespace pocketcloud\cloud\command;

use pocketcloud\cloud\command\impl\ConfigureCommand;
use pocketcloud\cloud\command\impl\DebugCommand;
use pocketcloud\cloud\command\impl\ExitCommand;
use pocketcloud\cloud\command\impl\group\GroupCommand;
use pocketcloud\cloud\command\impl\HelpCommand;
use pocketcloud\cloud\command\impl\ListCommand;
use pocketcloud\cloud\command\impl\player\KickCommand;
use pocketcloud\cloud\command\impl\plugin\DisableCommand;
use pocketcloud\cloud\command\impl\plugin\EnableCommand;
use pocketcloud\cloud\command\impl\plugin\PluginsCommand;
use pocketcloud\cloud\command\impl\server\ExecuteCommand;
use pocketcloud\cloud\command\impl\server\SaveCommand;
use pocketcloud\cloud\command\impl\server\StartCommand;
use pocketcloud\cloud\command\impl\server\StopCommand;
use pocketcloud\cloud\command\impl\StatusCommand;
use pocketcloud\cloud\command\impl\template\CreateCommand;
use pocketcloud\cloud\command\impl\template\EditCommand;
use pocketcloud\cloud\command\impl\template\MaintenanceCommand;
use pocketcloud\cloud\command\impl\template\RemoveCommand;
use pocketcloud\cloud\command\impl\VersionCommand;
use pocketcloud\cloud\command\impl\web\WebAccountCommand;
use pocketcloud\cloud\command\sender\ICommandSender;
use pocketcloud\cloud\PocketCloud;
use pocketcloud\cloud\util\promise\Promise;
use pocketcloud\cloud\util\SingletonTrait;
use pocketcloud\cloud\util\tick\Tickable;

final class CommandManager implements Tickable {
    use SingletonTrait;

    /** @var array<Promise> */
    private array $confirmationPromises = [];
    /** @var array<Command> */
    private array $commands = [];

    public function __construct() {
        self::setInstance($this);
        $this->register(new ExitCommand());
        $this->register(new HelpCommand());
        $this->register(new DebugCommand());
        $this->register(new ListCommand());
        $this->register(new VersionCommand());
        $this->register(new ConfigureCommand());
        $this->register(new StatusCommand());

        $this->register(new StartCommand());
        $this->register(new StopCommand());
        $this->register(new ExecuteCommand());
        $this->register(new SaveCommand());

        $this->register(new CreateCommand());
        $this->register(new EditCommand());
        $this->register(new RemoveCommand());
        $this->register(new MaintenanceCommand());

        $this->register(new KickCommand());

        $this->register(new EnableCommand());
        $this->register(new DisableCommand());
        $this->register(new PluginsCommand());

        $this->register(new WebAccountCommand());

        $this->register(new GroupCommand());
    }

    public function tick(int $currentTick): void {
        if (empty($this->confirmationPromises)) return;
        foreach ($this->confirmationPromises as $cmd => $data) {
            [, $expireTick, $promise] = $data;
            if ($expireTick <= PocketCloud::getInstance()->getTick()) {
                $promise->reject();
                unset($this->confirmationPromises[$cmd]);
            }
        }
    }

    public function handleInput(ICommandSender $sender, string $input): bool {
        $args = explode(" ", $input);
        $name = array_shift($args);

        if (count($this->confirmationPromises) > 0) {
            [$name, , $promise, $keywordsAccept, $keywordsDecline] = current($this->confirmationPromises);
            if (in_array(strtolower($input), $keywordsAccept)) {
                $promise->resolve(true);
                unset($this->confirmationPromises[$name]);
                return true;
            } else if (in_array(strtolower($input), $keywordsDecline)) {
                $promise->resolve(false);
                unset($this->confirmationPromises[$name]);
                return true;
            }

            $sender->warn("§cPlease do the confirmation before you enter a new command.");
            return false;
        }

        if (($command = $this->get($name)) === null) return false;

        $command->handle($sender, $name, $args);
        return true;
    }

    public function addConfirmation(Command $command, array $keywordsAccept, array $keywordsDecline, Promise $promise): void {
        $this->confirmationPromises[$command->getName()] = [$command->getName(), PocketCloud::getInstance()->getTick() + (20 * 10), $promise, $keywordsAccept, $keywordsDecline];
    }

    public function register(Command $command): void {
        $this->commands[$command->getName()] = $command;
    }

    public function remove(Command|string $command): void {
        $command = $command instanceof Command ? $command->getName() : $command;
        if (isset($this->commands[$command])) unset($this->commands[$command]);
    }

    public function get(string $name): ?Command {
        return $this->commands[$name] ?? null;
    }

    public function getAll(): array {
        return $this->commands;
    }
}