<?php 
$_RAASForm_Field = function(\RAAS\Field $field) {
    $err = (bool)array_filter((array)$field->Form->localError, function($x) use ($field) {
        return $x['value'] == $field->name;
    });
    ?>
    <div class="control-group<?php echo $err ? ' error' : ''?>">
      <div class="controls">
        <label class="checkbox" style="display: inline-block; margin-right: 12px; min-width: 85px;">
          <?php echo $field->render()?> <?php echo htmlspecialchars($field->caption)?>
        </label>
        <?php if ($field->Form->Item->email && !\RAAS\CMS\Users\Module::i()->registryGet('automatic_notification')) { ?>
            <a href="#notifyUserModal" role="button" class="btn btn-success" data-toggle="modal"><?php echo CMS\Users\NOTIFY?></a>
        <?php } ?>
      </div>
    </div>
    <?php
};
