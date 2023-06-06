<?php

namespace Models\User;

use BaseModel;
use Models\User\DTO\UserSettingsDTO;

/**
 * @property int $id
 * @property string $name
 * @property string $lastName верно в базе данных называть last_name
 * @property int $from
 * @property int $age
 * @property string $settings [json]
 *
 * @property string $key
 * @property string|UserSettingsDTO $settingsDTO
 *
 */
class User extends BaseModel
{
    public const limit = 0; // верно LIMIT
    public static function getTableName(): string
    {
        return 'Users'; // верное называние таблицы 'users'
    }

    /**
     * Возвращает список пользователей старше заданного возраста.
     * @param int $ageFrom
     * @return array
     */
    public static function getUsers(int $ageFrom): array // Неочевидное название, поскольку жесткая привязка к одному
        // условию. Либо в аттрибутах должен быть массив условий, либо имя метода должно быть другим.
        // К примеру getOlderUsers(int $ageFrom).
    {
        $users = User::find()
            ->select([ // тут изначально была ошибка, поскольку из БД запрашивалось меньше полей, чем использовалось
                'id',
                'name',
                'lastName',
                'from',
                'age',
                'key',
                'settings',
            ])
            ->andWhere(['age', '>', $ageFrom]);

        if (static::limit > 0) {
            $users->limit(static::limit);
        }
        return $users->all(true); // Приведение к массиву сделано, поскольку неизвестно сколько и где использований
        // этого метода в других (не видимых) частях кода. Верно возвращать массив объектов.
        // Перемещение класса в другую папку сделано для наглядности, в реальном проекте,
        // это также необходимо делать в рамках отдельной задачи по рефакторингу

    }

    /**
     * Возвращает пользователя по имени.
     * @param string $name
     * @return array
     */
    public static function user(string $name): array // Не верное название. getUserByName(string $name) более верно
    {
        return User::find()
            ->select([
                'id',
                'name',
                'lastName',
                'from',
                'age',
            ])
            ->andWhere(['name' => $name])
            ->one(true);
    }

    /**
     * Добавляет пользователя в базу данных.
     * @param string $name
     * @param string $lastName
     * @param int $age
     * @return string
     */
    public static function add(string $name, string $lastName, int $age): string // возвращается string|false, не учитывается булево.
    {
        return User::insert(compact('name', 'lastName', 'age'));
    }

    /** Обработка полученных из базы данных значений */
    public function processRowData()
    {
        $settings = json_decode($this->settings, true);
        $this->settingsDTO = new UserSettingsDTO();
        $this->settingsDTO->key = $settings['key'];
        $this->key = $settings['key']; // избыточно, но без рефакторинга других (невидимых в рамках задачи) частей кода,
        // нельзя менять возвращаемые значения
    }
}