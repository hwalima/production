<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\DailyProduction;
use App\Models\LabourEnergy;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    // ── Template definitions ──────────────────────────────────────────────

    private const TEMPLATES = [
        'production' => [
            'filename' => 'production_import_template.csv',
            'headers'  => [
                'date', 'shift', 'mining_site',
                'ore_hoisted', 'ore_hoisted_target',
                'waste_hoisted', 'uncrushed_stockpile',
                'ore_crushed', 'unmilled_stockpile',
                'ore_milled', 'ore_milled_target',
                'gold_smelted', 'purity_percentage', 'fidelity_price',
            ],
            'example' => [
                '2026-04-01', 'Day', 'Main Pit',
                '100.00', '110.00',
                '50.00', '5.00',
                '95.00', '3.00',
                '92.00', '95.00',
                '45.50', '92.00', '3450000.00',
            ],
            'notes' => [
                'date'               => 'YYYY-MM-DD format. Required.',
                'shift'              => 'Day / Night / Afternoon / Morning. Optional.',
                'mining_site'        => 'Site name. Optional.',
                'ore_hoisted'        => 'Tonnes. Required.',
                'ore_hoisted_target' => 'Tonnes. Optional.',
                'waste_hoisted'      => 'Tonnes. Required.',
                'uncrushed_stockpile'=> 'Tonnes. Optional.',
                'ore_crushed'        => 'Tonnes. Required.',
                'unmilled_stockpile' => 'Tonnes. Optional.',
                'ore_milled'         => 'Tonnes. Required.',
                'ore_milled_target'  => 'Tonnes. Optional.',
                'gold_smelted'       => 'Grams. Required.',
                'purity_percentage'  => '0–100. Required.',
                'fidelity_price'     => 'Price per gram in local currency. Required.',
            ],
        ],
        'consumables' => [
            'filename' => 'consumables_import_template.csv',
            'headers'  => [
                'name', 'category', 'description',
                'purchase_unit', 'use_unit', 'units_per_pack',
                'pack_cost', 'reorder_level',
            ],
            'example' => [
                'Drill Bit 38mm', 'mechanical', 'Standard rock drill bit',
                'box', 'each', '12',
                '1200.00', '24',
            ],
            'notes' => [
                'name'          => 'Unique item name. Required.',
                'category'      => 'blasting / chemicals / mechanical / ppe / general. Required.',
                'description'   => 'Optional free text.',
                'purchase_unit' => 'e.g. box / bag / litre. Required.',
                'use_unit'      => 'e.g. each / ml / kg. Required.',
                'units_per_pack'=> 'Number of use_units per purchase_unit. Required.',
                'pack_cost'     => 'Cost per purchase_unit. Required.',
                'reorder_level' => 'Reorder threshold in use_units. Optional (default 0).',
            ],
        ],
        'labour-energy' => [
            'filename' => 'labour_energy_import_template.csv',
            'headers'  => ['date', 'zesa_cost', 'diesel_cost', 'labour_cost'],
            'example'  => ['2026-04-01', '15000.00', '22000.00', '85000.00'],
            'notes'    => [
                'date'        => 'YYYY-MM-DD format. Required.',
                'zesa_cost'   => 'ZESA electricity cost. Required.',
                'diesel_cost' => 'Diesel / generator cost. Required.',
                'labour_cost' => 'Total labour cost. Required.',
            ],
        ],
    ];

    // ── Hub page ──────────────────────────────────────────────────────────

    public function index()
    {
        return view('imports.index');
    }

    public function showProduction()
    {
        return view('imports.production');
    }

    public function showConsumables()
    {
        return view('imports.consumables');
    }

    public function showLabourEnergy()
    {
        return view('imports.labour-energy');
    }

    // ── Template download ─────────────────────────────────────────────────

    public function template(string $type): Response
    {
        $def = self::TEMPLATES[$type] ?? null;
        abort_if($def === null, 404);

        $lines   = [];
        $lines[] = implode(',', $def['headers']);
        $lines[] = implode(',', $def['example']);
        $csv     = implode("\r\n", $lines) . "\r\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $def['filename'] . '"',
        ]);
    }

    // ── Production import ─────────────────────────────────────────────────

    public function importProduction(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:csv,xlsx,xls|max:10240',
        ]);

        try {
            $rows = $this->parseFile($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not read file: ' . $e->getMessage()]);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File must contain a header row and at least one data row.']);
        }

        $map = $this->buildHeaderMap($rows[0], self::TEMPLATES['production']['headers']);

        $required = ['date', 'ore_hoisted', 'waste_hoisted', 'ore_crushed', 'ore_milled', 'gold_smelted', 'purity_percentage', 'fidelity_price'];
        if ($missing = array_diff($required, array_keys($map))) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missing)]);
        }

        $inserted = $updated = 0;
        $errors   = [];

        DB::transaction(function () use ($rows, $map, $required, &$inserted, &$updated, &$errors) {
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNum = $i + 2;
                $g = $this->rowGetter($row, $map);

                // Skip completely blank rows
                if ($this->rowIsBlank($row)) {
                    continue;
                }

                // Validate required fields
                $rowErrors = [];
                foreach ($required as $col) {
                    if ($g($col) === '') {
                        $rowErrors[] = "$col is required";
                    }
                }
                if (!$this->isValidDate($g('date'))) {
                    $rowErrors[] = "date must be YYYY-MM-DD";
                }
                if ($rowErrors) {
                    $errors[] = ['row' => $rowNum, 'message' => implode('; ', $rowErrors)];
                    continue;
                }

                $data = [
                    'date'                 => $g('date'),
                    'shift'                => $g('shift') ?: null,
                    'mining_site'          => $g('mining_site') ?: null,
                    'ore_hoisted'          => $this->num($g('ore_hoisted')),
                    'ore_hoisted_target'   => $this->numOrNull($g('ore_hoisted_target')),
                    'waste_hoisted'        => $this->num($g('waste_hoisted')),
                    'uncrushed_stockpile'  => $this->num($g('uncrushed_stockpile')),
                    'ore_crushed'          => $this->num($g('ore_crushed')),
                    'unmilled_stockpile'   => $this->num($g('unmilled_stockpile')),
                    'ore_milled'           => $this->num($g('ore_milled')),
                    'ore_milled_target'    => $this->numOrNull($g('ore_milled_target')),
                    'gold_smelted'         => $this->num($g('gold_smelted')),
                    'purity_percentage'    => $this->num($g('purity_percentage')),
                    'fidelity_price'       => $this->num($g('fidelity_price')),
                ];

                $existing = DailyProduction::whereDate('date', $data['date'])
                    ->where('shift', $data['shift'])
                    ->first();

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    DailyProduction::create($data);
                    $inserted++;
                }
            }
        });

        AuditLog::create([
            'user_id'    => $request->user()->id,
            'action'     => 'import',
            'model_type' => 'DailyProduction',
            'model_id'   => 0,
            'changes'    => json_encode(['inserted' => $inserted, 'updated' => $updated, 'errors' => count($errors)]),
        ]);

        return redirect()->route('import.production')
            ->with('import_result', compact('inserted', 'updated', 'errors'));
    }

    // ── Consumables import ────────────────────────────────────────────────

    public function importConsumables(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:csv,xlsx,xls|max:10240',
        ]);

        try {
            $rows = $this->parseFile($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not read file: ' . $e->getMessage()]);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File must contain a header row and at least one data row.']);
        }

        $map = $this->buildHeaderMap($rows[0], self::TEMPLATES['consumables']['headers']);

        $required = ['name', 'category', 'purchase_unit', 'use_unit', 'units_per_pack', 'pack_cost'];
        if ($missing = array_diff($required, array_keys($map))) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missing)]);
        }

        $validCategories = ['blasting', 'chemicals', 'mechanical', 'ppe', 'general'];
        $inserted = $updated = 0;
        $errors   = [];

        DB::transaction(function () use ($rows, $map, $required, $validCategories, &$inserted, &$updated, &$errors) {
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNum = $i + 2;
                $g = $this->rowGetter($row, $map);

                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $rowErrors = [];
                foreach ($required as $col) {
                    if ($g($col) === '') {
                        $rowErrors[] = "$col is required";
                    }
                }
                if ($g('category') !== '' && !in_array(strtolower($g('category')), $validCategories)) {
                    $rowErrors[] = "category must be one of: " . implode(', ', $validCategories);
                }
                if ($rowErrors) {
                    $errors[] = ['row' => $rowNum, 'message' => implode('; ', $rowErrors)];
                    continue;
                }

                $data = [
                    'name'           => $g('name'),
                    'category'       => strtolower($g('category')),
                    'description'    => $g('description') ?: null,
                    'purchase_unit'  => $g('purchase_unit'),
                    'use_unit'       => $g('use_unit'),
                    'units_per_pack' => $this->num($g('units_per_pack')),
                    'pack_cost'      => $this->num($g('pack_cost')),
                    'reorder_level'  => $this->numOrNull($g('reorder_level')) ?? 0,
                    'is_active'      => true,
                ];

                $existing = Consumable::where('name', $data['name'])->first();
                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Consumable::create($data);
                    $inserted++;
                }
            }
        });

        AuditLog::create([
            'user_id'    => $request->user()->id,
            'action'     => 'import',
            'model_type' => 'Consumable',
            'model_id'   => 0,
            'changes'    => json_encode(['inserted' => $inserted, 'updated' => $updated, 'errors' => count($errors)]),
        ]);

        return redirect()->route('import.consumables')
            ->with('import_result', compact('inserted', 'updated', 'errors'));
    }

    // ── Labour / Energy import ────────────────────────────────────────────

    public function importLabourEnergy(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:csv,xlsx,xls|max:10240',
        ]);

        try {
            $rows = $this->parseFile($request->file('file'));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not read file: ' . $e->getMessage()]);
        }

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'File must contain a header row and at least one data row.']);
        }

        $map = $this->buildHeaderMap($rows[0], self::TEMPLATES['labour-energy']['headers']);

        $required = ['date', 'zesa_cost', 'diesel_cost', 'labour_cost'];
        if ($missing = array_diff($required, array_keys($map))) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missing)]);
        }

        $inserted = $updated = 0;
        $errors   = [];

        DB::transaction(function () use ($rows, $map, $required, &$inserted, &$updated, &$errors) {
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNum = $i + 2;
                $g = $this->rowGetter($row, $map);

                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $rowErrors = [];
                foreach ($required as $col) {
                    if ($g($col) === '') {
                        $rowErrors[] = "$col is required";
                    }
                }
                if (!$this->isValidDate($g('date'))) {
                    $rowErrors[] = "date must be YYYY-MM-DD";
                }
                if ($rowErrors) {
                    $errors[] = ['row' => $rowNum, 'message' => implode('; ', $rowErrors)];
                    continue;
                }

                $data = [
                    'date'        => $g('date'),
                    'zesa_cost'   => $this->num($g('zesa_cost')),
                    'diesel_cost' => $this->num($g('diesel_cost')),
                    'labour_cost' => $this->num($g('labour_cost')),
                ];

                $existing = LabourEnergy::whereDate('date', $data['date'])->first();
                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    LabourEnergy::create($data);
                    $inserted++;
                }
            }
        });

        AuditLog::create([
            'user_id'    => $request->user()->id,
            'action'     => 'import',
            'model_type' => 'LabourEnergy',
            'model_id'   => 0,
            'changes'    => json_encode(['inserted' => $inserted, 'updated' => $updated, 'errors' => count($errors)]),
        ]);

        return redirect()->route('import.labour-energy')
            ->with('import_result', compact('inserted', 'updated', 'errors'));
    }

    // ── File parsing ──────────────────────────────────────────────────────

    /**
     * Parse an uploaded CSV or XLSX/XLS file into a 2D array.
     * First element is the header row; remaining are data rows.
     */
    private function parseFile(\Illuminate\Http\UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'csv') {
            return $this->parseCsv($file->getRealPath());
        }

        return $this->parseSpreadsheet($file->getRealPath(), $ext);
    }

    private function parseCsv(string $path): array
    {
        $rows   = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open uploaded CSV file.');
        }
        // Strip BOM if present (UTF-8 BOM from Excel CSV export)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        while (($row = fgetcsv($handle)) !== false) {
            // Skip entirely empty rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) > 0) {
                $rows[] = array_map('trim', $row);
            }
        }
        fclose($handle);
        return $rows;
    }

    private function parseSpreadsheet(string $path, string $ext): array
    {
        $type   = match($ext) {
            'xlsx'  => 'Xlsx',
            'xls'   => 'Xls',
            default => throw new \InvalidArgumentException("Unsupported file extension: $ext"),
        };

        $reader = IOFactory::createReader($type);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet       = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($cellIterator as $cell) {
                $val = $cell->getValue();
                // Convert Excel date serial to string date
                if ($val !== null && is_numeric($val) && $cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC) {
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTimeFormatCode($format)) {
                        $val = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)
                            ->format('Y-m-d');
                    }
                }
                $cells[] = $val !== null ? trim((string) $val) : '';
            }
            // Trim trailing empty cells
            while (!empty($cells) && end($cells) === '') {
                array_pop($cells);
            }
            if (array_filter($cells, fn($v) => $v !== '') !== []) {
                $rows[] = $cells;
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Build a map of column_name => array_index from the header row.
     * Case-insensitive. Only maps columns that exist in $known.
     */
    private function buildHeaderMap(array $headerRow, array $known): array
    {
        $map   = [];
        $lower = array_map(fn($h) => strtolower(trim($h)), $headerRow);
        foreach ($known as $col) {
            $idx = array_search(strtolower($col), $lower, true);
            if ($idx !== false) {
                $map[$col] = $idx;
            }
        }
        return $map;
    }

    /**
     * Return a closure that extracts a column value from a row array.
     */
    private function rowGetter(array $row, array $map): \Closure
    {
        return function (string $col) use ($row, $map): string {
            if (!isset($map[$col])) {
                return '';
            }
            return isset($row[$map[$col]]) ? trim((string) $row[$map[$col]]) : '';
        };
    }

    private function rowIsBlank(array $row): bool
    {
        return count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0;
    }

    private function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    private function num(string $value): float
    {
        return (float) str_replace(',', '', $value);
    }

    private function numOrNull(string $value): ?float
    {
        return $value === '' ? null : (float) str_replace(',', '', $value);
    }
}
