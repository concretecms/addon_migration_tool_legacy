<?
defined('C5_EXECUTE') or die(_("Access Denied."));

class MigrationBatch extends Object
{
    protected $id;
    protected $notes;
    protected $timestamp;

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }


    public function getID()
    {
        return $this->id;
    }

    public static function getList()
    {
        $db = Loader::db();
        $r = $db->Execute('select id from MigrationExportBatches order by id asc');
        $batches = array();
        while ($row = $r->FetchRow()) {
            $batch = self::getByID($row['id']);
            $batches[] = $batch;
        }
        return $batches;
    }

    public static function create($notes)
    {
        $db = Loader::db();
        $timestamp = date("Y-m-d H:i:s");
        $id = $db->GetOne('select uuid()');
        $db->Execute('insert into MigrationExportBatches (id, timestamp, notes) values (?, ?, ?)', array(
            $id,
            $timestamp,
            $notes
        ));
        return self::getByID($id);
    }

    public function getObjectCollection()
    {
        return array();
    }

    public static function getByID($id)
    {
        $db = Loader::db();
        $r = $db->GetRow('select * from MigrationExportBatches where id = ?', array($id));
        if ($r && $r['id']) {
            $o = new MigrationBatch();
            $o->setPropertiesFromArray($r);
            return $o;
        }
    }

    public function hasRecords()
    {
        $db = Loader::db();
        $cnt = $db->getOne('select count(collection_id) from MigrationExportBatchObjectCollections where batch_id = ?', array($this->id));
        return $cnt > 0;
    }

    public function delete()
    {
        $db = Loader::db();
        $db->Execute('delete from MigrationExportBatches where id = ?', array($this->getID()));
    }

    /*




    public function addPageID($cID)
    {
        $db = Loader::db();
        if (!$this->containsPageID($cID)) {
            $db->Execute('insert into MigrationBatchPages (cID, batchID) values (?, ?)', array(
                $cID, $this->getID())
            );
        }
    }

    public function removePageID($cID)
    {
        $db = Loader::db();
        $db->Execute('delete from MigrationBatchPages where cID = ? and batchID = ?', array($cID, $this->getID()));
    }

    public function getPages()
    {
        $db = Loader::db();
        $r = $db->Execute('select cID from MigrationBatchPages where batchID = ? order by cID asc', array($this->getID()));
        $pages = array();
        while ($row = $r->FetchRow()) {
            $c = Page::getByID($row['cID']);
            if (is_object($c) && !$c->isError()) {
                $pages[] = $c;
            }
        }
        return $pages;
    }

    public function containsPageID($cID)
    {
        $db = Loader::db();
        $existing = $db->GetOne('select cID from MigrationBatchPages where cID = ? and batchID = ?', array($cID, $this->getID()));
        return $existing;
    }

    public function getExporter()
    {
        Loader::library('migration_batch_exporter', 'migration_tool');
        $exporter = new MigrationBatchExporter($this);
        return $exporter;
    }*/

}