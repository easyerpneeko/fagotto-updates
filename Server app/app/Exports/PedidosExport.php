<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PedidosExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    protected $pedidos;
    protected $sucursalName;
    protected $startDate;
    protected $endDate;
    protected $rowIndex = 1;

    public function __construct($pedidos, $sucursalName, $startDate = null, $endDate = null)
    {
        $this->pedidos = $pedidos;
        $this->sucursalName = $sucursalName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        $rows = [];
        
        foreach ($this->pedidos as $pedido) {
            // Convertir el pedido a array si es objeto
            if (is_object($pedido)) {
                $pedido = (array) $pedido;
            }
            
            $productos = json_decode($pedido['products'] ?? '[]', true) ?? [];
            
            if (empty($productos)) {
                // Si no hay productos, crear una fila
                $rows[] = [
                    $pedido['id'] ?? '',
                    isset($pedido['created_at']) ? date('d/m/Y H:i', strtotime($pedido['created_at'])) : '',
                    $pedido['contact_name'] ?? '',
                    $pedido['contact_phone'] ?? '',
                    ucfirst($pedido['status'] ?? ''),
                    ($pedido['emergency'] ?? 0) ? 'SÍ' : 'NO',
                    ($pedido['despacho'] ?? 0) ? 'SÍ' : 'NO',
                    strtoupper($pedido['payment_method'] ?? $pedido['paymode'] ?? ''),
                    strtoupper($pedido['invoice_type'] ?? 'TICKET'),
                    'Sin productos',
                    0,
                    '$0',
                    0,
                    '',
                    '$' . number_format($pedido['subtotal'] ?? 0, 0, ',', '.'),
                    '$' . number_format($pedido['iva'] ?? 0, 0, ',', '.'),
                    '$' . number_format($pedido['price'] ?? 0, 0, ',', '.'),
                    $pedido['comment'] ?? ''
                ];
            } else {
                // Crear una fila por cada producto
                foreach ($productos as $index => $producto) {
                    $rows[] = [
                        $index === 0 ? ($pedido['id'] ?? '') : '', // Solo mostrar ID en la primera fila
                        $index === 0 ? (isset($pedido['created_at']) ? date('d/m/Y H:i', strtotime($pedido['created_at'])) : '') : '',
                        $index === 0 ? ($pedido['contact_name'] ?? '') : '',
                        $index === 0 ? ($pedido['contact_phone'] ?? '') : '',
                        $index === 0 ? ucfirst($pedido['status'] ?? '') : '',
                        $index === 0 ? (($pedido['emergency'] ?? 0) ? 'SÍ' : 'NO') : '',
                        $index === 0 ? (($pedido['despacho'] ?? 0) ? 'SÍ' : 'NO') : '',
                        $index === 0 ? strtoupper($pedido['payment_method'] ?? $pedido['paymode'] ?? '') : '',
                        $index === 0 ? strtoupper($pedido['invoice_type'] ?? 'TICKET') : '',
                        $producto['name'] ?? 'Producto sin nombre',
                        $producto['quantity'] ?? 0,
                        '$' . number_format($producto['price'] ?? 0, 0, ',', '.'),
                        $producto['vasos'] ?? 0,
                        $producto['comment'] ?? '',
                        $index === 0 ? ('$' . number_format($pedido['subtotal'] ?? 0, 0, ',', '.')) : '',
                        $index === 0 ? ('$' . number_format($pedido['iva'] ?? 0, 0, ',', '.')) : '',
                        $index === 0 ? ('$' . number_format($pedido['price'] ?? 0, 0, ',', '.')) : '',
                        $index === 0 ? ($pedido['comment'] ?? '') : ''
                    ];
                }
            }
        }
        
        return $rows;
    }

    public function title(): string
    {
        return 'Pedidos ' . $this->sucursalName;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Cliente',
            'Teléfono',
            'Estado',
            'Emergencia',
            'Despacho',
            'Método Pago',
            'Tipo Factura',
            'Producto',
            'Cantidad',
            'Precio Unitario',
            'Vasos',
            'Comentario Producto',
            'Subtotal',
            'IVA',
            'Total',
            'Comentario General'
        ];
    }



    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 18,  // Fecha
            'C' => 25,  // Cliente
            'D' => 15,  // Teléfono
            'E' => 12,  // Estado
            'F' => 12,  // Emergencia
            'G' => 12,  // Despacho
            'H' => 15,  // Método Pago
            'I' => 12,  // Tipo Factura
            'J' => 30,  // Producto
            'K' => 10,  // Cantidad
            'L' => 15,  // Precio Unit
            'M' => 8,   // Vasos
            'N' => 25,  // Comentario Producto
            'O' => 12,  // Subtotal
            'P' => 12,  // IVA
            'Q' => 12,  // Total
            'R' => 30,  // Comentario General
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para los encabezados
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Estilo para el contenido
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:R' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                ],
            ]);

            // Centrar las columnas numéricas
            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K2:Q' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E2:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto ajuste de altura de filas
        for ($i = 1; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        return [];
    }
}