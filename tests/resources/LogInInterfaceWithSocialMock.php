<?php
/**
 * Интерфейс входа в систему с замещением профиля соц. сетей
 */
namespace RAAS\CMS\Users;

use ReflectionClass;
use RAAS\CMS\ULogin;

/**
 * Класс интерфейса входа в систему с замещением профиля соц. сетей
 */
class LogInInterfaceWithSocialMock extends LogInInterface
{
    public function getProfile($token)
    {
        $uLogin = new ULogin();
        $reflection = new ReflectionClass($uLogin);
        $profileProperty = $reflection->getProperty('profile');
        $profileProperty->setAccessible(true);
        $lastNameProperty = $reflection->getProperty('last_name');
        $lastNameProperty->setAccessible(true);
        $firstNameProperty = $reflection->getProperty('first_name');
        $firstNameProperty->setAccessible(true);
        switch ($token) {
            case 'testProcessSocialLoginByToken':
                $profileProperty->setValue($uLogin, 'https://vk.com/test');
                break;
            case 'testProcessSocialLoginByEmail':
                $profileProperty->setValue($uLogin, 'https://vk.com/test1');
                $uLogin->email = 'test@test.org';
                break;
            case 'testProcessSocialLoginByEmailWithNotFound':
                $profileProperty->setValue($uLogin, 'https://vk.com/test1');
                $uLogin->email = 'aaa@test.org';
                break;
            case 'testProcessSocialLoginQuickRegister':
                $profileProperty->setValue($uLogin, 'https://test-social.com/test3-profile');
                $uLogin->email = 'test3@test.org';
                $lastNameProperty->setValue($uLogin, 'Test');
                $firstNameProperty->setValue($uLogin, 'User');
                $uLogin->nickname = 'test';
                $uLogin->phone = '+7 999 333-33-33';
                break;
            case 'testProcessSocialLoginQuickRegisterWithoutNickName':
                $profileProperty->setValue($uLogin, 'https://test-social.com/test4aaa');
                $uLogin->email = 'test4@test.org';
                $lastNameProperty->setValue($uLogin, 'Test');
                $firstNameProperty->setValue($uLogin, 'User');
                $uLogin->phone = '+7 999 444-44-44';
                break;
            default:
                return null;
                break;
        }
        return $uLogin;
    }
}
