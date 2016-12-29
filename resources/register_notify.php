<?php
namespace RAAS\CMS;

use \RAAS\CMS\Users\Block_Register;

$smsField = function ($field) {
    $values = $field->getValues(true);
    $arr = array();
    foreach ($values as $key => $val) {
        $val = $field->doRich($val);
        switch ($field->datatype) {
            case 'date':
                $arr[$key] = date(DATEFORMAT, strtotime($val));
                break;
            case 'datetime-local':
                $arr[$key] = date(DATETIMEFORMAT, strtotime($val));
                break;
            case 'file':
            case 'image':
                $arr[$key] .= $val->name;
                break;
            case 'htmlarea':
                $arr[$key] = strip_tags($val);
                break;
            default:
                if (!$field->multiple && ($field->datatype == 'checkbox')) {
                    $arr[$key] = $val ? _YES : _NO;
                } else {
                    $arr[$key] = $val;
                }
                break;
        }
    }
    return $field->name . ': ' . implode(', ', $arr) . "\n";
};
$emailField = function ($field) {
    $values = $field->getValues(true);
    $arr = array();
    foreach ($values as $key => $val) {
        $val = $field->doRich($val);
        switch ($field->datatype) {
            case 'date':
                $arr[$key] = date(DATEFORMAT, strtotime($val));
                break;
            case 'datetime-local':
                $arr[$key] = date(DATETIMEFORMAT, strtotime($val));
                break;
            case 'color':
                $arr[$key] = '<span style="display: inline-block; height: 16px; width: 16px; background-color: ' . htmlspecialchars($val) . '"></span>';
                break;
            case 'email':
                $arr[$key] .= '<a href="mailto:' . htmlspecialchars($val) . '">' . htmlspecialchars($val) . '</a>';
                break;
            case 'url':
                $arr[$key] .= '<a href="http://' . (!preg_match('/^http(s)?:\\/\\//umi', trim($val)) ? 'http://' : '') . htmlspecialchars($val) . '">' . htmlspecialchars($val) . '</a>';
                break;
            case 'file':
                $arr[$key] .= '<a href="http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . $val->fileURL . '">' . htmlspecialchars($val->name) . '</a>';
                break;
            case 'image':
                $arr[$key] .= '<a href="http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . $val->fileURL . '">
                                 <img src="http://' . $_SERVER['HTTP_HOST'] . '/' . $val->tnURL. '" alt="' . htmlspecialchars($val->name) . '" title="' . htmlspecialchars($val->name) . '" />
                               </a>';
                break;
            case 'htmlarea':
                $arr[$key] = '<div>' . $val . '</div>';
                break;
            default:
                if (!$field->multiple && ($field->datatype == 'checkbox')) {
                    $arr[$key] = $val ? _YES : _NO;
                } else {
                    $arr[$key] = nl2br(htmlspecialchars($val));
                }
                break;
        }
    }
    return '<div>' . htmlspecialchars($field->name) . ': ' . implode(', ', $arr) . '</div>';
};
?>
<p><?php echo sprintf($ADMIN ? NEW_USER_REGISTERED_ON_SITE : YOU_HAVE_SUCCESSFULLY_REGISTERED_ON_WEBSITE, $_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST'])?></p>
<?php if ($SMS) {
    foreach ($Form->fields as $field) {
        if (in_array($field->urn, array('login', 'email'))) {
            echo $field->name . ': ' . $User->{$field->urn} . "\n";
        } elseif (isset($User->fields[$field->urn]) && ($field = $User->fields[$field->urn])) {
            echo $smsField($field);
        }
    }
} else { ?>
    <div>
      <?php
      foreach ($Form->fields as $field) {
          if ($field->urn == 'login') {
              echo '<div>' . htmlspecialchars($field->name) . ': ' . htmlspecialchars($User->{$field->urn}) . '</div>';
          } elseif ($field->urn == 'email') {
              echo '<div>' . htmlspecialchars($field->name) . ': <a href="mailto:' . htmlspecialchars($User->{$field->urn}) . '">' . htmlspecialchars($User->{$field->urn}) . '</a></div>';
          } elseif (!$ADMIN && ($field->urn == 'password')) {
              echo '<div>' . htmlspecialchars($field->name) . ': ' . htmlspecialchars($User->{$field->urn}) . '</div>';
          } elseif (isset($User->fields[$field->urn]) && ($field = $User->fields[$field->urn])) {
              echo $emailField($field);
          }
      }
      ?>
    </div>
    <?php if ($ADMIN) { ?>
        <?php if ($User && $User->id) { ?>
            <p>
              <a href="http<?php echo ($_SERVER['HTTPS'] == 'on' ? 's' : '')?>://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] . '/admin/?p=cms&m=users&action=edit&id=' . (int)$User->id)?>">
                <?php echo VIEW?>
              </a>
            </p>
        <?php } ?>
        <p>
          <small>
            <?php echo IP_ADDRESS?>: <?php echo htmlspecialchars($User->ip)?><br />
            <?php echo USER_AGENT?>: <?php echo htmlspecialchars($User->user_agent)?><br />
            <?php echo PAGE?>:
            <?php if ($User->page->parents) { ?>
                <?php foreach ($User->page->parents as $row) { ?>
                    <a href="<?php echo htmlspecialchars($User->domain . $row->url)?>"><?php echo htmlspecialchars($row->name)?></a> /
                <?php } ?>
            <?php } ?>
            <a href="<?php echo htmlspecialchars($User->domain . $User->page->url)?>"><?php echo htmlspecialchars($User->page->name)?></a>
          </small>
        </p>
        <?php
    } else {
        switch ($config['activation_type']) {
            case Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED:
                echo '<p>' . NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM . '</p>';
                break;
            case Block_Register::ACTIVATION_TYPE_ADMINISTRATOR:
                echo '<p>' . PLEASE_WAIT_FOR_ADMINISTRATOR_TO_ACTIVATE . '</p>';
                break;
            case Block_Register::ACTIVATION_TYPE_USER:
                $link = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/activate/?key=' . $User->activationKey;
                echo '<p>' . sprintf(ACTIVATION_LINK, $link, $link) . '</p>';
                break;
        }
        ?>
        <p>--</p>
        <p>
          <?php echo WITH_RESPECT?>,<br />
          <?php echo ADMINISTRATION_OF_SITE?> <a href="http<?php echo ($_SERVER['HTTPS'] == 'on' ? 's' : '')?>://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?>"><?php echo htmlspecialchars($_SERVER['HTTP_HOST'])?></a>
        </p>
        <?php
    }
}
?>
