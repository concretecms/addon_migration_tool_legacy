<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Loader::helper('form');
?>


<?=Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Migration Tool'))?>

<?php if ($this->controller->getTask() == 'add_to_batch') { ?>

    <div class="ccm-dashboard-header-buttons well">
        <a href="<?=View::action('view_batch', $batch->getID())?>" class="btn btn-default"><i class="fa fa-angle-double-left"></i> <?=t('Back to Batch')?></a>
    </div>

    <form method="get" action="<?=View::action('add_to_batch', $batch->getID())?>">
        <div class="form-group">
            <?=$form->label('item_type', t('Choose Item Type'))?>
            <select name="item_type" class="form-control">
                <option value=""><?=t('** Select Item')?></option>
                <?php foreach ($drivers as $itemType) {
                    ?>
                    <option value="<?=$itemType->getHandle()?>"
                            <?php if (isset($selectedItemType) && $selectedItemType->getHandle() == $itemType->getHandle()) {
                            ?>selected<?php
                    }
                    ?>><?=$itemType->getPluralDisplayName()?></option>
                    <?php
                } ?>
            </select>
            <button type="submit" name="submit" class="btn btn-primary"><?=t('Go')?></button>
        </div>
    </form>


<?php if (isset($selectedItemType)) {
    ?>


    <?php $formatter = $selectedItemType->getResultsFormatter($batch);
    ?>

    <?php if ($formatter->hasSearchForm()) {
        ?>

        <form method="get" action="<?=View::action('add_to_batch', $batch->getID())?>" class="clearfix">
            <?=$form->hidden('item_type', $selectedItemType->getHandle())?>
            <?=$form->hidden('search_form_submit', 1)?>

            <?=$formatter->displaySearchForm();
            ?>
            <div class="form-actions">
                <button type="submit" name="submit" class="btn pull-right btn-default"><?=t('Search')?></button>
            </div>
        </form>
        <?php
    }
    ?>

    <?php if ($formatter->hasSearchResults($request)) {
        ?>
        <?php if ($formatter->hasSearchForm()) {
            ?>
            <hr/>
            <?php
        }
        ?>

        <h3><?=$selectedItemType->getPluralDisplayName()?></h3>



        <div class="clearfix">
            <button disabled class="pull-right btn-default btn btn-sm" data-action="add-to-batch" type="button"><?=t('Add to Batch')?></button>
            <h4><?=t('Results')?></h4>
        </div>


        <?php echo $formatter->displaySearchResults();
        ?>

        <?php
    }
    ?>

    <script type="text/javascript">
        $(function() {
            $('input[data-action=select-all]').on('click', function() {
                if ($(this).is(':checked')) {
                    $('tbody input[type=checkbox]:enabled').prop('checked', true);
                } else {
                    $('tbody input[type=checkbox]:enabled').prop('checked', false);
                }
                $('tbody input[type=checkbox]:enabled').trigger('change');
            });

            $('tbody input[type=checkbox]').on('change', function() {
                if ($('tbody input[type=checkbox]:checked').length) {
                    $('button[data-action=add-to-batch]').prop('disabled', false);
                } else {
                    $('button[data-action=add-to-batch]').prop('disabled', true);
                }
            });

            $('button[data-action=add-to-batch]').on('click', function() {
                var $checkboxes = $('input[data-checkbox=batch-item]');
                if ($checkboxes.length) {
                    var data = $checkboxes.serializeArray();
                    jQuery.fn.dialog.showLoader();
                    data.push({'name': 'batch_id', 'value': '<?=$batch->getID()?>'});
                    data.push({'name': 'item_type', 'value': '<?=$selectedItemType->getHandle()?>'});
                    data.push({'name': 'ccm_token', 'value': '<?=Loader::helper('validation/token')->generate('add_items_to_batch')?>'});

                    $.post('<?=View::action('add_items_to_batch')?>', data, function(r) {
                        jQuery.fn.dialog.hideLoader();
                        if (r.error) {
                            alert(r.messages.join('<br>'));
                        } else {
                            alert('<?=t('Items added successfully.')?>');
                        }
                    }, 'json');
                }
            });

        });
    </script>

    <?php
} ?>

<?php } else if ($this->controller->getTask() == 'export_batch') { ?>

    <div class="ccm-dashboard-header-buttons well">
        <a href="<?=View::action('view_batch', $batch->getID())?>" class="btn btn-default"><i class="fa fa-angle-double-left"></i> <?=t('Back to Batch')?></a>
    </div>

    <?php if (count($files)) {
        ?>

        <script type="text/javascript">
            $(function() {
                $('input[data-checkbox=select-all]').on('click', function() {
                    if ($(this).is(':checked')) {
                        $('tbody input[type=checkbox]:enabled').prop('checked', true);
                    } else {
                        $('tbody input[type=checkbox]:enabled').prop('checked', false);
                    }
                    $('tbody input[type=checkbox]:enabled').trigger('change');
                });

                $('tbody input[type=checkbox]').on('change', function() {
                    if ($('tbody input[type=checkbox]:checked').length) {
                        $('button[data-action=download-files]').prop('disabled', false);
                    } else {
                        $('button[data-action=download-files]').prop('disabled', true);
                    }
                });

            });
        </script>

        <form method="post" action="<?=View::url('/dashboard/migration/export', 'download_files')?>">
            <input type="hidden" name="id" value="<?=$batch->getID()?>">
            <?=Loader::helper('validation/token')->output('download_files')?>
            <button style="float: right" disabled class="btn small btn-sm" data-action="download-files" type="submit"><?=t('Download Files')?></button>
            <h3><?=t('Files')?></h3>

            <table class="table table-striped zebra-striped">
                <thead>
                <tr>
                    <th><input type="checkbox" data-checkbox="select-all"></th>
                    <th><?=t('ID')?></th>
                    <th style="width: 100%"><?=t('Filename')?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($files as $file) {
                    ?>
                    <tr>
                        <td><input type="checkbox" data-checkbox="batch-file" name="batchFileID[]" value="<?=$file->getFileID()?>"></td>
                        <td><?=$file->getFileID()?></td>
                        <td><?=$file->getFileName()?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </form>
        <?php
    } else {
        ?>
        <h3><?=t('Files')?></h3>
        <p><?=t('No referenced files found.')?></p>
        <?php
    } ?>

    <h3><?=t('Content XML')?></h3>
    <form method="post" action="<?=View::action('export_batch_xml', $batch->getID())?>">
        <div class="btn-group">
            <button type="submit" name="view" value="1" class="btn btn-default"><?=t('View XML')?></button>
            <button type="submit" name="download" value="1" class="btn btn-default"><?=t('Download XML')?></button>
        </div>
    </form>



<?php } else if ($this->controller->getTask() == 'view_batch' && $batch) { ?>


    <div class="ccm-dashboard-header-buttons well">
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

<?php } else { ?>

    <h3><?=t('Batches')?></h3>
    <?php if (count($batches)) { ?>

    <table class="table table-striped zebra-striped">
    <thead>
        <tr>
            <th><?=t('Date')?></th>
            <th><?=t('Notes')?></th>
        </tr>
    </thead>
        <?php foreach($batches as $batch) { ?>
            <tr>
                <td style="width: 20%; white-space: nowrap"><a href="<?=View::url('/dashboard/migration/export', 'view_batch', $batch->getID())?>"><?=$batch->getTimestamp()?></td>
                <td><?=$batch->getNotes()?></td>
            </tr>
        <?php } ?>
    </table>

    <?php } else { ?>
        <p><?=t("You have not added any content batches.")?></p>
    <?php } ?>

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

<?php } ?>
<?=Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper();?>