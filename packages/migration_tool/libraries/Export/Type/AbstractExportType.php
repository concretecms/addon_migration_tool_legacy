<?php

defined('C5_EXECUTE') or die('Access Denied.');

abstract class AbstractExportType implements ExportTypeInterface, StandardExportSearchResultFormatterInterface
{
    public function getResultsFormatter(MigrationBatch $batch)
    {
        return new StandardExportSearchResultFormatter($this, $batch);
    }
}
