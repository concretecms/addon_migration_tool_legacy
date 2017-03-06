<?
defined('C5_EXECUTE') or die(_("Access Denied."));

class MigrationBatchObjectCollection
{

    protected $id;
    protected $type;
    protected $batch_id;
    protected $items;

    public static function getById($id)
    {
        $db = Loader::db();
        $row = $db->GetRow('select * from MigrationExportObjectCollections where id = ?', array($id));
        if ($row && is_array($row)) {
            $o = new static();
            $o->batch_id = $row['batch_id'];
            $o->id = $row['id'];
            $o->type = $row['type'];
            return $o;
        }
    }

    /**
     * @return mixed
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }

    /**
     * @param mixed $batch_id
     */
    public function setBatchId($batch_id)
    {
        $this->batch_id = $batch_id;
    }

    public function hasRecords()
    {
        $items = $this->getItems();
        return count($items) > 0;
    }

    public function getItemTypeObject()
    {
        $exporters = new ExportManager();
        return $exporters->driver($this->getType());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if (!isset($this->items)) {
            $db = Loader::db();
            $ids = $db->getCol('select id from MigrationExportItems where collection_id = ?', array(
                $this->getID()
            ));
            $this->items = array();
            foreach($ids as $id) {
                $item = MigrationBatchItem::getByID($id);
                if (is_object($item)) {
                    $this->items[] = $item;
                }
            }
        }
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }


    public function contains(MigrationBatchItem $item)
    {
        foreach ($this->getItems() as $existingItem) {
            if ($existingItem->getItemIdentifier() == $item->getItemIdentifier()) {
                return true;
            }
        }

        return false;
    }

    public function save()
    {
        $db = Loader::db();
        $id = $db->getOne('select uuid()');
        $db->query('insert into MigrationExportObjectCollections (batch_id, id, type) values (?, ?, ?)', array($this->getBatchId(), $id, $this->getType()));
        $this->id = $id;
    }

    public function delete()
    {
        $items = $this->getItems();
        foreach($items as $item) {
            $item->delete();
        }

        $db = Loader::db();
        $db->query('delete from MigrationExportObjectCollections where id = ?', array($this->getID()));
    }



}
