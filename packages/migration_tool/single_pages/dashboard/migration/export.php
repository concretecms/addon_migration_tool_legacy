<?
defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Loader::helper('form');
?>


<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Migration Tool'))?>

<? if ($this->controller->getTask() == 'view_batch' && $batch) { ?>


    <div class="ccm-dashboard-header-buttons">
        <div class="btn-group" role="group">
            <a href="<?=View::action('add_to_batch', $batch->getId())?>" class="btn btn-default"><?=t("Add Content to Batch")?></a>
            <button data-action="remove-from-batch" disabled class="btn btn-default"><?=t('Remove Selected')?></button>
            <a href="<?=View::action('export_batch', $batch->getId())?>" class="btn btn-primary"><?=t("Export Batch")?></a>
            <a href="javascript:void(0)" data-dialog-height="90" data-dialog="delete-batch" data-dialog-title="<?=t('Delete Batch')?>" class="btn btn-danger"><?=t("Delete Batch")?></a>
        </div>
    </div>

    <div style="display: none">

        <div id="ccm-dialog-delete-batch" class="ccm-ui">
            <form method="post" action="<?=View::action('delete_batch')?>">
                <?=Loader::helper("validation/token")->output('delete_batch')?>
                <input type="hidden" name="id" value="<?=$batch->getID()?>">
                <p><?=t('Are you sure you want to delete this export batch? This cannot be undone.')?></p>
                <div class="dialog-buttons">
                    <button class="btn btn-default pull-left" onclick="jQuery.fn.dialog.closeTop()"><?=t('Cancel')?></button>
                    <button class="btn btn-danger pull-right" onclick="$('#ccm-dialog-delete-batch form').submit()"><?=t('Delete Batch')?></button>
                </div>
            </form>
        </div>

    </div>


    <?php if ($batch) {
        ?>

        <h2><?=t('Batch')?>
            <small><?=Loader::helper('date')->formatDateTime($batch->getTimestamp(), true)?></small></h2>

        <?php if ($batch->getNotes()) {
            ?>
            <p><?=$batch->getNotes()?></p>
            <?php
        }
        ?>

        <?php if ($batch->hasRecords()) {
            ?>

            <form method="post" action="<?=View::action('remove_batch_items')?>" data-form="remove-batch-items">
                <?=$token->output('remove_batch_items')?>
                <?=$form->hidden('batch_id', $batch->getId())?>

                <?php foreach ($batch->getObjectCollections() as $collection) {
                    if ($collection->hasRecords()) {
                        $itemType = $collection->getItemTypeObject();
                        $formatter = $itemType->getResultsFormatter($batch);
                        ?>

                        <h3><?=$itemType->getPluralDisplayName()?></h3>
                        <?php echo $formatter->displayBatchResults()?>
                        <?php
                    }
                    ?>
                    <?php
                }
                ?>

            </form>

            <?php

        } else {
            ?>
            <p><?=t('This export batch is empty.')?></p>
            <?php
        }
        ?>

        <?php
    } ?>

    <script type="text/javascript">
        $(function() {
            $('a[data-dialog]').on('click', function() {
                var element = '#ccm-dialog-' + $(this).attr('data-dialog');
                jQuery.fn.dialog.open({
                    element: element,
                    modal: true,
                    width: 320,
                    title: $(this).attr('data-dialog-title'),
                    height: $(this).attr('data-dialog-height')
                });
            });

            $('input[data-action=select-all]').on('click', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('table').find('tbody input[type=checkbox]:enabled').prop('checked', true);
                } else {
                    $(this).closest('table').find('tbody input[type=checkbox]:enabled').prop('checked', false);
                }
                $(this).closest('table').find('tbody input[type=checkbox]:enabled').trigger('change');
            });

            $('tbody input[type=checkbox]').on('change', function() {
                if ($(this).closest('table').find('tbody input[type=checkbox]:checked').length) {
                    $('button[data-action=remove-from-batch]').prop('disabled', false);
                } else {
                    $('button[data-action=remove-from-batch]').prop('disabled', true);
                }
            });

            $('button[data-action=remove-from-batch]').on('click', function() {
                $('form[data-form=remove-batch-items]').submit();
            });

        });
    </script>


    <?php /* ?>
    <script type="text/javascript">
        $(function() {
            $('input.ccm-migration-select-all').on('click', function() {
                if ($(this).is(':checked')) {
                    $('tbody input[type=checkbox]:enabled').prop('checked', true);
                } else {
                    $('tbody input[type=checkbox]:enabled').prop('checked', false);
                }
                $('tbody input[type=checkbox]:enabled').trigger('change');
            });

            $('tbody input[type=checkbox]').on('change', function() {
                if ($('tbody input[type=checkbox]:checked').length) {
                    $('button[data-action=remove-from-batch]').prop('disabled', false);
                } else {
                    $('button[data-action=remove-from-batch]').prop('disabled', true);
                }
            });

            $('button[data-action=remove-from-batch]').on('click', function() {
                var $checkboxes = $('input[data-checkbox=batch-page]');
                if ($checkboxes.length) {
                    var data = $checkboxes.serializeArray();
                    jQuery.fn.dialog.showLoader();
                    data.push({'name': 'id', 'value': '<?=$batch->getID()?>'});
                    data.push({'name': 'ccm_token', 'value': '<?=Loader::helper('validation/token')->generate('remove_from_batch')?>'});
                    $.post('<?=View::url('/dashboard/migration/batches', 'remove_from_batch')?>', data, function(r) {
                        jQuery.fn.dialog.hideLoader();
                        if (r.error) {
                            alert(r.messages.join('<br>'));
                        } else if (r.pages.length) {
                            for (var i = 0; i < r.pages.length; i++) {
                                var cID = r.pages[i];
                                $('tr[data-batch-page=' + cID + ']').remove();
                            }
                        }
                    }, 'json');
                }
            });
        });
    </script>

    <form method="post" action="<?=View::url('/dashboard/migration/batches', 'update_batch')?>">
    <input type="hidden" name="id" value="<?=$batch->getID()?>">
    <?=Loader::helper('validation/token')->output("update_batch")?>
    <div class="well">
        <a href="<?=View::url('/dashboard/migration/batches/add_pages', $batch->getID())?>" class="btn btn-primary"><?=t('Add Pages')?></a>
        <a href="<?=View::url('/dashboard/migration/batches/export', $batch->getID())?>" class="btn btn-primary"><?=t('Export Batch')?></a>
        <button type="submit" onclick="return confirm('<?=t('Delete the Batch?')?>')" name="action" value="delete" class="btn danger btn-danger"><?=t('Delete Batch')?></button>
    </div>

        <? if (count($pages)) { ?>
            <button style="float: right" disabled class="btn small btn-sm" data-action="remove-from-batch" type="button"><?=t('Remove from Batch')?></button>
            <h3><?=t('Pages')?></h3>
        <table class="table table-striped zebra-striped">
            <thead>
            <tr>
                <th style="width: 20px"><input type="checkbox" class="ccm-migration-select-all"></th>
                <th><?=t('Name')?></th>
                <th><?=t('Description')?></th>
            </tr>
            </thead>
            <tbody>
            <? foreach($pages as $page) { ?>
                <tr data-batch-page="<?=$page->getCollectionID()?>">
                    <td><input type="checkbox" data-checkbox="batch-page" name="batchPageID[]" value="<?=$page->getCollectionID()?>"></td>
                    <td><a target="_blank" href="<?=Loader::helper('navigation')->getLinkToCollection($page)?>"><?=$page->getCollectionName()?></td>
                    <td><?=$page->getCollectionDescription()?></td>
                </tr>
            <? } ?>
            </tbody>
        </table>
    <? } else { ?>
            <h3><?=t('Pages')?></h3>
        <p><?=t('No pages in batch.')?></p>
    <? } ?>
    </form>
 */
?>



<?php } else { ?>

    <h3><?=t('Batches')?></h3>
    <? if (count($batches)) { ?>

    <table class="table table-striped zebra-striped">
    <thead>
        <tr>
            <th><?=t('Date')?></th>
            <th><?=t('Notes')?></th>
        </tr>
    </thead>
        <? foreach($batches as $batch) { ?>
            <tr>
                <td style="width: 20%; white-space: nowrap"><a href="<?=View::url('/dashboard/migration/export', 'view_batch', $batch->getID())?>"><?=$batch->getTimestamp()?></td>
                <td><?=$batch->getNotes()?></td>
            </tr>
        <? } ?>
    </table>

    <? } else { ?>
        <p><?=t("You have not added any content batches.")?></p>
    <? } ?>

    <hr/>
    <form method="post" action="<?=$this->action('submit')?>">
        <fieldset>
            <?=Loader::helper('validation/token')->output("submit")?>
            <legend><?=t("Add Batch")?></legend>

            <div class="form-group">
                <label class="control-label"><?=t('Notes')?></label>
                <?=$form->textarea('notes', array('style' => 'width:100%','rows'=>2))?>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary"><?=t('Add Batch')?></button></div>
        </fieldset>
    </form>

    <div class="ccm-spacer"></div>

<? } ?>
<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();?>