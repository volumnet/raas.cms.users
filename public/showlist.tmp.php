<div class="tabbable">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#users" data-toggle="tab"><?php echo $Table->caption?></a></li>
    <li><a href="#groups" data-toggle="tab"><?php echo $GroupsTable->caption?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="users">
      <form class="form-search" action="<?php echo \SOME\HTTP::queryString()?>" method="get">
        <?php foreach ($VIEW->nav as $key => $val) { ?>
            <?php if (!in_array($key, array('page', 'search_string', 'group_only'))) { ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key)?>" value="<?php echo htmlspecialchars($val)?>" />
            <?php } ?>
        <?php } ?>
        <div class="input-append">
          <input type="search" class="span2 search-query" name="search_string" value="<?php echo htmlspecialchars($VIEW->nav['search_string'])?>" />
          <button type="submit" class="btn"><i class="icon-search"></i></button>
        </div> &nbsp; 
        <?php if ($Group->id) { ?>
            <label class="checkbox" for="group_only">
              <input type="checkbox" name="group_only" id="group_only" value="1" <?php echo $VIEW->nav['group_only'] ? 'checked="checked"' : ''?> />
              <?php echo SEARCH_ONLY_IN_GROUPS?>
            </label>
        <?php } ?>
      </form>
      <?php 
      if ($Table->Set) { 
          include \RAAS\Application::i()->view->context->tmp('/table.tmp.php');
       }
       ?>
    </div>
    <div class="tab-pane" id="groups">
      <?php 
      if ($GroupsTable->Set) { 
          $Table = $GroupsTable;
          include \RAAS\Application::i()->view->context->tmp('/table.tmp.php');
      }
      ?>
    </div>
  </div>
</div>