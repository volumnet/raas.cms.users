<?php
/**
 * Файл трейта проверки редиректа
 */
declare(strict_types=1);

namespace RAAS\CMS\Users;

/**
 * Трейт проверки редиректа
 */
trait CheckRedirectTrait
{
    /**
     * По необходимости применяет редирект
     * @param array $post Данные $_POST-полей
     * @param array $server Данные $_SERVER-полей
     * @param string $referer URL реферера
     * @param bool $debug Режим отладки
     * @return string|true true, когда редирект не нужен, string - URL редиректа в режиме отладки
     */
    public function checkRedirect(
        array $post = [],
        array $server = [],
        $referer = null,
        $debug = false
    ) {
        if ($post['AJAX'] ?? null) {
            return true;
        } elseif ($referer) {
            $url = $referer;
        } else {
            $url = $server['REQUEST_URI'];
        }
        if ($debug) {
            return $url;
        // @codeCoverageIgnoreStart
        } else {
            header('Location: ' . $url);
            exit;
        }
        // @codeCoverageIgnoreEnd
    }
}
