<?php
/**
 * Файл теста трейта проверки редиректа
 */
namespace RAAS\CMS\Users;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use SOME\BaseTest;
use RAAS\CMS\Page;

/**
 * Класс теста трейта проверки редиректа
 */
class CheckRedirectTraitTest extends BaseTest
{
    /**
     * Тест применения редиректа
     * (случай с AJAX)
     */
    public function testCheckRedirectWithAJAX()
    {
        $trait = new class {
            use CheckRedirectTrait;
        };

        $result = $trait->checkRedirect(['AJAX' => true], [], null, true);

        $this->assertTrue($result);
    }


    /**
     * Тест применения редиректа
     * (случай с явным реферером)
     */
    public function testCheckRedirectWithReferer()
    {
        $trait = new class {
            use CheckRedirectTrait;
        };

        $result = $trait->checkRedirect([], [], '/referer/', true);

        $this->assertEquals('/referer/', $result);
    }


    /**
     * Тест применения редиректа
     * (случай с HTTP-реферером)
     */
    public function testCheckRedirectWithHTTPReferer()
    {
        $trait = new class {
            use CheckRedirectTrait;
        };

        $result = $trait->checkRedirect(
            [],
            ['REQUEST_URI' => '/register/'],
            null,
            true
        );

        $this->assertEquals('/register/', $result);
    }
}
