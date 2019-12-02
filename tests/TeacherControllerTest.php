<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Tests;

use DavideCasiraghi\LaravelEventsCalendar\Http\Controllers\TeacherController;
use DavideCasiraghi\LaravelEventsCalendar\Models\Event;
use DavideCasiraghi\LaravelEventsCalendar\Models\EventCategory;
use DavideCasiraghi\LaravelEventsCalendar\Models\Teacher;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;

class TeacherControllerTest extends TestCase
{
    use WithFaker;

    /***************************************************************/

    /** @test */
    public function it_displays_the_teachers_index_page()
    {
        $this->get('teachers')
            ->assertViewIs('laravel-events-calendar::teachers.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teachers_index_page_with_search_keywords()
    {
        $request = $this->call('GET', 'teachers', ['keywords' => 'test keywords'])
            ->assertStatus(200);
    }

    /** @test */
    public function it_opens_a_teacher_modal()
    {
        $this->authenticateAsAdmin();
        $this->get('/create-teacher/modal/')
            ->assertViewIs('laravel-events-calendar::teachers.modal')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teachers_directory_index_page()
    {
        $this->get('teachersDirectory')
            ->assertViewIs('laravel-events-calendar::teachers.index')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teacher_create_page()
    {
        $this->authenticateAsAdmin();
        $this->get('teachers/create')
            ->assertViewIs('laravel-events-calendar::teachers.create')
            ->assertStatus(200);
    }

    /** @test */
    public function it_stores_a_valid_teacher()
    {
        $this->authenticateAsAdmin();
        $attributes = factory(Teacher::class)->raw();

        $response = $this->post('/teachers', $attributes);
        $teacher = Teacher::first();

        //$this->assertDatabaseHas('teachers', $attributes);
        $response->assertRedirect('/teachers/');
    }

    /** @test */
    public function it_does_not_store_invalid_teacher()
    {
        $this->authenticateAsAdmin();
        $response = $this->post('/teachers', []);
        $response->assertSessionHasErrors();
        $this->assertNull(Teacher::first());
    }

    /** @test */
    public function it_displays_the_teacher_show_page()
    {
        $teacher = factory(Teacher::class)->create();
        $response = $this->get("/teachers/{$teacher->id}");
        $response->assertViewIs('laravel-events-calendar::teachers.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_displays_the_teacher_edit_page()
    {
        $this->authenticateAsAdmin();
        $teacher = factory(Teacher::class)->create();
        $response = $this->get("/teachers/{$teacher->id}/edit");
        $response->assertViewIs('laravel-events-calendar::teachers.edit')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_updates_valid_teacher()
    {
        $this->authenticateAsAdmin();
        $teacher = factory(Teacher::class)->create();
        $attributes = factory(Teacher::class)->raw(['name' => 'Updated']);

        $response = $this->put("/teachers/{$teacher->id}", $attributes);
        $response->assertRedirect('/teachers/');
        $this->assertEquals('Updated', $teacher->fresh()->name);
    }

    /** @test */
    public function it_does_not_update_invalid_teacher()
    {
        $this->authenticateAsAdmin();
        $teacher = factory(Teacher::class)->create(['name' => 'Example']);
        $response = $this->put("/teachers/{$teacher->id}", []);
        $response->assertSessionHasErrors();
        $this->assertEquals('Example', $teacher->fresh()->name);
    }

    /** @test */
    public function it_deletes_teachers()
    {
        $this->authenticateAsAdmin();
        $teacher = factory(Teacher::class)->create();
        $response = $this->delete("/teachers/{$teacher->id}");
        $response->assertRedirect('/teachers');
        $this->assertNull($teacher->fresh());
    }

    /** @test */
    public function it_store_from_teacher_modal()
    {
        $this->authenticateAsAdmin();
        $request = new \Illuminate\Http\Request();

        $name = $this->faker->name;
        $data = [
            'name' => $name,
            'bio' => $this->faker->paragraph,
            'year_starting_practice' => '2000',
            'year_starting_teach' => '2006',
            'significant_teachers' => $this->faker->paragraph,
            'website' => $this->faker->url,
            'facebook' => 'https://www.facebook.com/'.$this->faker->word,
            'country_id' => $this->faker->numberBetween($min = 1, $max = 253),
          ];

        $request->replace($data);

        $teacherController = new TeacherController();
        $teacherController->storeFromModal($request);

        $this->assertDatabaseHas('teachers', [
           'name' => $name,
        ]);
    }

    /** @test */
    public function it_gets_a_teacher_by_slug()
    {
        $teacher = factory(Teacher::class)->create();
        $response = $this->get('/teacher/'.$teacher->slug);
        $response->assertViewIs('laravel-events-calendar::teachers.show')
                 ->assertStatus(200);
    }

    /** @test */
    public function it_uploads_a_teacher_profile_image()
    {
        $this->authenticateAsAdmin();
        // Delete directory
        //dd(Storage::directories('public/images')); // List directories
        $directory = 'public/images/teachers_profile/';
        Storage::deleteDirectory($directory);

        // Symulate the upload
        $local_test_file = __DIR__.'/test-files/large-avatar.png';
        $uploadedFile = new \Illuminate\Http\UploadedFile(
                $local_test_file,
                'large-avatar.png',
                'image/png',
                null,
                null,
                true
            );

        // Call the function uploadImageOnServer()
        $imageFile = $uploadedFile;
        $imageName = $imageFile->hashName();
        $imageSubdir = 'teachers_profile';
        $imageWidth = '968';
        $thumbWidth = '300';

        TeacherController::uploadImageOnServer($imageFile, $imageName, $imageSubdir, $imageWidth, $thumbWidth);

        // Leave this lines here - they can be very useful for new tests
        //$directory = "/";
        //dump(Storage::allDirectories($directory));
        //dd(Storage::allFiles($directory));

        $filePath = 'public/images/'.$imageSubdir.'/'.$imageName;

        Storage::assertExists($filePath);
    }

    /** @test */
    public function it_displays_the_teacher_show_page_showing_the_events_of_that_teacher()
    {
        $this->authenticateAsAdmin();

        //Add event category
        $eventCategory = factory(EventCategory::class)->create();

        // Add event
        $teacher = factory(Teacher::class)->create();

        // Add the first event
        $attributes_first_event = factory(Event::class)->raw(
            ['multiple_teachers' => $teacher->id]
        );
        $this->post('/events', $attributes_first_event);

        // Add the second event
        $attributes_second_event = factory(Event::class)->raw(
            ['multiple_teachers' => $teacher->id]
        );
        $this->post('/events', $attributes_second_event);

        $response = $this->get("/teachers/{$teacher->id}");
        $response->assertViewIs('laravel-events-calendar::teachers.show')
                 ->assertStatus(200)
                 ->assertSee($attributes_first_event['title'])
                 ->assertSee($attributes_second_event['title']);
    }
}
