<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class QuestionsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * البيانات
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * العناوين
     */
    public function headings(): array
    {
        return [
            'type',
            'title',
            'content',
            'difficulty',
            'points',
            'category',
            'option1',
            'option1_correct',
            'option2',
            'option2_correct',
            'option3',
            'option3_correct',
            'option4',
            'option4_correct',
            'correct_answer',
            'tolerance',
            'case_sensitive',
        ];
    }

    /**
     * تنسيق الأعمدة
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // type
            'B' => 30,  // title
            'C' => 30,  // content
            'D' => 12,  // difficulty
            'E' => 10,  // points
            'F' => 15,  // category
            'G' => 25,  // option1
            'H' => 15,  // option1_correct
            'I' => 25,  // option2
            'J' => 15,  // option2_correct
            'K' => 25,  // option3
            'L' => 15,  // option3_correct
            'M' => 25,  // option4
            'N' => 15,  // option4_correct
            'O' => 15,  // correct_answer
            'P' => 12,  // tolerance
            'Q' => 15,  // case_sensitive
        ];
    }

    /**
     * تنسيق الخلايا
     */
    public function styles(Worksheet $sheet)
    {
        // تنسيق رأس الجدول
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // تنسيق الصفوف
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ]);
        }

        // جعل الصف الأول ثابت
        $sheet->freezePane('A2');

        return $sheet;
    }
}
