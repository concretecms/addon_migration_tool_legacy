<?php

class ExportManager
{
    protected function getDriverArray()
    {
        return array(
            'page',
            'stack',
            'user',
            'file',
            'file_set',
        );
    }

    public function driver($handle)
    {
        foreach ($this->getDrivers() as $driver) {
            if ($driver->getHandle() == $handle) {
                return $driver;
            }
        }
    }

    public function getDrivers()
    {
        Loader::library('Export/Type/FileSetExportType');
        Loader::library('Export/Type/FileExportType');
        $drivers = array();
        foreach ($this->getDriverArray() as $driver) {

            $class = ConcreteObject::camelcase($driver).'ExportType';
            $drivers[] = new $class();
        }

        return $drivers;
    }
}
    