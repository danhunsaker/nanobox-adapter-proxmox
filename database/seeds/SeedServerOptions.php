<?php

use App\Region;
use App\ServerPlan;
use App\ServerSize;
use Illuminate\Database\Seeder;

class SeedServerOptions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $region = Region::create(['code' => 'own', 'name' => 'Self-Owned/Operated']);

        $plans = [
            '1c'  => ServerPlan::create(['code' => '1c', 'name' => 'Single Core']),
            '2c'  => ServerPlan::create(['code' => '2c', 'name' => 'Dual Core']),
            '4c'  => ServerPlan::create(['code' => '4c', 'name' => 'Quad Core']),
            '4c+' => ServerPlan::create(['code' => '4c+', 'name' => 'Quad Core High Capacity']),
        ];

        foreach ($plans as $plan) {
            $plan->regions()->attach($region->id);
        }

        ServerSize::create(['code' => '512mb', 'ram' => 512, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['1c']->id);
        ServerSize::create(['code' => '1gb', 'ram' => 1024, 'cpu' => 1, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['1c']->id);

        ServerSize::create(['code' => '512mb2c', 'ram' => 512, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['2c']->id);
        ServerSize::create(['code' => '1gb2c', 'ram' => 1024, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['2c']->id);
        ServerSize::create(['code' => '2gb2c', 'ram' => 2048, 'cpu' => 2, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['2c']->id);

        ServerSize::create(['code' => '1gb4c', 'ram' => 1024, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c']->id);
        ServerSize::create(['code' => '2gb4c', 'ram' => 2048, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c']->id);
        ServerSize::create(['code' => '4gb4c', 'ram' => 4096, 'cpu' => 4, 'disk' => 40, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c']->id);

        ServerSize::create(['code' => '1gb4c+', 'ram' => 1024, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c+']->id);
        ServerSize::create(['code' => '2gb4c+', 'ram' => 2048, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c+']->id);
        ServerSize::create(['code' => '4gb4c+', 'ram' => 4096, 'cpu' => 4, 'disk' => 250, 'transfer' => null, 'dollars_per_hr' => 0, 'dollars_per_mo' => 0])->serverPlans()->attach($plans['4c+']->id);
    }
}
