<?php

namespace aieuo\mineflow\utils;

use pocketmine\Player;

class Session {

    private static array $sessions = [];

    public static function existsSession(Player $player): bool {
        return isset(self::$sessions[$player->getName()]);
    }

    public static function getSession(Player $player): Session {
        if (!self::existsSession($player)) self::createSession($player);
        return self::$sessions[$player->getName()];
    }

    public static function createSession(Player $player): void {
        self::$sessions[$player->getName()] = new Session();
    }

    public static function destroySession(Player $player): void {
        unset(self::$sessions[$player->getName()]);
    }

////////////////////////////////////////////////////////////////////////

    private array $data = [];

    public function exists(string $key): bool {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {
        if (!isset($this->data[$key])) return $default;
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return self
     */
    public function set(string $key, $data): self {
        $this->data[$key] = $data;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function push(string $key, $value): bool {
        $data = $this->get($key);
        if ($data === null) $data = [];
        if (!is_array($data)) return false;

        $data[] = $value;
        $this->set($key, $data);
        return true;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pop(string $key) {
        $data = $this->get($key);
        if (!is_array($data)) return null;

        $value = array_pop($data);
        $this->set($key, $data);
        return $value;
    }

    public function remove(string $key): self {
        unset($this->data[$key]);
        return $this;
    }

    public function removeAll(): self {
        $this->data = [];
        return $this;
    }
}