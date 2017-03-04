<?php

defined('C5_EXECUTE') or die("Access Denied.");

interface StandardExportSearchResultFormatterInterface
{
    public function getHandle();

    public function getHeaders();

    public function getResults(Request $request);

    public function getResultColumns(ExportItem $item);
}
