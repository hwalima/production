<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consumable;
use App\Models\ConsumableStockMovement;
use Carbon\Carbon;

class ConsumableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Catalog ────────────────────────────────────────────────────────
        $items = [
            // ── Blasting ──────────────────────────────────────────────────
            [
                'name'           => 'Safety Fuse',
                'category'       => 'blasting',
                'description'    => '6mm slow-burning safety fuse, ~120 s/m burn rate',
                'purchase_unit'  => 'coil',
                'use_unit'       => 'metre',
                'units_per_pack' => 50,
                'pack_cost'      => 45.00,
                'reorder_level'  => 100,
            ],
            [
                'name'           => 'ANFO (Ammonium Nitrate Fuel Oil)',
                'category'       => 'blasting',
                'description'    => 'Bulk blasting agent – 94% AN / 6% FO mix',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 25,
                'pack_cost'      => 35.00,
                'reorder_level'  => 200,
            ],
            [
                'name'           => 'Electric Detonator (IED)',
                'category'       => 'blasting',
                'description'    => 'Instantaneous electric detonator, plain type',
                'purchase_unit'  => 'box',
                'use_unit'       => 'each',
                'units_per_pack' => 100,
                'pack_cost'      => 280.00,
                'reorder_level'  => 50,
            ],
            [
                'name'           => 'Power Cord / Detonating Cord',
                'category'       => 'blasting',
                'description'    => '10 g/m PETN detonating cord',
                'purchase_unit'  => 'reel',
                'use_unit'       => 'metre',
                'units_per_pack' => 100,
                'pack_cost'      => 95.00,
                'reorder_level'  => 150,
            ],
            [
                'name'           => 'Non-Electric Detonator (NONEL)',
                'category'       => 'blasting',
                'description'    => 'Non-electric shock tube detonator, MS delay',
                'purchase_unit'  => 'box',
                'use_unit'       => 'each',
                'units_per_pack' => 25,
                'pack_cost'      => 120.00,
                'reorder_level'  => 25,
            ],
            [
                'name'           => 'Blast Connector / Connector Link',
                'category'       => 'blasting',
                'description'    => 'In-hole delay connector for trunk-line systems',
                'purchase_unit'  => 'box',
                'use_unit'       => 'each',
                'units_per_pack' => 50,
                'pack_cost'      => 85.00,
                'reorder_level'  => 50,
            ],
            [
                'name'           => 'Stemming Bags',
                'category'       => 'blasting',
                'description'    => 'Pre-filled sand stemming bags for blast-hole plugging',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'each',
                'units_per_pack' => 1,
                'pack_cost'      => 0.80,
                'reorder_level'  => 200,
            ],

            // ── Chemicals (gold processing) ───────────────────────────────
            [
                'name'           => 'Sodium Cyanide (NaCN)',
                'category'       => 'chemicals',
                'description'    => 'Briquette form, ≥98% purity – leaching reagent',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'kg',
                'units_per_pack' => 50,
                'pack_cost'      => 180.00,
                'reorder_level'  => 100,
            ],
            [
                'name'           => 'Hydrated Lime (Ca(OH)₂)',
                'category'       => 'chemicals',
                'description'    => 'Reagent-grade hydrated lime for pH control in leach circuit',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 25,
                'pack_cost'      => 12.50,
                'reorder_level'  => 250,
            ],
            [
                'name'           => 'Caustic Soda (NaOH)',
                'category'       => 'chemicals',
                'description'    => 'Flake form, ≥99% – pH adjustment, strip circuit',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 25,
                'pack_cost'      => 38.00,
                'reorder_level'  => 50,
            ],
            [
                'name'           => 'Activated Carbon',
                'category'       => 'chemicals',
                'description'    => 'Coconut-shell granular activated carbon for CIL/CIP',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 25,
                'pack_cost'      => 55.00,
                'reorder_level'  => 100,
            ],
            [
                'name'           => 'Hydrogen Peroxide (H₂O₂)',
                'category'       => 'chemicals',
                'description'    => '50% technical grade – oxygen source / reagent',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'litre',
                'units_per_pack' => 25,
                'pack_cost'      => 90.00,
                'reorder_level'  => 50,
            ],
            [
                'name'           => 'Borax (Na₂B₄O₇)',
                'category'       => 'chemicals',
                'description'    => 'Granular borax – gold smelting flux',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 25,
                'pack_cost'      => 28.00,
                'reorder_level'  => 50,
            ],
            [
                'name'           => 'Nitric Acid (HNO₃)',
                'category'       => 'chemicals',
                'description'    => '68% reagent grade – gold refining, digestion',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'litre',
                'units_per_pack' => 25,
                'pack_cost'      => 75.00,
                'reorder_level'  => 25,
            ],
            [
                'name'           => 'Sulphuric Acid (H₂SO₄)',
                'category'       => 'chemicals',
                'description'    => '98% concentrated – carbon stripping, reagent',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'litre',
                'units_per_pack' => 25,
                'pack_cost'      => 45.00,
                'reorder_level'  => 25,
            ],
            [
                'name'           => 'Iodised Salt (NaCl)',
                'category'       => 'chemicals',
                'description'    => 'Industrial salt – electrolyte in electrowinning cell',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 50,
                'pack_cost'      => 8.00,
                'reorder_level'  => 100,
            ],
            [
                'name'           => 'Steel Balls (Grinding Media)',
                'category'       => 'chemicals',
                'description'    => '40 mm forged steel grinding balls for ball mill',
                'purchase_unit'  => 'bag',
                'use_unit'       => 'kg',
                'units_per_pack' => 100,
                'pack_cost'      => 85.00,
                'reorder_level'  => 500,
            ],

            // ── PPE ───────────────────────────────────────────────────────
            [
                'name'           => 'Safety Helmets',
                'category'       => 'ppe',
                'description'    => 'EN397 hard hat, assorted colours',
                'purchase_unit'  => 'box',
                'use_unit'       => 'each',
                'units_per_pack' => 10,
                'pack_cost'      => 65.00,
                'reorder_level'  => 10,
            ],
            [
                'name'           => 'Safety Boots (Steel Toe)',
                'category'       => 'ppe',
                'description'    => 'ISO 20345 S3 steel toe & midsole, various sizes',
                'purchase_unit'  => 'pair',
                'use_unit'       => 'pair',
                'units_per_pack' => 1,
                'pack_cost'      => 28.00,
                'reorder_level'  => 10,
            ],
            [
                'name'           => 'Nitrile Gloves (Chemical Resistant)',
                'category'       => 'ppe',
                'description'    => 'Heavy-duty nitrile, elbow length, sizes M/L',
                'purchase_unit'  => 'box',
                'use_unit'       => 'pair',
                'units_per_pack' => 12,
                'pack_cost'      => 24.00,
                'reorder_level'  => 24,
            ],
            [
                'name'           => 'Dust Masks (P2 Respirator)',
                'category'       => 'ppe',
                'description'    => 'FFP2 / N95 cup-style dust & mist respirator',
                'purchase_unit'  => 'box',
                'use_unit'       => 'each',
                'units_per_pack' => 20,
                'pack_cost'      => 18.00,
                'reorder_level'  => 40,
            ],

            // ── Mechanical ────────────────────────────────────────────────
            [
                'name'           => 'Drill Bits (Button 38mm)',
                'category'       => 'mechanical',
                'description'    => '38 mm cross-button bit for jackleg / plugger drill',
                'purchase_unit'  => 'each',
                'use_unit'       => 'each',
                'units_per_pack' => 1,
                'pack_cost'      => 42.00,
                'reorder_level'  => 10,
            ],
            [
                'name'           => 'Drill Steel (Hexagonal)',
                'category'       => 'mechanical',
                'description'    => '22 mm hex x 1.8 m jack leg drill steel',
                'purchase_unit'  => 'each',
                'use_unit'       => 'each',
                'units_per_pack' => 1,
                'pack_cost'      => 38.00,
                'reorder_level'  => 5,
            ],
            [
                'name'           => 'Hydraulic Oil (ISO 46)',
                'category'       => 'mechanical',
                'description'    => 'Mineral hydraulic oil ISO VG 46 – machinery',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'litre',
                'units_per_pack' => 210,
                'pack_cost'      => 320.00,
                'reorder_level'  => 420,
            ],
            [
                'name'           => 'Diesel Engine Oil (15W-40)',
                'category'       => 'mechanical',
                'description'    => 'CI-4 mineral 15W-40 for underground loaders / gensets',
                'purchase_unit'  => 'drum',
                'use_unit'       => 'litre',
                'units_per_pack' => 210,
                'pack_cost'      => 380.00,
                'reorder_level'  => 210,
            ],
        ];

        foreach ($items as $data) {
            $data['is_active'] = true;
            $c = Consumable::firstOrCreate(['name' => $data['name']], $data);

            // ── Seed opening stock + a few recent movements ────────────────
            $this->seedMovements($c, $now);
        }
    }

    // -------------------------------------------------------------------------
    private function seedMovements(Consumable $c, Carbon $now): void
    {
        $unitCost = (float)$c->units_per_pack > 0
            ? (float)$c->pack_cost / (float)$c->units_per_pack : 0;

        // Opening delivery ~45 days ago
        $this->addMovement($c, $unitCost, 'purchase', 'in',
            rand(4, 10) * (float)$c->units_per_pack,
            $now->copy()->subDays(45), 'OPENING STOCK');

        // Two more deliveries in the last 30 days
        foreach ([25, 10] as $daysAgo) {
            if (rand(0, 1)) {
                $this->addMovement($c, $unitCost, 'purchase', 'in',
                    rand(2, 6) * (float)$c->units_per_pack,
                    $now->copy()->subDays($daysAgo), null);
            }
        }

        // Daily usage over the last 14 days
        for ($d = 14; $d >= 1; $d--) {
            if (rand(0, 2) > 0) { // ~67% chance each day
                $qty = round(rand(1, max(1, (int)($c->units_per_pack * 0.3))), 2);
                $this->addMovement($c, $unitCost, 'usage', 'out',
                    $qty, $now->copy()->subDays($d), null);
            }
        }
    }

    // -------------------------------------------------------------------------
    private function addMovement(
        Consumable $c, float $unitCost, string $type, string $direction,
        float $qty, Carbon $date, ?string $reference
    ): void {
        ConsumableStockMovement::create([
            'consumable_id' => $c->id,
            'user_id'       => null,
            'type'          => $type,
            'direction'     => $direction,
            'quantity'      => $qty,
            'packs'         => $direction === 'in'
                ? ((float)$c->units_per_pack > 0 ? $qty / (float)$c->units_per_pack : null)
                : null,
            'unit_cost'     => $unitCost,
            'total_cost'    => round($qty * $unitCost, 2),
            'movement_date' => $date->toDateString(),
            'reference'     => $reference,
            'notes'         => null,
            'created_at'    => $date,
            'updated_at'    => $date,
        ]);
    }
}
