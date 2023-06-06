<?php


namespace Models\User;

class UserManager extends User
{
    public const limit = 10;


    /**
     * Возвращает пользователей по списку имен.
     * @return array
     */
    public static function getByNames(): array
    {
        $users = [];
        foreach ($_GET['names'] as $name) { // Модель не должна работать с request. В аттрибуты необходимо передавать
            // массив значений $names. А обработчик запроса перенести в контроллер. Либо весь метод в контроллер.
            // Кроме того, использовать GET параметры для списка имен - не верное решение. Список может быть очень
            // большим. В GET параметрах можно передавать короткие значения (фильтры, например, is_active_users=1).
            // Для массива данных необходимо
            // использовать POST запрос. Если речь идет о безопасности, то нужна авторизация, контроль прав доступа,
            // для api можно добавить шифрование.
            $users[] = User::user($name);
        }

        return $users;
    }

    /**
     * Добавляет пользователей в базу данных.
     * @param $users
     * @return array
     */
    public function users($users): array // Неочевидное название, по логике должно быть что-то вроде addMany()
    {
        $ids = [];
        $model = User::find();
        try {
            $model->db->beginTransaction();
            foreach ($users as $user) {
                User::add($user['name'], $user['lastName'], $user['age']); // нужна DTO для $user в данном контексте
                $ids[] = $model->db->lastInsertId();
            }
            $model->db->commit();
            return $ids;
        } catch (\Exception $e) { // тут нужны логи
            $model->db->rollBack();
            return [];
        }
    }
}