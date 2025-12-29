<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SimpleArrayExport implements FromArray, WithHeadings
{
    protected array $data;
    protected array $headings;

    public function __construct(array $data, ?array $headings = null)
    {
        $this->data = $data;
        $this->headings = $headings ?? (count($data) > 0 ? array_keys($data[0]) : []);
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
