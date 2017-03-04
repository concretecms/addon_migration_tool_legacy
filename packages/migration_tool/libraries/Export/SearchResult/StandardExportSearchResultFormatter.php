<?php

defined('C5_EXECUTE') or die("Access Denied.");

class StandardExportSearchResultFormatter implements ExportSearchResultFormatterInterface
{
    protected $itemType;
    protected $batch;
    protected $collection;
    protected $request;

    public function __construct(StandardExportSearchResultFormatterInterface $type, MigrationBatch $batch)
    {
        $this->itemType = $type;
        $this->batch = $batch;
        $this->collection = $this->batch->getObjectCollection($type->getHandle());
        $this->request = $_REQUEST;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function hasSearchForm()
    {
        $env = Environment::get();
        $rec = $env->getRecord(
            DIRNAME_ELEMENTS . '/export/search/' . $this->itemType->getHandle() . '.php',
            'migration_tool');

        return $rec->exists();
    }

    public function hasSearchResults()
    {
        if (!isset($this->request)) {
            throw new \RuntimeException(t('Request must be passed to the StandardFormatter'));
        }

        $request = $this->getRequest();

        if (!$this->hasSearchForm()) {
            return true;
        } elseif (!empty($request['search_form_submit'])) {
            return true;
        }
    }

    public function displaySearchForm()
    {
        echo Loader::element('export/search/' . $this->itemType->getHandle(), array(
            'formatter' => $this,
            'batch' => $this->batch,
            'collection' => $this->collection,
            'type' => $this->itemType,
        ), 'migration_tool');
    }

    public function displayBatchResults()
    {
        echo Loader::element('export/results/standard_list', array(
            'formatter' => $this,
            'batch' => $this->batch,
            'collection' => $this->collection,
            'mode' => 'results',
            'type' => $this->itemType,
            'headers' => $this->itemType->getHeaders(),
            'results' => $this->collection->getItems(),
        ), 'migration_tool');
    }

    public function displaySearchResults()
    {
        echo Loader::element('export/results/standard_list', array(
            'formatter' => $this,
            'batch' => $this->batch,
            'collection' => $this->collection,
            'mode' => 'search',
            'type' => $this->itemType,
            'headers' => $this->itemType->getHeaders(),
            'results' => $this->itemType->getResults($this->getRequest()),
        ), 'migration_tool');
    }
}
