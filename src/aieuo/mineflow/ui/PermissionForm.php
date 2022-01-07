<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\StringResponseDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function array_pop;
use function in_array;

class PermissionForm {

    public function sendSelectPlayer(Player $player): void {
        Await::f2c(function () use ($player) {
            $players = array_values(array_map(fn(Player $p) => $p->getName(), Server::getInstance()->getOnlinePlayers()));
            $form = new CustomForm("@permission.form.selectPlayer.title");
            $form->addContents([
                new StringResponseDropdown("@permission.form.selectPlayer.dropdown", $players, $player->getName(), result: $target),
                new Input("@form.element.variableDropdown.inputManually", result: $target2)
            ]);

            yield $form->showAwait($player);

            $target = $target2 !== "" ? $target2 : $target;

            $this->sendEditPermission($player, $target);
        });
    }

    public function sendEditPermission(Player $player, string $target, array $messages = []): void {
        Await::f2c(function () use($player, $target, $messages) {
            $config = Main::getInstance()->getPlayerSettings();
            $permissions = $config->getPlayerActionPermissions($target);

            $form = new CustomForm(Language::get("permission.form.edit.title", [$target]));
            foreach (FlowItem::PERMISSION_ALL as $permission) {
                $form->addContent(new Toggle("@permission.".$permission, in_array($permission, $permissions, true)));
            }
            $form->addContent(new CancelToggle(fn() => $this->sendSelectPlayer($player)));

            $form->addMessages($messages);
            $data = yield $form->showAwait($player);

            array_pop($data);
            foreach ($data as $i => $checked) {
                $permission = FlowItem::PERMISSION_ALL[$i];
                $hasPermission = in_array($permission, $permissions, true);
                if ($hasPermission and !$checked) {
                    $config->removePlayerActionPermission($target, $permission);
                } elseif (!$hasPermission and $checked) {
                    $config->addPlayerActionPermission($target, $permission);
                }
            }
            $config->save();

            $this->sendEditPermission($player, $target, ["@form.changed"]);
        });
    }

}