<?php

namespace hiapi\rcptraf\collectors;

use DateTime;

class FileParser
{
    const AGGREGATION_MAX   = 'max';
    const AGGREGATION_MIN   = 'min';
    const AGGREGATION_SUM   = 'sum';
    const AGGREGATION_LAST  = 'last';
    const AGGREGATION_FIRST = 'first';

    protected $data = [];

    protected $keys;

    protected $fields;

    protected $aggregation;

    public function __construct($keys, $fields, $aggregation)
    {
        $this->keys = $keys;
        $this->fields = $fields;
        $this->aggregation = $aggregation;
    }

    public function parse($path)
    {
        foreach (file($path) as $entry) {
            $items = preg_split('/\s+/', trim($entry));
            $date = array_shift($items);
            $keys = [];
            foreach ($this->keys as $key) {
                $keys[$key] = array_shift($items);
            }
            $key = implode(' ', $keys);
            foreach ($this->fields as $field) {
                $this->setValue($key, $date, $field, array_shift($items));
            }
        }
    }

    public function setValue($key, $date, $field, $value)
    {
        $prev = isset($this->data[$key][$date][$field]) ? $this->data[$key][$date][$field] : null;
        $this->data[$key][$date][$field] = $this->aggregate($prev, $value);
    }
    
    protected function aggregate($prev, $curr)
    {
        switch ($this->aggregation) {
            case self::AGGREGATION_SUM:
                return $prev + $curr;
            case self::AGGREGATION_MAX:
                return $curr>$prev ? $curr : $prev;
            case self::AGGREGATION_MIN:
                return $curr<$prev ? $curr : $prev;
            case self::AGGREGATION_FIRST:
                return $prev === null ? $curr : $prev;
            default:
            case self::AGGREGATION_LAST:
                return $curr;
        }
    }

    public function getAllValues()
    {
        return $this->data;
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function getValue($key, $date, $field)
    {
        return isset($this->data[$key][$date][$field]) ? $this->data[$key][$date][$field] : null;
    }

    public function getValues($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : [];
    }
}
