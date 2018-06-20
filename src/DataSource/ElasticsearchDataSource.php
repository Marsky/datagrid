<?php

// nástin metody pro pull request do Ublaboo datagridu

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Martin Skyba
 * @author      Petr Martinec
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\Sorting;

class ElasticsearchDataSource extends FilterableDataSource implements IDataSource
{
	/**
	 * @var 
	 */
	protected $data_source = [];


	/**
	 * @param array $data_source
	 */
	public function __construct(\ElasticsearchService $data_source)
	{
		$this->data_source = $data_source;
	}


	/**
	 * Get count of data
	 * @return int
	 */
	public function getCount()
	{
		return $this->data_source->count();
	}


	/**
	 * Get the data
	 * @return array
	 */
	public function getData()
	{
		return $this->data_source->execute()->getSimpleArray();
	}


	/**
	 * Filter data
	 * @param array $filters
	 * @return static
	 */
	// public function filter(array $filters)
	// {
	// 	foreach ($filters as $column => $value) {
	// 		if($value->isValueSet()) {
	// 			$this->data_source->addFilter(array($column => $value->getValue()));
	// 		}
	// 	}

	// 	return $this;	
	// }

	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return void
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		//dump("applyFilterDate");
	}


	/**
	 * Filter by date range
	 * @param  Filter\FilterDateRange $filter
	 * @return void
	 */
	public function applyFilterDateRange(Filter\FilterDateRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		$this->data_source->addDateRange($filter->getColumn(),$value_from,$value_to,"dd.MM.yyyy");
	}


	/**
	 * Filter by range
	 * @param  Filter\FilterRange $filter
	 * @return void
	 */
	public function applyFilterRange(Filter\FilterRange $filter)
	{
		//dump("applyFilterRange");
	}

	/**
	 * Filter by select value
	 * @param  Filter\FilterSelect $filter
	 * @return void
	 */
	public function applyFilterSelect(Filter\FilterSelect $filter)
	{
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$this->data_source->addTerm($column, $value);			
		}
	}

	/**
	 * Filter data - get one row
	 * @param array $filter
	 * @return static
	 */
	public function filterOne(array $filter)
	{
		// foreach ($filters as $column => $value) {
		// 	if($value->isValueSet()) {
		// 		$this->data_source->addFilter(array($column => $value->getValue()));
		// 	}
		// }
		//dump("filterOne");
		return $this;
	}

	/**
	 * Filter by keyword
	 * @param  Filter\FilterText $filter
	 * @return void
	 */
	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();
		foreach ($condition as $column => $value)
		{
			$this->data_source->addCustom([
				'bool' => [
					'should' => [
						['wildcard' => [$column => "*" . strtolower($value) . "*"]],
						['wildcard' => [$column.'.keyword' => "*" . $value . "*"]]
					]
				]
			]);

			// pokud wildcard bude hodne casove narocne -> pouzit jinou z nasledujicich variant
			// $this->data_source->addPreifx($column, strtolower($value));			
			// $this->data_source->addMatch($column, strtolower($value));			
			// $this->data_source->addTerm($column, strtolower($value));			
		}
		return $this;
	}

	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$this->data_source->size = $limit;
		$this->data_source->from = $offset;

		return $this;
	}


	/**
	 * Sort data
	 * @param Sorting $sorting
	 * @return static
	 */
	public function sort(Sorting $sorting)
	{
		foreach ($sorting->sort as $key => $item) {
			if($item !== "0"){
				$this->data_source->addSort($key, strtolower($item));
			}
		}
		return $this;
	}

}
