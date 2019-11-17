<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\ui\HomeForm;
use aieuo\mineflow\trigger\TriggerManager;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\FormAPI\element\Dropdown;
use aieuo\mineflow\FormAPI\element\Button;

class CommandForm {

    public function sendMenu(Player $player, array $messages = []) {
        (new ListForm("@form.command.menu.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.add"),
                new Button("@form.edit"),
                new Button("@form.command.menu.commandList"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        $this->sendAddCommand($player);
                        break;
                    case 1:
                        $this->sendSelectCommand($player);
                        break;
                    case 2:
                        $this->sendCommandList($player);
                        break;
                    default:
                        (new HomeForm)->sendMenu($player);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function sendAddCommand(Player $player, array $defaults = [], array $errors = []) {
        (new CustomForm("@form.command.addCommand.title"))
            ->setContents([
                new Input("@form.command.menu.title", "@trigger.command.select.placeholder", $defaults[0] ?? ""),
                new Input("@form.command.description", "", $defaults[1] ?? ""),
                new Dropdown("@form.command.permission", [
                    Language::get("form.command.addCommand.permission.op"),
                    Language::get("form.command.addCommand.permission.true"),
                ]),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data) {
                if ($data === null) return;
                if ($data[3]) {
                    $this->sendMenu($player);
                    return;
                }

                $manager = Main::getInstance()->getCommandManager();
                $original = $manager->getOriginCommand($data[0]);
                if (!$manager->isSubcommand($data[0]) and $manager->existsCommand($original)) {
                    $this->sendAddCommand($player, $data[0], ["@form.command.alreadyExists", 0]);
                    return;
                }
                if ($manager->isRegisterd($original)) {
                    $this->sendAddCommand($player, $data[0], ["@form.command.alreadyUsed", 0]);
                    return;
                }
                $permission = ["mineflow.customcommand.op", "mineflow.customcommand.true"][$data[2]];

                $manager->addCommand($data[0], $permission, $data[1]);
                $command = $manager->getCommand($original);
                Session::getSession($player)->set("command_menu_prev", [$this, "sendMenu"]);
                $this->sendCommandMenu($player, $command);
            })->addErrors($errors)->show($player);
    }

    public function sendSelectCommand(Player $player, array $defaults = [], array $errors = []) {
        (new CustomForm("@form.command.select.title"))
            ->setContents([
                new Input("@form.command.name", "", $defaults[0] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data) {
                if ($data === null) return;

                if ($data[1]) {
                    $this->sendMenu($player);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSelectCommand($player, $data, [["@form.insufficient", 0]]);
                    return;
                }

                $manager = Main::getInstance()->getCommandManager();
                if (!$manager->existsCommand($manager->getOriginCommand($data[0]))) {
                    $this->sendSelectCommand($player, $data, [["@form.command.notFound", 0]]);
                    return;
                }

                $command = $manager->getCommand($manager->getOriginCommand($data[0]));
                Session::getSession($player)->set("command_menu_prev", [$this, "sendSelectCommand"]);
                $this->sendCommandMenu($player, $command);
            })->addErrors($errors)->show($player);
    }

    public function sendCommandList(Player $player) {
        $manager = Main::getInstance()->getCommandManager();
        $commands = $manager->getCommandAll();
        $buttons = [new Button("@form.back")];
        foreach ($commands as $command) {
            $buttons[] = new Button("/".$command["command"]);
        }

        (new ListForm("@form.command.commandList.title"))
            ->setContent("@form.selectButton")
            ->addButtons($buttons)
            ->onRecive(function (Player $player, ?int $data, array $commands) {
                if ($data === null) return;

                if ($data === 0) {
                    $this->sendMenu($player);
                    return;
                }
                $data --;

                $command = $commands[$data];
                Session::getSession($player)->set("command_menu_prev", [$this, "sendCommandList"]);
                $this->sendCommandMenu($player, $command);
            })->addArgs(array_values($commands))->show($player);
    }

    public function sendCommandMenu(Player $player, array $command) {
        $permission = str_replace("mineflow.customcommand", "@form.command.addCommand.permission", $command["permission"]);
        (new ListForm("/".$command["command"]))
            ->setContent("/".$command["command"]."\n権限: ".$permission."\n説明: ".$command["description"])
            ->addButtons([
                new Button("@form.command.commandMenu.editDescription"),
                new Button("@form.command.commandMenu.editPermission"),
                new Button("@form.delete"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data, array $command) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        $this->changeDescription($player, $command);
                        break;
                    case 1:
                        $this->changePermission($player, $command);
                        break;
                    case 2:
                        $this->sendConfirmDelete($player, $command);
                        break;
                    default:
                        $prev = Session::getSession($player)->get("command_menu_prev");
                        if (is_callable($prev)) call_user_func_array($prev, [$player]);
                        else $this->sendMenu($player);
                        break;
                }
            })->addArgs($command)->show($player);
    }

    public function changeDescription(Player $player, array $command) {
        (new CustomForm(Language::get("form.command.changeDescription.title", ["/".$command["command"]])))
            ->setContents([
                new Input("@form.command.description", "", $command["description"] ?? ""),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data, array $command) {
                if ($data === null) return;

                if ($data[1]) {
                    $this->sendCommandMenu($player, $command);
                    return;
                }

                $manager = Main::getInstance()->getCommandManager();
                $command["description"] = $data[0];
                $manager->updateCommand($command);
                $this->sendCommandMenu($player, $command);
            })->addArgs($command)->show($player);
    }

    public function changePermission(Player $player, array $command) {
        $permissions = ["mineflow.customcommand.op" => 0, "mineflow.customcommand.true" => 1];
        (new CustomForm(Language::get("form.command.changePermission.title", ["/".$command["command"]])))
            ->setContents([
                new Dropdown("@form.command.permission", [
                    Language::get("form.command.addCommand.permission.op"),
                    Language::get("form.command.addCommand.permission.true"),
                ], $permissions[$command["permission"]]),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data, array $command) {
                if ($data === null) return;

                if ($data[1]) {
                    $this->sendCommandMenu($player, $command);
                    return;
                }

                $manager = Main::getInstance()->getCommandManager();
                $command["permission"] = ["mineflow.customcommand.op", "mineflow.customcommand.true"][$data[0]];
                $manager->updateCommand($command);
                $this->sendCommandMenu($player, $command);
            })->addArgs($command)->show($player);
    }

    public function sendConfirmDelete(Player $player, array $command) {
        (new ModalForm(Language::get("form.command.delete.title", ["/".$command["command"]])))
            ->setContent(Language::get("form.delete.confirm", ["/".$command["command"]]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, array $command) {
                if ($data === null) return;

                if ($data) {
                    $commandManager = Main::getInstance()->getCommandManager();
                    $recipeManager = Main::getInstance()->getRecipeManager();

                    foreach ($command["recipes"] as $recipe => $cmds) {
                        $recipe = $recipeManager->get($recipe);
                        if ($recipe === null) continue;

                        foreach ($cmds as $cmd) {
                            $recipe->removeTrigger([TriggerManager::TRIGGER_COMMAND, $cmd]);
                        }
                    }
                    $commandManager->removeCommand($command["command"]);
                    $this->sendMenu($player, ["@form.delete.success"]);
                } else {
                    $this->sendCommandMenu($player, $command, ["@form.cancelled"]);
                }
            })->addArgs($command)->show($player);
    }
}