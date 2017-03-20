<?php

defined('C5_EXECUTE') or die('Access Denied.');

interface StandardExportSearchResultFormatterInterface
{
    public function getHandle();

    public function getHeaders();

    public function getResults($request);

    public function getResultColumns(MigrationBatchItem $item);
}
