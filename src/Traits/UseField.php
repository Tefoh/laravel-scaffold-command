<?php

namespace Scaffolding\Traits;

trait UseField
{
	public $fullFields = [];
	public $fieldArray = [];
	public $fieldsArray = [];

	public function getStringColumns(array $field = [], string $fields = ''): string
	{
		$this->getAllFields($field, $fields);

		if (count($this->fullFields) > 0) {
			return "'".implode("',\n\t\t'", $this->columnKeys())."',";
		}

		return '';
	}

	public function columnKeys(): array
	{
		return array_keys($this->fullFields);
	}

	public function getAllFields($field, $fields)
	{
		$this->setField($field, 'fieldArray');
		$this->setField(explode(',', $fields), 'fieldsArray');

		$this->fullFields = array_merge($this->fieldArray, $this->fieldsArray);

		return $this->fullFields;
	}

	public function setField(array $option, string $fieldName): void
	{
		if (count($option) < 1) {
			return;
		}

		foreach ($option as $field) {
			[$key, $value] = explode(':', $field);
			$this->{$fieldName}[$key] = $value;
		}

		if (count($this->{$fieldName}) < 2) {
			return;
		}

		$this->{$fieldName} = array_filter(
			$this->{$fieldName},
			function ($key) use ($fieldName) {
				return !in_array($key, $this->{$fieldName});
			},
			ARRAY_FILTER_USE_KEY
		);
	}
}
