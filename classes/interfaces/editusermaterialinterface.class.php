<?php
/**
 * Файл стандартного интерфейса редактирования пользовательского материала
 */
namespace RAAS\CMS\Users;

use RAAS\Controller_Frontend as RAASControllerFrontend;
use RAAS\CMS\Block_Form;
use RAAS\CMS\Material;
use RAAS\CMS\Page;
use RAAS\CMS\User;

/**
 * Класс стандартного интерфейса редактирования пользовательского материала
 */
class EditUserMaterialInterface extends RegisterInterface
{
    /**
     * Конструктор класса
     * @param Block_Form|null $block Блок, для которого применяется
     *                               интерфейс
     * @param Page|null $page Страница, для которой применяется интерфейс
     * @param array $get Поля $_GET параметров
     * @param array $post Поля $_POST параметров
     * @param array $cookie Поля $_COOKIE параметров
     * @param array $session Поля $_SESSION параметров
     * @param array $server Поля $_SERVER параметров
     * @param array $files Поля $_FILES параметров
     */
    public function __construct(
        Block_Form $block = null,
        Page $page = null,
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $session = [],
        array $server = [],
        array $files = []
    ) {
        parent::__construct(
            $block,
            $page,
            $get,
            $post,
            $cookie,
            $session,
            $server,
            $files
        );
    }


    public function process()
    {
        $result = [];
        // 2019-08-21, AVS: пока не помню, для чего создается новый пользователь,
        // но вероятно в этом есть какой-то смысл
        $uid = RAASControllerFrontend::i()->user->id;
        $user = new User($uid);
        $form = $this->block->Form;
        $result['Form'] = $form;
        $result['User'] = $user;
        if (!$user->id) {
            return $result;
        }

        if ($form->id) {
            if ($this->isFormProceed(
                $this->block,
                $form,
                $this->server['REQUEST_METHOD'],
                $this->post
            )) {
                $localError = $this->check(
                    $form,
                    $this->post,
                    $this->session,
                    $this->files
                );

                if (!$localError) {
                    if ($material = $this->processUserMaterial(
                        $user,
                        $form,
                        false,
                        $this->page,
                        $this->post,
                        $this->server,
                        $this->files
                    )) {
                        $result['Material'] = $material;
                    }
                    $result['success'][(int)$this->block->id] = true;
                }
                $result['DATA'] = $this->post;
                $result['localError'] = $localError;
            } else {
                $result['DATA'] = [];
                $material = $this->getUserMaterial($form, $user, $new);
                $materialId = $material->id;
                foreach ($form->fields as $fieldURN => $formField) {
                    if ($materialId && isset($material->fields[$fieldURN])) {
                        $materialField = $material->fields[$fieldURN];
                        $values = array_map(function ($x) {
                            if ($x instanceof Material) {
                                return $x->id;
                            } else {
                                return $x;
                            }
                        }, $materialField->getValues(true));
                        if ($formField->multiple) {
                            $result['DATA'][$fieldURN] = $values;
                        } else {
                            $result['DATA'][$fieldURN] = array_shift($values);
                        }
                    } elseif ($materialId &&
                        in_array($fieldURN, ['_name_', '_description_'])
                    ) {
                        $result['DATA'][$fieldURN] = $material->{trim($fieldURN, '_')};
                    } elseif (!$materialId->id) {
                        $result['DATA'][$fieldURN] = $formField->defval;
                    }
                }
                $result['localError'] = [];
            }
        }
        return $result;
    }
}
