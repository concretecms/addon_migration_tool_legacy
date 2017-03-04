<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Loader::helper('form');
$pagetypes = array();
$datetime = Loader::helper('form/date_time')->translate('datetime', $_GET);
$list = CollectionType::getList();
$pagetypes = array('' => t('** Choose a page type'));
foreach ($list as $type) {
    $pagetypes[$type->getCollectionTypeID()] = $type->getCollectionTypeName();
}
?>
<div class="form-group">
    <label class="control-label"><?=t('Keywords')?></label>
    <?=$form->text('keywords')?>
    <br/><br/>
</div>

<div class="form-group">
    <label class="control-label"><?=t('Published on or After')?></label>
    <?=Loader::helper('form/date_time')->datetime('datetime', $datetime, true)?>
    <br/><br/>
</div>


<div class="form-group">
    <label class="control-label"><?=t('Filter by Parent Page')?></label>
    <?=Loader::helper('form/page_selector')->selectPage('startingPoint')?>
    <Br/>
</div>

<div class="form-group">
    <label class="control-label"><?=t('Filter by Page Type')?></label>
    <?=$form->select('ptID', $pagetypes)?>
</div>
