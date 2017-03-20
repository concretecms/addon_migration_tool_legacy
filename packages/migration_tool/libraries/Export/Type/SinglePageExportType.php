<?php

defined('C5_EXECUTE') or die('Access Denied.');

class SinglePageExportType extends AbstractExportType
{
    public function getHeaders()
    {
        return array(
            t('Path'),
            t('Name'),
        );
    }

    public function exportCollection($collection, \SimpleXMLElement $element)
    {
        $node = $element->addChild('singlepages');
        foreach ($collection->getItems() as $page) {
            $c = \Page::getByID($page->getItemIdentifier());
            if (is_object($c) && !$c->isError()) {
                $c->export($node);
            }
        }
    }

    public function getResultColumns(MigrationBatchItem $exportItem)
    {
        $c = \Page::getByID($exportItem->getItemIdentifier());

        return array(
            $c->getCollectionPath(),
            $c->getCollectionName(),
        );
    }

    public function getItemsFromRequest($array)
    {
        $items = array();
        foreach ($array as $id) {
            $c = \Page::getByID($id);
            if (is_object($c) && !$c->isError()) {
                $page = new MigrationBatchItem();
                $page->setType('page');
                $page->setItemId($c->getCollectionID());
                $items[] = $page;
            }
        }

        return $items;
    }

    public function getResults($request)
    {
        $db = \Database::connection();
        $r = $db->Execute('select cID from Pages where cFilename is not null and cFilename <> "" and cID not in (select cID from Stacks) order by cID asc');
        $items = array();
        while ($row = $r->FetchRow()) {
            $item = new \PortlandLabs\Concrete5\MigrationTool\Entity\Export\SinglePage();
            $item->setItemId($row['cID']);
            $items[] = $item;
        }

        return $items;
    }

    public function getHandle()
    {
        return 'single_page';
    }

    public function getPluralDisplayName()
    {
        return t('Single Pages');
    }
}
