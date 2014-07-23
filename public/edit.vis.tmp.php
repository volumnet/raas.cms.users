<?php 
$_RAASForm_Field = function(\RAAS\Field $Field) use (&$_RAASForm_Control, &$_RAASForm_Options) {
    $err = (bool)array_filter((array)$Field->Form->localError, function($x) use ($Field) { return $x['value'] == $Field->name; });
    ?>
    <div class="control-group<?php echo $err ? ' error' : ''?>">
      <div class="controls">
        <label class="checkbox" style="display: inline-block; margin-right: 12px; min-width: 85px;"><?php echo $_RAASForm_Control($Field, false)?> <?php echo htmlspecialchars($Field->caption)?></label>
        <?php if ($Field->Form->Item->email && !\RAAS\CMS\Users\Module::i()->registryGet('automatic_notification')) { ?>
            <a href="#notifyUserModal" role="button" class="btn btn-success" data-toggle="modal"><?php echo CMS\Users\NOTIFY?></a>
        <?php } ?>
      </div>
    </div>
    <?php
};