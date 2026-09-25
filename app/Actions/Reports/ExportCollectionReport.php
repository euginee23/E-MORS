<?php

namespace App\Actions\Reports;

use App\Enums\PaymentStatus;
use App\Models\Collection;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Builds the admin collection report as a formatted .xlsx workbook.
 *
 * Four sheets: Summary, Collections (the row-level ledger), By Section, and Overdue.
 */
class ExportCollectionReport
{
    private const HEADER_FILL = 'FFF97316';

    private const MONEY_FORMAT = '#,##0.00';

    public function __construct(
        private readonly int $marketId,
        private readonly CarbonInterface $start,
        private readonly CarbonInterface $end,
        private readonly string $periodLabel,
    ) {}

    public function build(): Spreadsheet
    {
        $book = new Spreadsheet;
        $book->getProperties()
            ->setCreator('E-MORS')
            ->setTitle('E-MORS Collection Report')
            ->setDescription('Collection report for '.$this->periodLabel);

        $this->buildSummarySheet($book->getActiveSheet());
        $this->buildCollectionsSheet($book->createSheet());
        $this->buildSectionSheet($book->createSheet());
        $this->buildOverdueSheet($book->createSheet());

        $book->setActiveSheetIndex(1);

        return $book;
    }

    public function writeTo(string $path): void
    {
        $book = $this->build();
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
    }

    private function buildSummarySheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Summary');

        $paid = $this->baseQuery()->where('status', PaymentStatus::Paid);
        $totalRevenue = (float) $paid->clone()->sum('amount');
        $paidCount = (int) $paid->clone()->count();
        $totalCount = (int) $this->baseQuery()->count();

        // Business days in the period, Sundays excluded — markets are closed.
        $days = max(1, $this->start->diffInDaysFiltered(
            fn (CarbonInterface $date) => ! $date->isSunday(),
            min($this->end, Carbon::today())
        ) ?: 1);

        $outstanding = (float) Collection::where('market_id', $this->marketId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->sum('amount');

        $sheet->setCellValue('A1', 'E-MORS Collection Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A1:B1');

        $rows = [
            ['Period', $this->periodLabel],
            ['Date Range', $this->start->format('M j, Y').' – '.$this->end->format('M j, Y')],
            ['Generated', now()->format('M j, Y g:i A')],
            [],
            ['Total Revenue', $totalRevenue],
            ['Average Daily Collection', round($totalRevenue / $days, 2)],
            ['Collection Efficiency', $totalCount > 0 ? round(($paidCount / $totalCount) * 100, 1).'%' : '0%'],
            ['Outstanding Balance', $outstanding],
            ['Transactions Recorded', $totalCount],
            ['Transactions Paid', $paidCount],
        ];

        $row = 3;
        foreach ($rows as $entry) {
            if ($entry === []) {
                $row++;

                continue;
            }

            $sheet->setCellValue("A{$row}", $entry[0]);
            $sheet->setCellValue("B{$row}", $entry[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            if (is_float($entry[1])) {
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
            }

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(30);
    }

    private function buildCollectionsSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Collections');

        $headers = ['#', 'Receipt No.', 'Date', 'Vendor', 'Stall', 'Section', 'Amount', 'Method', 'Reference No.', 'Collector', 'Status'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, 'A1:K1');

        $row = 2;
        $index = 1;

        $this->baseQuery()
            ->with(['vendor', 'stall', 'collector'])
            ->orderBy('payment_date')
            ->chunk(200, function ($collections) use ($sheet, &$row, &$index) {
                foreach ($collections as $collection) {
                    $sheet->fromArray([
                        $index,
                        $collection->receipt_number,
                        $collection->payment_date?->format('Y-m-d'),
                        $collection->vendor?->contact_name ?? '',
                        $collection->stall?->stall_number ?? '',
                        $collection->stall?->section ?? '',
                        (float) $collection->amount,
                        ucfirst(str_replace('_', ' ', $collection->payment_method)),
                        null,
                        $collection->collector?->name ?? '',
                        $collection->status->label(),
                    ], null, "A{$row}");

                    // Written as text: an all-digit GCash reference would otherwise
                    // become a number and lose its leading zeros or precision.
                    if ($collection->reference_number) {
                        $sheet->setCellValueExplicit("I{$row}", $collection->reference_number, DataType::TYPE_STRING);
                    }

                    $row++;
                    $index++;
                }
            });

        $lastDataRow = $row - 1;

        if ($lastDataRow >= 2) {
            $sheet->getStyle("G2:G{$lastDataRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
            $sheet->getStyle("A2:K{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Total row, as a live SUM so the figure survives edits in Excel.
            $totalRow = $lastDataRow + 1;
            $sheet->setCellValue("F{$totalRow}", 'TOTAL');
            $sheet->setCellValue("G{$totalRow}", "=SUM(G2:G{$lastDataRow})");
            $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getFont()->setBold(true);
            $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
            $sheet->getStyle("A{$totalRow}:K{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

            $sheet->setAutoFilter("A1:K{$lastDataRow}");
        } else {
            $sheet->setCellValue('A2', 'No collections recorded for this period.');
        }

        $sheet->freezePane('A2');
        $this->autoSize($sheet, 'A', 'K');
    }

    private function buildSectionSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('By Section');

        $sections = Collection::where('collections.market_id', $this->marketId)
            ->where('collections.status', PaymentStatus::Paid)
            ->whereBetween('collections.payment_date', [$this->start, $this->end])
            ->join('stalls', 'collections.stall_id', '=', 'stalls.id')
            ->selectRaw('stalls.section, COUNT(*) as txn_count, SUM(collections.amount) as total')
            ->groupBy('stalls.section')
            ->orderBy('stalls.section')
            ->get();

        $grandTotal = (float) $sections->sum('total');

        $sheet->fromArray(['Section', 'Transactions', 'Total Collected', 'Share'], null, 'A1');
        $this->styleHeaderRow($sheet, 'A1:D1');

        $row = 2;
        foreach ($sections as $section) {
            $sheet->fromArray([
                'Section '.$section->section,
                (int) $section->txn_count,
                (float) $section->total,
                $grandTotal > 0 ? round(((float) $section->total / $grandTotal) * 100, 1).'%' : '0%',
            ], null, "A{$row}");
            $row++;
        }

        $lastDataRow = $row - 1;

        if ($lastDataRow >= 2) {
            $sheet->getStyle("C2:C{$lastDataRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
            $sheet->getStyle("A2:D{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $totalRow = $lastDataRow + 1;
            $sheet->setCellValue("A{$totalRow}", 'TOTAL');
            $sheet->setCellValue("C{$totalRow}", "=SUM(C2:C{$lastDataRow})");
            $sheet->getStyle("A{$totalRow}:D{$totalRow}")->getFont()->setBold(true);
            $sheet->getStyle("C{$totalRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
        } else {
            $sheet->setCellValue('A2', 'No paid collections for this period.');
        }

        $sheet->freezePane('A2');
        $this->autoSize($sheet, 'A', 'D');
    }

    private function buildOverdueSheet(Worksheet $sheet): void
    {
        $sheet->setTitle('Overdue');

        $sheet->fromArray(['Vendor', 'Stall', 'Receipt No.', 'Due Date', 'Days Overdue', 'Amount'], null, 'A1');
        $this->styleHeaderRow($sheet, 'A1:F1');

        // Outstanding items are not period-bound — an old debt still needs chasing.
        $overdue = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Overdue)
            ->with(['vendor', 'stall'])
            ->orderBy('payment_date')
            ->get();

        $row = 2;
        foreach ($overdue as $collection) {
            $sheet->fromArray([
                $collection->vendor?->contact_name ?? '—',
                $collection->stall?->stall_number ?? '—',
                $collection->receipt_number,
                $collection->payment_date?->format('Y-m-d'),
                $collection->payment_date ? (int) $collection->payment_date->diffInDays(Carbon::today()) : 0,
                (float) $collection->amount,
            ], null, "A{$row}");
            $row++;
        }

        $lastDataRow = $row - 1;

        if ($lastDataRow >= 2) {
            $sheet->getStyle("F2:F{$lastDataRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
            $sheet->getStyle("A2:F{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $totalRow = $lastDataRow + 1;
            $sheet->setCellValue("E{$totalRow}", 'TOTAL');
            $sheet->setCellValue("F{$totalRow}", "=SUM(F2:F{$lastDataRow})");
            $sheet->getStyle("E{$totalRow}:F{$totalRow}")->getFont()->setBold(true);
            $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
        } else {
            $sheet->setCellValue('A2', 'No overdue payments. ');
        }

        $sheet->freezePane('A2');
        $this->autoSize($sheet, 'A', 'F');
    }

    private function baseQuery()
    {
        return Collection::where('market_id', $this->marketId)
            ->whereBetween('payment_date', [$this->start, $this->end]);
    }

    private function styleHeaderRow(Worksheet $sheet, string $range): void
    {
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function autoSize(Worksheet $sheet, string $from, string $to): void
    {
        foreach (range($from, $to) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
