<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\ServerObjectVariable;
use pocketmine\block\BaseSign;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\Server;

class DefaultVariables {

    public static function getServerVariables(): array {
        $server = Server::getInstance();
        $onlines = array_map(fn(Player $player) => new StringVariable($player->getName()), array_values($server->getOnlinePlayers()));
        return [
            "server_name" => new StringVariable($server->getName()),
            "microtime" => new NumberVariable(microtime(true)),
            "time" => new StringVariable(date("H:i:s")),
            "date" => new StringVariable(date("m/d")),
            "default_world" => new StringVariable($server->getWorldManager()->getDefaultWorld()?->getFolderName() ?? ""),
            "onlines" => new ListVariable($onlines),
            "ops" => new ListVariable(array_map(fn(string $name) => new StringVariable($name), $server->getOps()->getAll(true))),
            "server" => new ServerObjectVariable($server),
        ];
    }

    public static function getEntityVariables(Entity $target, string $name = "target"): array {
        return [$name => EntityObjectVariable::fromObject($target)];
    }

    public static function getPlayerVariables(Player $target, string $name = "target"): array {
        return [$name => new PlayerObjectVariable($target, $target->getName())];
    }

    public static function getBlockVariables(Block $block, string $name = "block"): array {
        $variables = [$name => new BlockObjectVariable($block, $block->getId().":".$block->getMeta())];
        if ($block instanceof BaseSign) {
            $variables["sign_lines"] = new ListVariable(array_map(fn(string $text) => new StringVariable($text), $block->getText()->getLines()));
        }
        return $variables;
    }

    public static function getCommandVariables(string $command): array {
        $commands = explode(" ", $command);
        return [
            "cmd" => new StringVariable(array_shift($commands)),
            "args" => new ListVariable(array_map(fn(string $cmd) => new StringVariable($cmd), $commands)),
        ];
    }
}