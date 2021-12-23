<?php

namespace App\Services\Fns;

use App\Models\User;
use Exception;
use Illuminate\Support\Arr;

/**
 * Сервис для работы с оффлайн ключами
 * получаемыми из api ФНС
 *
 * Class OfflineKeysService
 * @package App\Services\Fns
 */
class OfflineKeysService
{
    /**
     * Задает ключ для пользователей
     *
     * @param array $keys
     * @return bool
     * @throws Exception
     */
    public function setUsersKey(array $keys): bool
    {
        if (empty($keys)) {
            throw new Exception('Оффлайн ключи не получены');
        }

        return $this->setKeyByUserInn($keys);
    }

    /**
     * Задает ключ по ИНН пользователя
     *
     * @param array $keys
     * @return bool
     * @throws Exception
     */
    private function setKeyByUserInn(array $keys): bool
    {
        foreach ($keys as $key) {

            if (!Arr::exists($key, 'Inn')) {
                throw new Exception('Не получен ИНН');
            }

            $inn = $key['Inn'];
            $user = User::where('inn', $inn)->first();

            if (!$user) {
                continue;
            }

            $this->setUserKey($key['KeyRecord'], $user);
        }

        return true;
    }

    /**
     * Устанавливает ключ для пользователя
     *
     * @param array $keyRecord
     * @param User $user
     * @return void
     * @throws Exception
     */
    private function setUserKey(array $keyRecord, User $user): void
    {
        foreach ($keyRecord as $key) {

            if (!Arr::exists($key, 'Base64Key')) {
                throw new Exception('Не получен Base64Key для пользователя с ИНН ' . $user->inn);
            }

            if (!Arr::exists($key, 'SequenceNumber')) {
                throw new Exception('Не получена инкрементная часть чека для пользователя с ИНН ' . $user->inn);
            }

            if (!Arr::exists($key, 'ExpireTime')) {
                throw new Exception('Не получен срок валидности ключа для пользователя с ИНН ' . $user->inn);
            }

            $user->setNpdOfflineKey($key['Base64Key'], $key['SequenceNumber'], $key['ExpireTime']);
        }
    }
}
