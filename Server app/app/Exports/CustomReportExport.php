<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;

class CustomReportExport implements WithMultipleSheets
{
    protected $countersData;
    protected $sellsData;
    protected $startDate;
    protected $endDate;

    public function __construct($countersData, $sellsData, $startDate, $endDate)
    {
        $this->countersData = $countersData;
        $this->sellsData = $sellsData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        
        // Debug log
        \Log::info('CustomReportExport - Constructor:', [
            'sellsData_type' => gettype($sellsData),
            'sellsData_count' => is_array($sellsData) ? count($sellsData) : (is_object($sellsData) && method_exists($sellsData, 'count') ? $sellsData->count() : 'unknown'),
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function sheets(): array
    {
        $sheets = [];
        $sellsByDay = [];
        
        \Log::info('CustomReportExport - Datos recibidos:', [
            'sellsData_type' => gettype($this->sellsData),
            'sellsData_count' => is_array($this->sellsData) ? count($this->sellsData) : 'not countable',
            'first_item_sample' => is_array($this->sellsData) && !empty($this->sellsData) ? 
                [
                    'id' => isset($this->sellsData[0]['id']) ? $this->sellsData[0]['id'] : 'no id',
                    'total' => isset($this->sellsData[0]['total']) ? $this->sellsData[0]['total'] : 'no total',
                    'created_at' => isset($this->sellsData[0]['created_at']) ? $this->sellsData[0]['created_at'] : 'no date'
                ] : 'no first item'
        ]);
        
        // Los datos ya vienen como array desde el controller
        $sellsArray = $this->sellsData;
        
        if (!is_array($sellsArray)) {
            \Log::error('CustomReportExport - Datos no son array:', [
                'type' => gettype($sellsArray),
                'data' => $sellsArray
            ]);
            $sellsArray = [];
        }
        
        foreach ($sellsArray as $sell) {
            // Los datos ya vienen como array asociativo desde la base de datos
            $createdAt = isset($sell['created_at']) ? $sell['created_at'] : null;
            
            if ($createdAt) {
                try {
                    $day = Carbon::parse($createdAt)->format('Y-m-d');
                    if (!isset($sellsByDay[$day])) {
                        $sellsByDay[$day] = [];
                    }
                    $sellsByDay[$day][] = $sell;
                } catch (\Exception $e) {
                    \Log::warning('CustomReportExport - Error parsing date:', [
                        'created_at' => $createdAt,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('CustomReportExport - Venta sin fecha:', [
                    'sell_data' => $sell
                ]);
            }
        }
        
        // Crear hoja para cada día
        $dayCounter = 1;
        foreach ($sellsByDay as $day => $sells) {
            $dayName = Carbon::parse($day)->locale('es')->isoFormat('dddd DD [de] MMMM [de] YYYY');
            $sheets["Día $dayCounter"] = new DaySheet($sells, $day, $dayName, $dayCounter);
            $dayCounter++;
        }
        
        // Si no hay datos, crear una hoja con mensaje
        if (empty($sheets)) {
            \Log::warning('CustomReportExport - No hay datos para mostrar');
            $sheets['Sin Datos'] = new EmptySheet($this->startDate, $this->endDate);
        }
        
        \Log::info('CustomReportExport - Hojas creadas:', [
            'sheets_count' => count($sheets),
            'days_with_data' => count($sellsByDay),
            'total_sells_processed' => count($sellsArray)
        ]);
        
        return $sheets;
    }
}

class DaySheet implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $sells;
    protected $day;
    protected $dayName;
    protected $dayNumber;

    public function __construct($sells, $day, $dayName, $dayNumber)
    {
        $this->sells = $sells;
        $this->day = $day;
        $this->dayName = $dayName;
        $this->dayNumber = $dayNumber;
    }

    public function title(): string
    {
        return "Día {$this->dayNumber}";
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE VENTAS - ' . strtoupper($this->dayName)], // Título principal
            [''], // Línea vacía
            ['ID', 'TOTAL', 'Hora de la Venta', 'Última Actualización']
        ];
    }

    public function array(): array
    {
        $data = [
            [], // Para el título
            [], // Línea vacía después del título
            []  // Para los headers
        ];
        
        $totalDay = 0;
        
        \Log::info('DaySheet - Procesando ventas del día:', [
            'day' => $this->day,
            'sells_count' => count($this->sells),
            'first_sell' => !empty($this->sells) ? $this->sells[0] : 'no sells'
        ]);
        
        foreach ($this->sells as $sell) {
            // Los datos ya vienen como array directamente de la base de datos
            $total = floatval($sell['total'] ?? 0);
            $id = $sell['id'] ?? '';
            $createdAt = $sell['created_at'] ?? '';
            $updatedAt = $sell['updated_at'] ?? '';
            
            $totalDay += $total;
            
            $data[] = [
                $id,
                '$' . number_format($total, 0, ',', '.'),
                $createdAt ? Carbon::parse($createdAt)->format('H:i:s') : '',
                $updatedAt ? Carbon::parse($updatedAt)->format('d/m/Y H:i:s') : ''
            ];
        }
        
        // Líneas vacías antes del total
        $data[] = [''];
        $data[] = [''];
        
        // Total del día
        $data[] = [
            '📊 TOTAL DEL DÍA:',
            '$' . number_format($totalDay, 0, ',', '.'),
            count($this->sells) . ' ventas',
            $this->dayName
        ];
        
        \Log::info('DaySheet - Datos procesados:', [
            'day' => $this->day,
            'total_rows' => count($data),
            'total_sells' => count($this->sells),
            'total_amount' => $totalDay
        ]);
        
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // ID
            'B' => 20,  // TOTAL
            'C' => 25,  // Hora de la Venta
            'D' => 30   // Última Actualización
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        return [
            // Título principal (fila 1)
            '1' => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E86AB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            
            // Headers (fila 3)
            '3' => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4CAF50']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            
            // Total del día (última fila)
            $lastRow => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF9800']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            
            // Bordes para toda la tabla
            'A1:D' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            
            // Alineación para columnas específicas
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // ID centrado
            'B:B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]],  // Total derecha
            'C:C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Hora centrada
            'D:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]  // Fecha centrada
        ];
    }
}

class EmptySheet implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return "Sin Datos";
    }

    public function headings(): array
    {
        return [
            ['📋 NO HAY VENTAS EN EL PERÍODO SELECCIONADO'],
            [''],
            ['Período:', Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y')],
            [''],
            ['💡 Sugerencias:'],
            ['• Verifica las fechas seleccionadas'],
            ['• Asegúrate de que haya ventas en ese período'],
            ['• Contacta al administrador si el problema persiste']
        ];
    }

    public function array(): array
    {
        return [
            [], [], [], [], [], [], [], []
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            '1' => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F44336']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ],
            '3' => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
            '5' => [
                'font' => ['bold' => true, 'color' => ['rgb' => '2196F3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }
}

