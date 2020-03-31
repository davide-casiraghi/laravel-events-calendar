<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Foundation\Testing\WithFaker;

class TeacherModelTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_gets_the_teacher_creator()
    {
        $this->authenticateAsAdmin();

        // Create a teacher by the administrator, that has id 1
        $attributes = factory(Teacher::class)->raw([
            'Name'=>'test teacher',
        ]);
        $this->post('/teachers', $attributes);

        $teacher = Teacher::find(1);

        // Get the user id of the user that create the teacher
        $creatorId = $teacher->user->id;

        $this->assertEquals($creatorId, 1);
    }

    /***************************************************************/
}
