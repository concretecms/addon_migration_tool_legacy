<?php

defined('C5_EXECUTE') or die('Access Denied.');
interface ExportTypeInterface
{
    public function getHandle();
    public function getPluralDisplayName();
    public function getResultsFormatter(MigrationBatch $batch);
    public function getItemsFromRequest($array);
    public function exportCollection($collection, \SimpleXMLElement $element);
}
