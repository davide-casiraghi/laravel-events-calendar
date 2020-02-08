<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Region;
use Illuminate\Foundation\Testing\WithFaker;

class RegionModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_region_name()
    {
        $this->authenticate();

        $region = factory(Region::class)->create([
            'name' => 'Tuscany',
        ]);
        
        $regionName = Region::getRegionName($region->id);
        $this->assertEquals('Tuscany', $regionName);
    }

    /***************************************************************/

}
