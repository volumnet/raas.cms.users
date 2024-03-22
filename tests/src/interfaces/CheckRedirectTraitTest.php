<?php
/**
 * Файл теста трейта проверки редиректа
 */
namespace RAAS\CMS\Users;

use SOME\BaseTest;
use RAAS\CMS\Page;

/**
 * Класс теста трейта проверки редиректа
 * @covers RAAS\CMS\Users\CheckRedirectTrait
 */
class CheckRedirectTraitTest extends BaseTest
{
    /**
     * Тест применения редиректа
     * (случай с AJAX)
     */
    public function testCheckRedirectWithAJAX()
    {
        $trait = $this->getMockForTrait(CheckRedirectTrait::class);

        $result = $trait->checkRedirect(['AJAX' => true], [], null, true);

        $this->assertTrue($result);
    }


    /**
     * Тест применения редиректа
     * (случай с явным реферером)
     */
    public function testCheckRedirectWithReferer()
    {
        $trait = $this->getMockForTrait(CheckRedirectTrait::class);

        $result = $trait->checkRedirect([], [], '/referer/', true);

        $this->assertEquals('/referer/', $result);
    }


    /**
     * Тест применения редиректа
     * (случай с HTTP-реферером)
     */
    public function testCheckRedirectWithHTTPReferer()
    {
        $trait = $this->getMockForTrait(CheckRedirectTrait::class);

        $result = $trait->checkRedirect(
            [],
            ['REQUEST_URI' => '/register/'],
            null,
            true
        );

        $this->assertEquals('/register/', $result);
    }
}
