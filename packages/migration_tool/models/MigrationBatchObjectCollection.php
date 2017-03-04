<?
defined('C5_EXECUTE') or die(_("Access Denied."));

class MigrationBatchObjectCollection
{

    protected $id;
    protected $type;
    protected $items = array();

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


}
