<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Server;

class SendMessageToOp extends TypeMessage {

    protected string $id = self::SEND_MESSAGE_TO_OP;

    protected string $name = "action.sendMessageToOp.name";
    protected string $detail = "action.sendMessageToOp.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $message = $source->replaceVariables($this->getMessage());
        $players = Server::getInstance()->getOnlinePlayers();
        foreach ($players as $player) {
            if ($player->isOp()) {
                $player->sendMessage($message);
            }
        }
        yield FlowItemExecutor::CONTINUE;
    }
}