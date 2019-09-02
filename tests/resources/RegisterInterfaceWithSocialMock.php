<?php
/**
 * Интерфейс регистрации с замещением профиля соц. сетей
 */
namespace RAAS\CMS\Users;

use ReflectionClass;
use RAAS\CMS\ULogin;

/**
 * Класс интерфейса регистрации с замещением профиля соц. сетей
 */
class RegisterInterfaceWithSocialMock extends RegisterInterface
{
    public function getProfile($token)
    {
        if ($token == 'sntoken') {
            $uLogin = new ULogin();
            $reflection = new ReflectionClass($uLogin);
            $reflectionProperty = $reflection->getProperty('profile');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($uLogin, 'https://vk.com/test');
            return $uLogin;
        }
        return null;
    }
}
