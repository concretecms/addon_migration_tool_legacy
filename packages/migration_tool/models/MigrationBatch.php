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

    public function getObjectCollection($type)
    {
        $db = Loader::db();
        $id = $db->getOne('select id from MigrationExportObjectCollections where batch_id = ? and type = ?', array(
            $this->getID(), $type
        ));
        if ($id) {
            return MigrationBatchObjectCollection::getByID($id);
        }
    }

    public function getObjectCollections()
    {
        $collections = array();
        $db = Loader::db();
        $r = $db->execute('select id from MigrationExportObjectCollections where batch_id = ?', array($this->getID()));
        while ($row = $r->fetchRow()) {
            $collection = MigrationBatchObjectCollection::getById($row['id']);
            if (is_object($collection)) {
                $collections[] = $collection;
            }
        }
        return $collections;
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
        $cnt = $db->getOne('select count(id) from MigrationExportObjectCollections where batch_id = ?', array($this->id));
        return $cnt > 0;
    }

    public function delete()
    {
        $collections = $this->getObjectCollections();
        foreach($collections as $collection) {
            $collection->delete();
        }

        $db = Loader::db();
        $db->Execute('delete from MigrationExportBatches where id = ?', array($this->getID()));
    }

    public function getExporter()
    {
        $exporter = new MigrationBatchExporter($this);
        return $exporter;
    }


}