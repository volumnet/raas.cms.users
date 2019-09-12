<?php
/**
 * Файл стандартного интерфейса редактирования пользовательского материала
 */
namespace RAAS\CMS\Users;

use Mustache_Engine;
use SOME\Text;
use RAAS\Application;
use RAAS\Controller_Frontend as RAASControllerFrontend;
use RAAS\View_Web as RAASViewWeb;
use RAAS\CMS\Block_Form;
use RAAS\CMS\Form;
use RAAS\CMS\FormInterface;
use RAAS\CMS\Package;
use RAAS\CMS\Page;
use RAAS\CMS\ULogin;
use RAAS\CMS\User;
use RAAS\CMS\View_Web as PackageViewWeb;

/**
 * Класс стандартного интерфейса редактирования пользовательского материала
 */
class EditUserMaterialInterface extends RegisterInterface
{
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
                        $result['DATA'][$fieldURN] = $materialField->getValues();
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
