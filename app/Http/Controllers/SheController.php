<?php
namespace App\Http\Controllers;

use App\Models\MiningDepartment;
use App\Models\SheIndicator;
use App\Models\SheRequirementItem;
use App\Models\SheRequirementEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SheController extends Controller
{
    const INDICATORS = [
        'medical_injury_case' => 'Medical Injury Case',
        'fatal_incident'      => 'Fatal Incident Injury',
        'lti'                 => 'LTI',
        'nlti'                => 'NLTI',
        'leave'               => 'Leave',
        'offdays'             => 'Offdays',
        'sick'                => 'Sick',
        'iod'                 => 'IOD',
        'awol'                => 'AWOL',
        'terminations'        => 'Terminations',
    ];

    const CAT_LABELS = [
        'she'         => 'SHE Requirements',
        'mining'      => 'Mining Requirements',
        'engineering' => 'Engineering Requirements',
        'plant'       => 'Plant Requirements',
    ];

    // ── Indicators CRUD ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $now        = Carbon::now();
        $filterFrom = $request->filled('from') ? $request->input('from') : $now->copy()->startOfMonth()->toDateString();
        $filterTo   = $request->filled('to')   ? $request->input('to')   : $now->copy()->endOfMonth()->toDateString();
        if ($filterFrom > $filterTo) $filterFrom = $now->copy()->startOfMonth()->toDateString();

        $records = SheIndicator::with('department')
            ->whereBetween('date', [$filterFrom, $filterTo])
            ->orderByDesc('date')
            ->orderBy('mining_department_id')
            ->paginate(30)
            ->withQueryString();

        $isDefaultRange = $filterFrom === $now->copy()->startOfMonth()->toDateString()
                       && $filterTo   === $now->copy()->endOfMonth()->toDateString();

        return view('she.index', compact('records', 'filterFrom', 'filterTo', 'isDefaultRange'));
    }

    public function create()
    {
        return view('she.create', [
            'departments'     => MiningDepartment::active()->orderBy('name')->get(),
            'indicatorLabels' => self::INDICATORS,
        ]);
    }

    public function store(Request $request)
    {
        $deptId = (int) $request->input('mining_department_id', 0);

        $request->validate([
            'date' => [
                'required', 'date',
                Rule::unique('she_indicators')->where(fn ($q) => $q->where('mining_department_id', $deptId)),
            ],
            'mining_department_id' => 'required|exists:mining_departments,id',
        ]);

        $row = ['date' => $request->date, 'mining_department_id' => $deptId];
        foreach (array_keys(self::INDICATORS) as $field) {
            $raw        = $request->input($field, '');
            $row[$field] = ($raw !== '' && is_numeric($raw)) ? max(0, (float) $raw) : 0;
        }

        SheIndicator::create($row);
        return redirect()->route('she.index')->with('success', 'SHE indicator record added.');
    }

    public function edit(SheIndicator $indicator)
    {
        return view('she.edit', [
            'indicator'       => $indicator,
            'departments'     => MiningDepartment::active()->orderBy('name')->get(),
            'indicatorLabels' => self::INDICATORS,
        ]);
    }

    public function update(Request $request, SheIndicator $indicator)
    {
        $deptId = (int) $request->input('mining_department_id', 0);

        $request->validate([
            'date' => [
                'required', 'date',
                Rule::unique('she_indicators')
                    ->where(fn ($q) => $q->where('mining_department_id', $deptId))
                    ->ignore($indicator->id),
            ],
            'mining_department_id' => 'required|exists:mining_departments,id',
        ]);

        $row = ['date' => $request->date, 'mining_department_id' => $deptId];
        foreach (array_keys(self::INDICATORS) as $field) {
            $raw        = $request->input($field, '');
            $row[$field] = ($raw !== '' && is_numeric($raw)) ? max(0, (float) $raw) : 0;
        }

        $indicator->update($row);
        return redirect()->route('she.index')->with('success', 'SHE indicator record updated.');
    }

    public function destroy(SheIndicator $indicator)
    {
        $indicator->delete();
        return redirect()->route('she.index')->with('success', 'Record deleted.');
    }

    // ── Requirements ──────────────────────────────────────────────────────

    private function parsePeriod(?string $period): Carbon
    {
        if (!$period || !preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }
        return Carbon::parse($period . '-01');
    }

    public function editRequirements(Request $request)
    {
        $period     = $request->get('period', now()->format('Y-m'));
        $periodDate = $this->parsePeriod($period);

        $allItems     = SheRequirementItem::orderBy('category')->orderBy('sort_order')->orderBy('name')->get();
        $groupedItems = $allItems->where('is_active', true)->groupBy('category');

        $entries = SheRequirementEntry::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('she_requirement_item_id');

        return view('she.requirements', [
            'period'       => $period,
            'periodDate'   => $periodDate,
            'groupedItems' => $groupedItems,
            'allItems'     => $allItems,
            'entries'      => $entries,
            'catLabels'    => self::CAT_LABELS,
        ]);
    }

    public function storeRequirements(Request $request)
    {
        $request->validate(['period' => ['required', 'regex:/^\d{4}-\d{2}$/']]);

        $periodDate = $this->parsePeriod($request->period)->format('Y-m-d');

        foreach ($request->input('entries', []) as $itemId => $data) {
            if (!ctype_digit((string) $itemId)) continue;
            $value = (isset($data['unit_value']) && $data['unit_value'] !== '') ? (float) $data['unit_value'] : null;
            $notes = isset($data['notes']) ? substr(strip_tags($data['notes']), 0, 255) : null;

            SheRequirementEntry::updateOrCreate(
                ['she_requirement_item_id' => (int) $itemId, 'period' => $periodDate],
                ['unit_value' => $value, 'notes' => $notes]
            );
        }

        return redirect()->route('she.requirements.edit', ['period' => $request->period])
            ->with('success', 'Requirements saved for ' . Carbon::parse($periodDate)->format('F Y') . '.');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'category'        => 'required|in:she,mining,engineering,plant',
            'name'            => 'required|string|max:200',
            'unit_of_measure' => 'nullable|string|max:100',
            'sort_order'      => 'nullable|integer|min:0|max:9999',
        ]);

        SheRequirementItem::create([
            'category'        => $request->category,
            'name'            => trim($request->name),
            'unit_of_measure' => trim($request->unit_of_measure ?? ''),
            'sort_order'      => (int) ($request->sort_order ?? 0),
            'is_active'       => true,
        ]);

        return redirect()->back()->with('success', 'Item added.');
    }

    public function destroyItem(SheRequirementItem $item)
    {
        $item->delete();
        return redirect()->back()->with('success', 'Item deleted.');
    }
}