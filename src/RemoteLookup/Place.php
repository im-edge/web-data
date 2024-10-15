<?php

namespace IMEdge\Web\Data\RemoteLookup;

use gipfl\ZfDb\Select;
use IMEdge\Web\Data\ForeignModel\ZipCode;
use IMEdge\Web\Select2\BaseSelect2Lookup;

class Place extends BaseSelect2Lookup
{
    protected string $table = ZipCode::TABLE_NAME;
    protected array $searchColumns = [
        'place',
        'zip',
    ];

    // protected string $defaultCountryCode = 'DE';
    // protected string $defaultState = 'Bayern';
    protected string $defaultCountryCode = 'IT';
    protected string $defaultState = 'Trentino-Alto Adige';

    protected function select(?array $columns = null): Select
    {
        return parent::select($columns)
            ->order($this->db->quoteInto("CASE WHEN country_code = ? THEN 1 ELSE 2 END", $this->defaultCountryCode))
            ->order($this->db->quoteInto("CASE WHEN state = ? THEN 1 ELSE 2 END", $this->defaultState));
    }

    protected function getSelectColumns(): array
    {
        return ['zip', 'place', 'state', 'country_code'];
    }

    protected function prepareQuery(): Select
    {
        return $this->eventuallySearch($this->select())->order('place');
    }

    protected function getFormattedId($row): string
    {
        return sprintf('%s %s (%s)', $row->zip, $row->place, $row->country_code);
    }

    protected function getFormattedCompactText($row): string
    {
        return sprintf('%s %s', $row->zip, $row->place);
    }

    protected function getFormattedText($row): string
    {
        return sprintf('%s %s, %s (%s)', $row->zip, $row->place, $row->state, $row->country_code);
    }

    protected function getFormattedGroup($row): string
    {
        return sprintf('%s (%s)', $row->state, $row->country_code);
    }

    protected function prepareResult($rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $value = $this->getFormattedId($row);
            $group = $this->getFormattedGroup($row);
            $text = $this->getFormattedCompactText($row);
            if (! isset($result[$group])) {
                $result[$group] = [
                    'id'   => $group,
                    'text' => $group,
                    'children' => []
                ];
            }
            $result[$group]['children'][] = [
                'id'   => $value,
                'text' => $text,
            ];
        }

        return array_values($result);
    }

    protected function searchInQuery(Select $query): Select
    {
        // Overriding parent method, special for ZIP code
        $search = $this->searchString;
        $query->where('place LIKE ?', "%$search%");
        if (preg_match('/(\d{2,})/', $search, $match)) {
            $this->applySearchOrder($query, 'zip', $search);
            $query->orWhere('zip LIKE ?', $match[1] . '%');
        }
        $query->order('zip');
        $this->applySearchOrder($query, 'place', $search);

        return $query;
    }

    protected function filterId(Select $query, $id): Select
    {
        $parts = explode(' ', $id, 2);
        if (count($parts) !== 2) {
            throw new \RuntimeException("Cannot filter places by '$id'");
        }

        return $query->where('zip = ?', $parts[0])->where('place = ?', $parts[1]);
    }
}
