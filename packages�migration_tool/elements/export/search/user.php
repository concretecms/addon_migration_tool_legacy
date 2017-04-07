<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

$form = Loader::helper('form');
$datetime = Loader::helper('form/date_time')->translate('datetime', $_GET);

$groups = array();
$list = new GroupSearch();
$list->setItemsPerPage(-1);
$groups = array('' => t('** All Groups'));
foreach ($list->getPage() as $group) {
    $groups[$group['gID']] = $group['gName'];
}

?>
<div class="control-group">
    <label class="control-label"><?=t('Keywords')?></label>
    <div class="controls">
        <?=$form->text('keywords')?>
    </div>
</div>

<div class="control-group">
    <label class="control-label"><?=t('Added on or After')?></label>
    <div class="controls">
        <?=Loader::helper('form/date_time')->datetime('datetime', $datetime, true)?>
    </div>
</div>

<div class="control-group">
    <label class="control-label"><?=t('Filter by Group')?></label>
    <div class="controls">
        <select name="gID" style="display: none">
            <?php foreach($groups as $key => $value) {  ?>
                <option value="<?=$key?>" <?php if ($_GET['gID'] == $key) { ?> selected <?php } ?>><?=$value?></option>
            <?php } ?>
        </select>
    </div>
</div>


<script type="text/javascript">
    $(function() {
        $('select[name=gID]').chosen();
    });
</script>