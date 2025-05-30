<?php

namespace App\Exports;

use App\Models\VoipRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
class RecordExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithEvents, WithColumnFormatting
{

    protected $records;
    protected $headings = [];
    protected $rowCount = 1;
    protected $columnFormats = [
        'D' => 'yyyy-mm-dd hh:mm:ss', // Datetime format
    ];
    /**
     * Contruct the export with the search parameters.
     */
    public function __construct(Collection $records) {
        $this->records = $records;
        $this->rowCount += $this->records->count();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->records;
    }

    /**
     * Set the headings for the export.
     */
    public function headings(): array {
        return [
            'Direction',
            'Extension',
            'Initiating number',
            'Date and time',
            'Duration HH:MM:SS',
            'Destination',
            'External number',
        ];
    }

    /**
     * Map the records to the export format.
     */
    public function map($record): array {
        $arr = [
            $record->call_direction,
            $record->extension,
            $record->initiator,
            Date::stringToExcel($record->datetime),
            $record->duration,
            $record->destination_number,
            $record->external_number,
        ];

        return $arr;
    }

    /**
     * Event regustration for the export.
     */
    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                //Set header column
                $event->sheet->getDelegate()->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                for ($i = 2; $i <= $this->rowCount; $i++) {
                    $row = "A$i:G$i";
                    $event->sheet->getDelegate()->getStyle($row)->getFont()->setSize(10);
                    $event->sheet->getDelegate()->getStyle($row)->getFont()->setName('Tahoma');
                }
              
            }
        ];
    }

    /**
     * Column formatting for the export.
     */
    public function columnFormats(): array {
        return $this->columnFormats;
    }

}
