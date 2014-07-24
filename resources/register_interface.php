<?php
namespace RAAS\CMS\Users;
use \RAAS\Controller_Frontend;
use \RAAS\CMS\Form;
use \RAAS\Application;
use \RAAS\CMS\User;

$notify = function(User $User, Form $Form, array $config = array(), $ADMIN = false)
{
    $emails = $sms = array();
    if (!$ADMIN) {
        if ($User->email) {
            $emails[] = $User->email;
        }
    } else {
        $temp = array_values(array_filter(array_map('trim', preg_split('/( |;|,)/', $Form->email))));
        foreach ($temp as $row) {
            if (($row[0] == '[') && ($row[strlen($row) - 1] == ']')) {
                $sms[] = substr($row, 1, -1);
            } else {
                $emails[] = $row;
            }
        }
    }
    if ($Form->Interface->id) {
        $template = $Form->Interface->description;
    }
    
    $subject = date(DATETIMEFORMAT) . ' ' . sprintf(REGISTRATION_ON_SITE, $_SERVER['HTTP_HOST']);
    if ($emails) {
        ob_start();
        eval('?' . '>' . $template);
        $message = ob_get_contents();
        ob_end_clean();
        \RAAS\Application::i()->sendmail($emails, $subject, $message, 'info@' . $_SERVER['HTTP_HOST'], 'RAAS.CMS');
    }
    if ($sms) {
        ob_start();
        $SMS = true;
        eval('?' . '>' . $template);
        $message_sms = ob_get_contents();
        ob_end_clean();
        \RAAS\Application::i()->sendmail($sms, $subject, $message_sms, 'info@' . $_SERVER['HTTP_HOST'], 'RAAS.CMS', false);
    }
};

$OUT = array();
$User = Controller_Frontend::i()->user;
$Form = new Form(isset($config['form_id']) ? (int)$config['form_id'] : 0);

if ($Form->id) {
    $localError = array();
    if (($Form->signature && isset($_POST['form_signature']) && $_POST['form_signature'] == md5('form' . (int)$Form->id . (int)$Block->id)) || (!$Form->signature && ($_SERVER['REQUEST_METHOD'] == 'POST'))) {
        $Item = $User;
        foreach ($Form->fields as $row) {
            switch ($row->datatype) {
                case 'file': case 'image':
                    $val = isset($_FILES[$row->urn]['tmp_name']) ? $_FILES[$row->urn]['tmp_name'] : null;
                    if ($val && $row->multiple) {
                        $val = (array)$val;
                        $val = array_shift($val);
                    }
                    if (!isset($val) || !$row->isFilled($val)) {
                        if ($row->required && !$row->countValues()) {
                            $localError[$row->urn] = sprintf(ERR_CUSTOM_FIELD_REQUIRED, $row->name);
                        }
                    } elseif (!$row->multiple) {
                        if (!$row->validate($val)) {
                            $localError[$row->urn] = sprintf(ERR_CUSTOM_FIELD_INVALID, $row->name);
                        }
                    }
                    break;
                default:
                    $val = isset($_POST[$row->urn]) ? $_POST[$row->urn] : null;
                    if ($val && $row->multiple) {
                        $val = (array)$val;
                        $val = array_shift($val);
                    }
                    if (!isset($val) || !$row->isFilled($val)) {
                        if ($row->required) {
                            $localError[$row->urn] = sprintf(ERR_CUSTOM_FIELD_REQUIRED, $row->name);
                        }
                    } elseif (!$row->multiple) {
                        if (($row->datatype == 'password') && ($_POST[$row->urn] != $_POST[$row->urn . '@confirm'])) {
                            $localError[$row->urn] = sprintf(ERR_CUSTOM_PASSWORD_DOESNT_MATCH_CONFIRM, $row->name);
                        } elseif (!$row->validate($val)) {
                            $localError[$row->urn] = sprintf(ERR_CUSTOM_FIELD_INVALID, $row->name);
                        }
                    }
                    break;
            }
        }
        if (!$User->id && $Form->antispam && $Form->antispam_field_name) {
            switch ($Form->antispam) {
                case 'captcha':
                    if (!isset($_POST[$Form->antispam_field_name], $_SESSION['captcha_keystring']) || ($_POST[$Form->antispam_field_name] != $_SESSION['captcha_keystring'])) {
                        $localError[$row->urn] = ERR_CAPTCHA_FIELD_INVALID;
                    }
                    break;
                case 'hidden':
                    if (isset($_POST[$Form->antispam_field_name]) && $_POST[$Form->antispam_field_name]) {
                        $localError[$row->urn] = ERR_CAPTCHA_FIELD_INVALID;
                    }
                    break;
            }
        }
        if (isset($_POST['email']) && $_POST['email'] && isset($Form->fields['email'])) {
            if ($User->checkEmailExists(trim($_POST['login']))) {
                $localError['login'] = 'ERR_LOGIN_EXISTS';
            } elseif (!isset($Form->fields['login'])) {
                if ($User->checkLoginExists(trim($_POST['email']))) {
                    $localError['email'] = 'ERR_LOGIN_EXISTS';
                }
            }
        }
        if (!$localError) {
            $User->page_id = (int)$Page->id;
            $User->ip = (string)$_SERVER['REMOTE_ADDR'];
            $User->user_agent = (string)$_SERVER['HTTP_USER_AGENT'];
            $User->vis = (int)($config['activation_type'] == Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED);
            $User->new = 1;
            
            if (isset($Form->fields['email'])) {
                $val = $User->email = trim($_POST['email']);
                if ($val && $config['email_as_login']) {
                    $User->login = $val;
                }
            }
            if (isset($Form->fields['login']) && !$config['email_as_login']) {
                if ($val = trim($_POST['login'])) {
                    $User->login = $val;
                }
            }
            if (isset($Form->fields['password']) && ($val = trim($_POST['password']))) {
                $User->password = $val;
                $User->password_md5 = Application::i()->md5It($val);
            }
            if (isset($Form->fields['lang']) && ($val = trim($_POST['lang']))) {
                $User->lang = $val;
            }
            $User->commit();

            foreach ($Form->fields as $fname => $temp) {
                if (isset($User->fields[$fname])) {
                    $row = $User->fields[$fname];
                    switch ($row->datatype) {
                        case 'file': case 'image':
                            $row->deleteValues();
                            if ($row->multiple) {
                                foreach ($_FILES[$row->urn]['tmp_name'] as $key => $val) {
                                    $row2 = array(
                                        'vis' => (int)$_POST[$row->urn . '@vis'][$key], 
                                        'name' => (string)$_POST[$row->urn . '@name'][$key],
                                        'description' => (string)$_POST[$row->urn . '@description'][$key],
                                        'attachment' => (int)$_POST[$row->urn . '@attachment'][$key]
                                    );
                                    if (is_uploaded_file($_FILES[$row->urn]['tmp_name'][$key]) && $row->validate($_FILES[$row->urn]['tmp_name'][$key])) {
                                        $att = new Attachment((int)$row2['attachment']);
                                        $att->upload = $_FILES[$row->urn]['tmp_name'][$key];
                                        $att->filename = $_FILES[$row->urn]['name'][$key];
                                        $att->mime = $_FILES[$row->urn]['type'][$key];
                                        $att->parent = $Material;
                                        if ($row->datatype == 'image') {
                                            $att->image = 1;
                                            if ($temp = (int)Package::i()->registryGet('maxsize')) {
                                                $att->maxWidth = $att->maxHeight = $temp;
                                            }
                                            if ($temp = (int)Package::i()->registryGet('tnsize')) {
                                                $att->tnsize = $temp;
                                            }
                                        }
                                        $att->commit();
                                        $row2['attachment'] = (int)$att->id;
                                        $row->addValue(json_encode($row2));
                                    } elseif ($row2['attachment']) {
                                        $row->addValue(json_encode($row2));
                                    }
                                    unset($att, $row2);
                                }
                            } else {
                                $row2 = array(
                                    'vis' => (int)$_POST[$row->urn . '@vis'], 
                                    'name' => (string)$_POST[$row->urn . '@name'], 
                                    'description' => (string)$_POST[$row->urn . '@description'],
                                    'attachment' => (int)$_POST[$row->urn . '@attachment']
                                );
                                if (is_uploaded_file($_FILES[$row->urn]['tmp_name']) && $row->validate($_FILES[$row->urn]['tmp_name'])) {
                                    $att = new Attachment((int)$row2['attachment']);
                                    $att->upload = $_FILES[$row->urn]['tmp_name'];
                                    $att->filename = $_FILES[$row->urn]['name'];
                                    $att->mime = $_FILES[$row->urn]['type'];
                                    $att->parent = $Material;
                                    if ($row->datatype == 'image') {
                                        $att->image = 1;
                                        if ($temp = (int)Package::i()->registryGet('maxsize')) {
                                            $att->maxWidth = $att->maxHeight = $temp;
                                        }
                                        if ($temp = (int)Package::i()->registryGet('tnsize')) {
                                            $att->tnsize = $temp;
                                        }
                                    }
                                    $att->commit();
                                    $row2['attachment'] = (int)$att->id;
                                    $row->addValue(json_encode($row2));
                                } elseif ($_POST[$row->urn . '@attachment']) {
                                    $row2['attachment'] = (int)$_POST[$row->urn . '@attachment'];
                                    $row->addValue(json_encode($row2));
                                }
                                unset($att, $row2);
                            }
                            break;
                        default:
                            $row->deleteValues();
                            if (isset($_POST[$row->urn])) {
                                foreach ((array)$_POST[$row->urn] as $val) {
                                    $row->addValue($val);
                                }
                            }
                            break;
                    }
                    if (in_array($row->datatype, array('file', 'image'))) {
                        $row->clearLostAttachments();
                    }
                }
            }

            foreach ($User->fields as $fname => $temp) {
                if (!isset($_POST[$fname])) {
                    switch ($temp->datatype) {
                        case 'datetime': case 'datetime-local':
                            $temp->addValue(date('Y-m-d H:i:s'));
                            break;
                        case 'date':
                            $temp->addValue(date('Y-m-d'));
                            break;
                        case 'time':
                            $temp->addValue(date('H:i:s'));
                            break;
                    }
                }
            }

            if (isset($User->fields['ip'])) {
                $User->fields['ip']->deleteValues();
                $User->fields['ip']->addValue((string)$_SERVER['REMOTE_ADDR']);
            }
            if (isset($User->fields['user_agent'])) {
                $User->fields['user_agent']->deleteValues();
                $User->fields['user_agent']->addValue((string)$_SERVER['HTTP_USER_AGENT']);
            }
            
            if ($Form->email && (!$User->id || $config['notify_about_edit'])) {
                $notify($User, $Form, $config, true);
            }
            if ($User->email && !$User->id) {
                $notify($User, $Form, $config, false);
            }
            $OUT['success'][(int)$Block->id] = true;
        }
    }
    $OUT['localError'] = $localError;
    $OUT['DATA'] = $_POST;
    $OUT['User'] = $User;
}
$OUT['Form'] = $Form;

return $OUT;