<?php

defined('C5_EXECUTE') or die('Access Denied.');

class StackExportType extends \AbstractExportType
{
    public function getHeaders()
    {
        return array(
            t('Type'),
            t('Name'),
        );
    }

    public function exportCollection($collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('stacks');
        foreach ($collection->getItems() as $item) {
            $c = Stack::getByID($item->getItemIdentifier());
            if (is_object($c) && !$c->isError()) {
                $c->export($node);
            }
        }
    }

    public function getResultColumns(MigrationBatchItem $exportItem)
    {
        $c = Stack::getByID($exportItem->getItemIdentifier());
        switch ($c->getStackType()) {
            case Stack::ST_TYPE_GLOBAL_AREA:
                $type = t('Global Area');
                break;
            default:
                $type = t('Stack');
                break;
        }

        return array(
            $type,
            $c->getCollectionName(),
        );
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $page = false;
            $c = Stack::getByID($id);
            if (is_object($c) && !$c->isError()) {
                $page = new MigrationBatchItem();
                $page->setType('stack');
                $page->setItemId($c->getCollectionID());
            }
            if (is_object($page)) {
                $items[] = $page;
            }
        }

        return $items;
    }

    public function getResults($request)
    {
        $list = new StackList();
        $list->setItemsPerPage(1000);
        $stacks = $list->getPage();
        $items = array();
        foreach ($stacks as $stack) {
            $item = new MigrationBatchItem();
            $item->setType('stack');
            $item->setItemId($stack->getCollectionID());
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'stack';
    }

    public function getPluralDisplayName()
    {
        return t('Stacks');
    }
}
