<?
defined('C5_EXECUTE') or die(_("Access Denied."));

class MigrationBatchItem
{

    protected $item_identifier;
    protected $identifier;
    protected $collection_id;
    protected $type;
    protected $item_id;
    protected $item_handle;

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getCollectionId()
    {
        return $this->collection_id;
    }

    /**
     * @param mixed $collection_id
     */
    public function setCollectionId($collection_id)
    {
        $this->collection_id = $collection_id;
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
     * @return mixed
     */
    public function getItemId()
    {
        return $this->item_id;
    }

    /**
     * @param mixed $item_id
     */
    public function setItemId($item_id)
    {
        $this->item_id = $item_id;
    }

    /**
     * @return mixed
     */
    public function getItemHandle()
    {
        return $this->item_handle;
    }

    /**
     * @param mixed $item_handle
     */
    public function setItemHandle($item_handle)
    {
        $this->item_handle = $item_handle;
    }

    /**
     * @return mixed
     */
    public function getItemIdentifier()
    {
        if (isset($this->item_identifier)) {
            return $this->item_identifier;
        }
        return $this->item_id;
    }

    /**
     * @param mixed $item_identifier
     */
    public function setItemIdentifier($item_identifier)
    {
        $this->item_identifier = $item_identifier;
    }


    

}
