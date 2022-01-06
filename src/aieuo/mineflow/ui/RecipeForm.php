<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\NumberInput;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\ui\trigger\BaseTriggerForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Utils;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function count;
use function explode;
use function implode;

class RecipeForm {

    public function sendMenu(Player $player, array $messages = []): void {
        (new ListForm("@mineflow.recipe"))
            ->addButtons([
                new Button("@form.back", fn() => (new HomeForm)->sendMenu($player)),
                new Button("@form.add", fn() => $this->sendAddRecipe($player)),
                new Button("@form.edit", fn() => $this->sendSelectRecipe($player)),
                new Button("@form.recipe.menu.recipeList", fn() => $this->sendRecipeList($player)),
                new Button("@mineflow.export", function () use($player) {
                    (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title", function(Recipe $recipe) use($player) {
                        (new ExportForm())->sendRecipeListByRecipe($player, $recipe);
                    });
                }),
                new Button("@mineflow.import", fn() => (new ImportForm)->sendSelectImportFile($player)),
            ])->addMessages($messages)->show($player);
    }

    public function sendAddRecipe(Player $player, array $default = []): void {
        Await::f2c(function () use ($player, $default) {
            $manager = Main::getRecipeManager();
            $defaultName = $manager->getNotDuplicatedName("recipe");

            $form = new CustomForm("@form.recipe.addRecipe.title");
            $form->setContents([
                new Input("@form.recipe.recipeName", $defaultName, $default[0] ?? "", result: $name),
                new Input("@form.recipe.groupName", default: $default[1] ?? "", result: $group),
                new CancelToggle(fn() => $this->sendMenu($player)),
            ]);

            yield $form->showAwait($player);

            $name = $name === "" ? $defaultName : $name;

            while (true) {
                if (!Utils::isValidFileName($name)) {
                    yield $form->errorAwait([["@form.recipe.invalidName", 0]]);
                    continue;
                }
                if (!Utils::isValidFileName($group)) {
                    yield $form->errorAwait([["@form.recipe.invalidName", 1]]);
                    continue;
                }

                if ($manager->exists($name, $group)) {
                    $nonDuplicateName = $manager->getNotDuplicatedName($name, $group);

                    $renameForm = new ModalForm("@form.home.rename.title");
                    $renameForm->setContent(Language::get("form.home.rename.content", [$name, $nonDuplicateName]));
                    if (yield $renameForm->showAwait($player)) {
                        $name = $nonDuplicateName;
                    }
                }

                $recipe = new Recipe($name, $group, $player->getName(), Main::getPluginVersion());
                if (file_exists($recipe->getFileName($manager->getSaveDir()))) {
                    yield $form->errorAwait([[Language::get("form.recipe.exists", [$name]), 0]]);
                    continue;
                }
                break;
            }

            $manager->add($recipe);
            Session::getSession($player)->set("recipe_menu_prev", function () use ($player, $recipe) {
                $this->sendRecipeList($player, $recipe->getGroup());
            });
            $this->sendRecipeMenu($player, $recipe);
        });
    }

    public function sendSelectRecipe(Player $player, array $default = []): void {
        (new MineflowForm)->selectRecipe($player, "@form.recipe.select.title",
            function (Recipe $recipe) use($player) {
                Session::getSession($player)->set("recipe_menu_prev", function() use($player, $recipe) {
                    $this->sendRecipeList($player, $recipe->getGroup());
                });
                $this->sendRecipeMenu($player, $recipe);
            },
            fn() => $this->sendMenu($player),
            $default
        );
    }

    public function sendRecipeList(Player $player, string $path = "", array $messages = []): void {
        $manager = Main::getRecipeManager();
        $recipeGroups = $manager->getByPath($path);
        $buttons = [
            new Button("@form.back"),
            new Button("@recipe.add"),
        ];
        $recipes = $recipeGroups[$path] ?? [];
        foreach ($recipes as $recipe) {
            $buttons[] = new Button($recipe->getName());
        }
        unset($recipeGroups[$path]);

        $groups = [];
        foreach ($recipeGroups as $group => $value) {
            if ($path !== "") {
                $name = explode("/", str_replace($path."/", "", $group))[0];
            } else {
                $name = explode("/", $group)[0];
            }

            if (!isset($groups[$name])) {
                $buttons[] = new Button("[$name]");
                $groups[$name] = $path !== "" ? $path."/".$name : $name;
            }
        }
        if ($path !== "") $buttons[] = new Button("@recipe.group.delete");

        $recipeGroups = array_merge($recipes, array_values($groups));

        (new ListForm("@form.recipe.recipeList.title"))
            ->addButtons($buttons)
            ->onReceive(function (Player $player, int $data, string $path, array $recipes) {
                if ($data === 0) {
                    if ($path === "") {
                        $this->sendMenu($player);
                        return;
                    }
                    $paths = explode("/", $path);
                    array_pop($paths);
                    $this->sendRecipeList($player, implode("/", $paths));
                    return;
                }

                if ($data === 1) {
                    $this->sendAddRecipe($player, ["", $path]);
                    return;
                }

                $data -= 2;
                $recipes = array_values($recipes);
                if (isset($recipes[$data])) {
                    $recipe = $recipes[$data];
                    if ($recipe instanceof Recipe) {
                        Session::getSession($player)->set("recipe_menu_prev", function() use($player, $path) {
                            $this->sendRecipeList($player, $path);
                        });
                        $this->sendRecipeMenu($player, $recipe);
                        return;
                    }
                    $this->sendRecipeList($player, $recipe);
                    return;
                }

                $this->confirmDeleteRecipeGroup($player, $path);
            })->addMessages($messages)->addArgs($path, $recipeGroups)->show($player);
    }

    public function sendRecipeMenu(Player $player, Recipe $recipe, array $messages = []): void {
        $detail = trim($recipe->getDetail());
        (new ListForm(Language::get("form.recipe.recipeMenu.title", [$recipe->getPathname()])))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@action.edit"),
                new Button("@form.recipe.recipeMenu.changeName"),
                new Button("@form.recipe.recipeMenu.execute"),
                new Button("@form.recipe.recipeMenu.setTrigger"),
                new Button("@form.recipe.args.return.set"),
                new Button("@form.recipe.changeTarget"),
                new Button("@form.recipe.recipeMenu.save"),
                new Button("@mineflow.export"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, int $data, Recipe $recipe) {
                switch ($data) {
                    case 0:
                        $prev = Session::getSession($player)->get("recipe_menu_prev");
                        is_callable($prev) ? $prev($player) : $this->sendMenu($player);
                        break;
                    case 1:
                        Session::getSession($player)->set("parents", []);
                        (new FlowItemContainerForm)->sendActionList($player, $recipe, FlowItemContainer::ACTION);
                        break;
                    case 2:
                        $this->sendChangeName($player, $recipe);
                        break;
                    case 3:
                        $recipe->executeAllTargets($player);
                        break;
                    case 4:
                        $this->sendTriggerList($player, $recipe);
                        break;
                    case 5:
                        (new ListForm("@form.recipe.args.return.set"))
                            ->setButtons([
                                new Button("@form.back"),
                                new Button("@form.recipe.args.set"),
                                new Button("@form.recipe.returnValue.set"),
                            ])->onReceive(function (Player $player, int $data, Recipe $recipe) {
                                switch ($data) {
                                    case 0:
                                        $this->sendRecipeMenu($player, $recipe);
                                        break;
                                    case 1:
                                        $this->sendSetArgs($player, $recipe);
                                        break;
                                    case 2:
                                        $this->sendSetReturns($player, $recipe);
                                        break;
                                }
                            })->addArgs($recipe)->show($player);
                        break;
                    case 6:
                        $this->sendChangeTarget($player, $recipe);
                        break;
                    case 7:
                        $recipe->save(Main::getRecipeManager()->getSaveDir());
                        $this->sendRecipeMenu($player, $recipe, ["@form.recipe.recipeMenu.save.success"]);
                        break;
                    case 8:
                        (new ExportForm)->sendRecipeListByRecipe($player, $recipe);
                        break;
                    case 9:
                        (new ModalForm(Language::get("form.recipe.delete.title", [$recipe->getName()])))
                            ->setContent(Language::get("form.delete.confirm", [$recipe->getName()]))
                            ->onYes(function() use ($player, $recipe) {
                                $manager = Main::getRecipeManager();
                                $recipe->removeTriggerAll();
                                $manager->remove($recipe->getName(), $recipe->getGroup());
                                $this->sendRecipeList($player, $recipe->getGroup(), ["@form.deleted"]);
                            })->onNo(function() use($player, $recipe) {
                                $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]);
                            })->show($player);
                        break;
                }
            })->addArgs($recipe)->addMessages($messages)->show($player);
    }

    public function sendChangeName(Player $player, Recipe $recipe): void {
        Await::f2c(function () use($player, $recipe) {
            $manager = Main::getRecipeManager();
            $form = new CustomForm(Language::get("form.recipe.changeName.title", [$recipe->getName()]));
            $form->setContents([
                new Label("@form.recipe.changeName.description"),
                new Input("@form.recipe.changeName.newName", default: $recipe->getName(), required: true, result: $newName),
                new CancelToggle(fn() => $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]))
            ]);

            yield $form->showAwait($player);

            while (true) {
                if ($manager->exists($newName, $recipe->getGroup())) {
                    $nonDuplicateName = $manager->getNotDuplicatedName($newName, $recipe->getGroup());

                    $confirmRenameForm = new ModalForm("@form.home.rename.title");
                    $confirmRenameForm->setContent(Language::get("form.home.rename.content", [$newName, $nonDuplicateName]));
                    $result = yield $confirmRenameForm->showAwait($player);
                    if (!$result) {
                        yield $form->errorAwait([[Language::get("form.recipe.exists", [$newName]), 1]]);
                        continue;
                    }

                    $newName = $nonDuplicateName;
                }
                break;
            }

            $manager->rename($recipe->getName(), $newName, $recipe->getGroup());
            $this->sendRecipeMenu($player, $recipe, ["@form.recipe.changeName.success"]);
        });
    }

    public function sendTriggerList(Player $player, Recipe $recipe, array $messages = []): void {
        $triggers = $recipe->getTriggers();
        (new ListForm(Language::get("form.recipe.triggerList.title", [$recipe->getName()])))
            ->addButton(new Button("@form.back", fn() => $this->sendRecipeMenu($player, $recipe)))
            ->addButton(new Button("@form.add", fn() => (new BaseTriggerForm)->sendSelectTriggerType($player, $recipe)))
            ->addButtonsEach($triggers, function (Trigger $trigger) use($player, $recipe) {
                return new Button((string)$trigger, fn() => (new BaseTriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger));
            })->addMessages($messages)->show($player);
    }

    public function sendSetArgs(Player $player, Recipe $recipe, array $messages = []): void {
        Await::f2c(function () use($player, $recipe, $messages) {
            $form = new CustomForm("@form.recipe.args.set");
            $form->addContent(new CancelToggle(fn() => $this->sendRecipeMenu($player, $recipe), "@form.exit"));

            foreach ($recipe->getArguments() as $i => $argument) {
                $form->addContent(new Input(Language::get("form.recipe.args", [$i]), default: $argument));
            }
            $form->addContent(new Input("@form.recipe.args.add"));

            $form->addMessages($messages);
            $data = yield $form->showAwait($player);

            $arguments = [];
            for ($i = 1, $iMax = count($data); $i < $iMax; $i++) {
                if ($data[$i] !== "") $arguments[] = $data[$i];
            }
            $recipe->setArguments($arguments);
            $this->sendSetArgs($player, $recipe, ["@form.changed"]);
        });
    }

    public function sendSetReturns(Player $player, Recipe $recipe, array $messages = []): void {
        Await::f2c(function () use($player, $recipe, $messages) {
            $form = new CustomForm("@form.recipe.returnValue.set");
            $form->addContent(new CancelToggle(fn() => $this->sendRecipeMenu($player, $recipe), "@form.exit"));

            foreach ($recipe->getReturnValues() as $i => $value) {
                $form->addContent(new Input(Language::get("form.recipe.returnValue", [$i]), default: $value));
            }
            $form->addContent(new Input("@form.recipe.returnValue.add"));

            $form->addMessages($messages);
            $data = yield $form->showAwait($player);

            $returnValues = [];
            for ($i = 1, $iMax = count($data); $i < $iMax; $i++) {
                if ($data[$i] !== "") $returnValues[] = $data[$i];
            }
            $recipe->setReturnValues($returnValues);
            $this->sendSetReturns($player, $recipe, ["@form.changed"]);
        });
    }

    public function sendChangeTarget(Player $player, Recipe $recipe): void {
        Await::f2c(function () use($player, $recipe) {
            $default1 = $default[1] ?? ($recipe->getTargetType() === Recipe::TARGET_SPECIFIED ? implode(",", $recipe->getTargetOptions()["specified"]) : "");
            $default2 = $default[2] ?? ($recipe->getTargetType() === Recipe::TARGET_RANDOM ? (string)$recipe->getTargetOptions()["random"] : "");
            $form = new CustomForm(Language::get("form.recipe.changeTarget.title", [$recipe->getName()]));
            $form->setContents([
                new Dropdown("@form.recipe.changeTarget.type", [
                    Language::get("form.recipe.target.none"),
                    Language::get("form.recipe.target.default"),
                    Language::get("form.recipe.target.specified"),
                    Language::get("form.recipe.target.onWorld"),
                    Language::get("form.recipe.target.all"),
                    Language::get("form.recipe.target.random"),
                ], $default[0] ?? $recipe->getTargetType(), result: $targetType),
                new Input("@form.recipe.changeTarget.name", "@form.recipe.changeTarget.name.placeholder", $default1, result: $targetPlayers),
                new NumberInput("@form.recipe.changeTarget.random", "@form.recipe.changeTarget.random.placeholder", $default2, min: 1, result: $randomNumber),
                new CancelToggle(fn() => $this->sendRecipeMenu($player, $recipe, ["@form.cancelled"]))
            ]);
            $data = yield $form->showAwait($player);

            while (true) {
                if ($targetType === Recipe::TARGET_SPECIFIED and $targetPlayers === "") {
                    yield $form->insufficientAwait(1);
                    continue;
                }
                if ($targetType === Recipe::TARGET_RANDOM and $data[2] === "") {
                    yield $form->insufficientAwait(2);
                    continue;
                }
                break;
            }

            switch ($targetType) {
                case Recipe::TARGET_SPECIFIED:
                    $recipe->setTargetSetting((int)$targetType, ["specified" => explode(",", $targetPlayers)]);
                    break;
                case Recipe::TARGET_RANDOM:
                    $recipe->setTargetSetting((int)$targetType, ["random" => empty($randomNumber) ? 1 : (int)$randomNumber]);
                    break;
                default:
                    $recipe->setTargetSetting((int)$targetType);
                    break;
            }
            $this->sendRecipeMenu($player, $recipe, ["@form.changed"]);
        });
    }

    public function confirmDeleteRecipeGroup(Player $player, string $path): void {
        $recipes = Main::getRecipeManager()->getByPath($path);
        $count = count($recipes) - 1 + count($recipes[$path] ?? []);
        if ($count >= 1) {
            $this->sendRecipeList($player, $path, ["@recipe.group.delete.not.empty"]);
            return;
        }
        (new ModalForm(Language::get("form.recipe.delete.title", [$path])))
            ->setContent(Language::get("form.delete.confirm", [$path, count($recipes)]))
            ->onYes(function() use ($player, $path) {
                $manager = Main::getRecipeManager();
                $result = $manager->deleteGroup($path);
                $this->sendRecipeList($player, $manager->getParentPath($path), [$result ? "@form.deleted" : "@recipe.group.delete.not.empty"]);
            })->onNo(function() use($player, $path) {
                $this->sendRecipeList($player, $path, ["@form.cancelled"]);
            })->show($player);
    }
}