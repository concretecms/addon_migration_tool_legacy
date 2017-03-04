<?php

class ExportManager
{
	protected function getDriverArray()
	{
		return array(
			'page'
		);
	}

	public function getDrivers()
	{
		$drivers = array();
		foreach($this->getDriverArray() as $driver) {
			$class = Object::camelcase($driver) . 'ExportType';
			$drivers[] = new $class();
		}
		return $drivers;
	}

}