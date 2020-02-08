<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use Illuminate\Foundation\Testing\WithFaker;

class EventCategoryModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_category_name()
    {
        $this->authenticate();

        $eventCategory = factory(EventCategory::class)->create([
            'name' => 'Regular Jam',
            'slug' => 'regular-jam',
        ]);
        
        $categoryName = EventCategory::getCategoryName($eventCategory->id);
        $this->assertEquals('Regular Jam', $categoryName);
    }

    /***************************************************************/

}
