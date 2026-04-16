<?php
namespace App\Http\Controllers;

use App\Models\MiningDepartment;
use App\Models\SheIndicator;
use App\Models\SheRequirementItem;
use App\Models\SheRequirementEntry;
use Illuminate\Http\Request;
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

    private function parsePeriod(?string $period): Carbon
    {
        if (!$period || !preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }
        return Carbon::parse($period . '-01');
    }

    private function activeDepartments()
    {
        return MiningDepartment::active()->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $period     = $request->get('period', now()->format('Y-m'));
        $periodDate = $this->parsePeriod($period);
        $departments = $this->activeDepartments();

        $indicators = SheIndicator::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('mining_department_id');

        $requirementItems = SheRequirementItem::where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get()->groupBy('category');

        $entries = SheRequirementEntry::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('she_requirement_item_id');

        return view('she.index', [
            'period'           => $period,
            'periodDate'       => $periodDate,
            'departments'      => $departments,
            'indicators'       => $indicators,
            'requirementItems' => $requirementItems,
            'entries'          => $entries,
            'indicatorLabels'  => self::INDICATORS,
            'catLabels'        => self::CAT_LABELS,
        ]);
    }

    public function editIndicators(Request $request)
    {
        $period      = $request->get('period', now()->format('Y-m'));
        $periodDate  = $this->parsePeriod($period);
        $departments = $this->activeDepartments();

        $indicators = SheIndicator::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('mining_department_id');

        return view('she.indicators', [
            'period'          => $period,
            'periodDate'      => $periodDate,
            'departments'     => $departments,
            'indicators'      => $indicators,
            'indicatorLabels' => self::INDICATORS,
        ]);
    }

    public function storeIndicators(Request $request)
    {
        $request->validate([
            'period'     => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'indicators' => ['nullable', 'array'],
        ]);

        $periodDate  = $this->parsePeriod($request->period)->format('Y-m-d');
        $departments = $this->activeDepartments();

        foreach ($departments as $dept) {
            $data = $request->input("indicators.{$dept->id}", []);
            $row  = [];
            foreach (array_keys(self::INDICATORS) as $field) {
                $raw = $data[$field] ?? '';
                $row[$field] = ($raw !== '' && is_numeric($raw)) ? max(0, (float)$raw) : 0;
            }
            SheIndicator::updateOrCreate(
                ['period' => $periodDate, 'mining_department_id' => $dept->id],
                $row
            );
        }

        return redirect()->route('she.index', ['period' => $request->period])
            ->with('success', 'SHE indicators saved for ' . Carbon::parse($periodDate)->format('F Y') . '.');
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
        $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $periodDate = $this->parsePeriod($request->period)->format('Y-m-d');

        foreach ($request->input('entries', []) as $itemId => $data) {
            if (!ctype_digit((string)$itemId)) {
                continue;
            }
            $value = (isset($data['unit_value']) && $data['unit_value'] !== '')
                ? (float)$data['unit_value']
                : null;
            $notes = isset($data['notes']) ? substr(strip_tags($data['notes']), 0, 255) : null;

            SheRequirementEntry::updateOrCreate(
                ['she_requirement_item_id' => (int)$itemId, 'period' => $periodDate],
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
            'sort_order'      => (int)($request->sort_order ?? 0),
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


class SheController extends Controller
{
    const DEPARTMENTS = ['mining', 'plant_processing', 'engineering', 'admin'];

    const DEPT_LABELS = [
        'mining'           => 'Mining',
        'plant_processing' => 'Plant & Processing',
        'engineering'      => 'Engineering',
        'admin'            => 'Admin',
    ];

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

    private function parsePeriod(?string $period): Carbon
    {
        if (!$period || !preg_match('/^\d{4}-\d{2}$/', $period)) {
            $period = now()->format('Y-m');
        }
        return Carbon::parse($period . '-01');
    }

    public function index(Request $request)
    {
        $period     = $request->get('period', now()->format('Y-m'));
        $periodDate = $this->parsePeriod($period);

        $indicators = SheIndicator::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('department');

        $requirementItems = SheRequirementItem::where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get()->groupBy('category');

        $entries = SheRequirementEntry::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('she_requirement_item_id');

        return view('she.index', [
            'period'           => $period,
            'periodDate'       => $periodDate,
            'indicators'       => $indicators,
            'requirementItems' => $requirementItems,
            'entries'          => $entries,
            'departments'      => self::DEPARTMENTS,
            'deptLabels'       => self::DEPT_LABELS,
            'indicatorLabels'  => self::INDICATORS,
            'catLabels'        => self::CAT_LABELS,
        ]);
    }

    public function editIndicators(Request $request)
    {
        $period     = $request->get('period', now()->format('Y-m'));
        $periodDate = $this->parsePeriod($period);

        $indicators = SheIndicator::where('period', $periodDate->format('Y-m-d'))
            ->get()->keyBy('department');

        return view('she.indicators', [
            'period'          => $period,
            'periodDate'      => $periodDate,
            'indicators'      => $indicators,
            'departments'     => self::DEPARTMENTS,
            'deptLabels'      => self::DEPT_LABELS,
            'indicatorLabels' => self::INDICATORS,
        ]);
    }

    public function storeIndicators(Request $request)
    {
        $request->validate([
            'period'     => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'indicators' => ['nullable', 'array'],
        ]);

        $periodDate = $this->parsePeriod($request->period)->format('Y-m-d');

        foreach (self::DEPARTMENTS as $dept) {
            $data = $request->input("indicators.{$dept}", []);
            $row  = [];
            foreach (array_keys(self::INDICATORS) as $field) {
                $raw = $data[$field] ?? '';
                $row[$field] = ($raw !== '' && is_numeric($raw)) ? max(0, (float)$raw) : 0;
            }
            SheIndicator::updateOrCreate(
                ['period' => $periodDate, 'department' => $dept],
                $row
            );
        }

        return redirect()->route('she.index', ['period' => $request->period])
            ->with('success', 'SHE indicators saved for ' . Carbon::parse($periodDate)->format('F Y') . '.');
    }

    public function editRequirements(Request $request)
    {
        $period     = $request->get('period', now()->format('Y-m'));
        $periodDate = $this->parsePeriod($period);

        $allItems = SheRequirementItem::orderBy('category')->orderBy('sort_order')->orderBy('name')->get();

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
        $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $periodDate = $this->parsePeriod($request->period)->format('Y-m-d');

        foreach ($request->input('entries', []) as $itemId => $data) {
            if (!ctype_digit((string)$itemId)) {
                continue;
            }
            $value = (isset($data['unit_value']) && $data['unit_value'] !== '')
                ? (float)$data['unit_value']
                : null;
            $notes = isset($data['notes']) ? substr(strip_tags($data['notes']), 0, 255) : null;

            SheRequirementEntry::updateOrCreate(
                ['she_requirement_item_id' => (int)$itemId, 'period' => $periodDate],
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
            'sort_order'      => (int)($request->sort_order ?? 0),
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
