<?php

class DashboardMigrationExportController extends DashboardBaseController
{
    public function delete_batch()
    {
        $id = $_POST['id'];
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (!is_object($batch)) {
            $this->error->add(t('Invalid Batch'));
        }
        if (!$this->error->has()) {
            if (!$this->token->validate('delete_batch')) {
                $this->error->add($this->token->getErrorMessage());
            }
        }
        if (!$this->error->has()) {
            $batch->delete();
            $this->redirect('/dashboard/migration/export', 'batch_deleted');
            exit;
        }
        $this->view();
    }

    public function export_batch($id = null)
    {
        $batch = MigrationBatch::getByID($id);
        if (is_object($batch)) {
            $this->set('batch', $batch);
            $exporter = $batch->getExporter();
            $files = $exporter->getReferencedFiles();
            $this->set('files', $files);
        } else {
            $this->view();
        }
    }

    public function export_batch_xml($id = null)
    {
        $batch = MigrationBatch::getByID($id);
        if (is_object($batch)) {
            $exporter = new MigrationBatchExporter($batch);
            if ($_REQUEST['download']) {
                header('Content-disposition: attachment; filename="export.xml"');
                header('Content-type: "text/xml"; charset="utf8"');
            } else {
                header('Content-type: text/xml');
            }
            // I feel like this html_entity_decode is risky but how else am I to get rid of the double
            // quoting &amp;amp; problem?
            // Never mind, this creates broken XML.
            //$xml = html_entity_decode($exporter->getContentXML(), ENT_NOQUOTES | ENT_XML1, APP_CHARSET);
            //$xml = $exporter->getContentXML();
            echo $exporter->getContentXML();
            exit;
        } else {
            $this->view();
        }
    }

    public function download_files()
    {
        @ini_set('memory_limit', '-1');
        @set_time_limit(0);
        $id = $_POST['id'];
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (!is_object($batch)) {
            $this->error->add(t('Invalid Batch'));
        }
        if (!$this->token->validate('download_files')) {
            $this->error->add($this->token->getErrorMessage());
        }
        $fh = Loader::helper('file');
        $vh = Loader::helper('validation/identifier');
        if (!$this->error->has()) {
            $temp = sys_get_temp_dir();
            if (!$temp) {
                $temp = $fh->getTemporaryDirectory();
            }
            $filename = $temp.'/'.$vh->getString().'.zip';
            $files = array();
            $filenames = array();
            foreach ((array) $_POST['batchFileID'] as $fID) {
                $f = File::getByID(intval($fID));
                if ($f->isError()) {
                    continue;
                }
                $fp = new Permissions($f);
                if ($fp->canRead()) {
                    if (!in_array(basename($f->getPath()), $filenames) && file_exists($f->getPath())) {
                        $files[] = $f->getPath();
                    }
                    $filenames[] = basename($f->getPath());
                }
            }
            if (empty($files)) {
                throw new Exception(t('None of the requested files could be found.'));
            }
            if (class_exists('ZipArchive', false)) {
                $zip = new ZipArchive();
                $res = $zip->open($filename, ZipArchive::CREATE);
                if ($res !== true) {
                    throw new Exception(t('Could not open with ZipArchive::CREATE'));
                }
                foreach ($files as $f) {
                    $zip->addFile($f, basename($f));
                }
                $zip->close();
            } else {
                $exec = escapeshellarg(DIR_FILES_BIN_ZIP).' -j '.escapeshellarg($filename);
                foreach ($files as $f) {
                    $exec .= ' '.escapeshellarg($f);
                }
                $exec .= ' 2>&1';
                @exec($exec, $output, $rc);
                if ($rc !== 0) {
                    throw new Exception(t('External zip failed. Error description: %s', implode("\n", $output)));
                }
            }
            $fh->forceDownload($filename);
        }
        exit;
    }

    public function remove_from_batch()
    {
        $id = $_POST['id'];
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (!is_object($batch)) {
            $this->error->add(t('Invalid Batch'));
        }
        if (!$this->token->validate('remove_from_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }
        $r = new stdClass();
        if (!$this->error->has()) {
            $r->error = false;
            $r->pages = array();
            foreach ((array) $_POST['batchPageID'] as $cID) {
                $r->pages[] = $cID;
                $batch->removePageID($cID);
            }
        } else {
            $r->error = true;
            $r->messages = $this->error->getList();
        }
        echo Loader::helper('json')->encode($r);
        exit;
    }

    public function add_to_batch($id = null)
    {
        $batch = MigrationBatch::getByID($id);
        if (is_object($batch)) {
            $exporters = new ExportManager();
            if (!empty($_REQUEST['item_type'])) {
                $selectedItemType = $exporters->driver($_REQUEST['item_type']);
                if (is_object($selectedItemType)) {
                    $this->set('selectedItemType', $selectedItemType);
                }
            }
            $drivers = $exporters->getDrivers();
            usort($drivers, function ($a, $b) {
                return strcasecmp($a->getPluralDisplayName(), $b->getPluralDisplayName());
            });
            $this->set('drivers', $drivers);
            $this->set('batch', $batch);
            $this->set('request', $this->request);
            $this->set('pageTitle', t('Add To Batch'));
        } else {
            $this->view();
        }
    }

    public function remove_batch_items()
    {
        if (!$this->token->validate('remove_batch_items')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $exporters = new ExportManager();
        $batch = MigrationBatch::getByID($_REQUEST['batch_id']);
        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }

        if (!$this->error->has()) {
            $values = $_REQUEST['id'];
            foreach ($values as $item_type => $ids) {
                $collection = $batch->getObjectCollection($item_type);
                if (is_object($collection)) {
                    foreach ($collection->getItems() as $item) {
                        if (in_array($item->getItemIdentifier(), $ids)) {
                            $item->delete();
                        }
                    }
                }
            }

            // Now we make sure no empty object collections remain.
            $collections = $batch->getObjectCollections();
            foreach ($collections as $collection) {
                if (!$collection->hasRecords()) {
                    $collection->delete();
                }
            }

            $this->redirect('/dashboard/migration/export', 'view_batch', $batch->getId());
        }
        $this->view_batch($_REQUEST['batch_id']);
    }

    public function view_batch($id = null)
    {
        if ($id) {
            $batch = MigrationBatch::getByID($id);
        }
        if (is_object($batch)) {
            $this->set('batch', $batch);
        }
        $this->set('token', $this->token);
    }

    public function batch_deleted()
    {
        $this->set('message', t('Batch deleted.'));
        $this->view();
    }

    public function add_items_to_batch()
    {
        if (!$this->token->validate('add_items_to_batch')) {
            $this->error->add($this->token->getErrorMessage());
        }

        $exporters = new ExportManager();
        $batch = MigrationBatch::getByID($_REQUEST['batch_id']);

        if (!is_object($batch)) {
            $this->error->add(t('Invalid batch.'));
        }

        $selectedItemType = false;
        if (!empty($_REQUEST['item_type']) && $_REQUEST['item_type']) {
            $selectedItemType = $exporters->driver($_REQUEST['item_type']);
        }

        if (!is_object($selectedItemType)) {
            $this->error->add(t('Invalid item type.'));
        }

        if (!$this->error->has()) {
            $values = $_REQUEST['id'];
            $exportItems = $selectedItemType->getItemsFromRequest($values[$selectedItemType->getHandle()]);
            $collection = $batch->getObjectCollection($selectedItemType->getHandle());
            if (!is_object($collection)) {
                $collection = new MigrationBatchObjectCollection();
                $collection->setType($selectedItemType->getHandle());
                $collection->setBatchId($batch->getID());
                $collection->save();
            }
            foreach ($exportItems as $item) {
                if (!$collection->contains($item)) {
                    $item->setCollectionId($collection->getID());
                    $item->save();
                }
            }

            $json = Loader::helper('json');
            echo $json->encode($exportItems);
            exit;
        }

        $r = new EditResponse();
        $r->setError($this->error);
        $r->outputJSON();
    }

    public function view()
    {
        $batches = MigrationBatch::getList();
        $this->set('batches', $batches);
    }

    public function submit()
    {
        if ($this->token->validate('submit')) {
            $batch = MigrationBatch::create($_POST['notes']);
            $this->redirect('/dashboard/migration/export', 'view_batch', $batch->getID());
        } else {
            $this->error->add($this->token->getErrorMessage());
        }
    }
}
