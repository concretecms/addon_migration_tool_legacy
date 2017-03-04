<?php

defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbstractExportType implements ExportTypeInterface, StandardExportSearchResultFormatterInterface
{
    public function __construct()
    {
//        $this->exporter = new Exporter();
    }

    public function getResultsFormatter(MigrationBatch $batch)
    {
        return new StandardFormatter($this, $batch);
    }
}
