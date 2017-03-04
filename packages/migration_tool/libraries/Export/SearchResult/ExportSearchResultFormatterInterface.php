<?php

defined('C5_EXECUTE') or die("Access Denied.");

interface ExportSearchResultFormatterInterface
{
    public function hasSearchForm();
    public function hasSearchResults();
    public function displaySearchForm();
    public function getRequest();
    public function displaySearchResults();
    public function displayBatchResults();
}
