<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class MigrationToolPackage extends Package
{

    protected $pkgHandle = 'migration_tool';
    protected $appVersionRequired = '5.5.0';
    protected $pkgVersion = '0.6.1';

    public function getPackageDescription()
    {
        return t('Generates content from a 5.5 or greater concrete5 site for import into a modern concrete5 installation.');
    }

    public function getPackageName()
    {
        return t('Migration Tool');
    }

    public function on_start()
    {
        $classes = array(
            'ExportManager' => array('library', 'Export/ExportManager', 'migration_tool'),
            'AbstractExportType' => array('library', 'Export/Type/AbstractExportType', 'migration_tool'),
            'SinglePageExportType' => array('library', 'Export/Type/SinglePageExportType', 'migration_tool'),
            'PageExportType' => array('library', 'Export/Type/PageExportType', 'migration_tool'),
            'StackExportType' => array('library', 'Export/Type/StackExportType', 'migration_tool'),
            'ExportTypeInterface' => array('library', 'Export/Type/ExportTypeInterface', 'migration_tool'),
            'ExportSearchResultFormatterInterface' => array('library', 'Export/SearchResult/ExportSearchResultFormatterInterface', 'migration_tool'),
            'StandardExportSearchResultFormatter' => array('library', 'Export/SearchResult/StandardExportSearchResultFormatter', 'migration_tool'),
            'StandardExportSearchResultFormatterInterface' => array('library', 'Export/SearchResult/StandardExportSearchResultFormatterInterface', 'migration_tool'),
        );
        Loader::registerAutoload($classes);

    }
    public function install()
    {
        $pkg = parent::install();
        Loader::model('single_page');
        SinglePage::add('/dashboard/migration', $pkg);
        SinglePage::add('/dashboard/migration/export', $pkg);
    }

}