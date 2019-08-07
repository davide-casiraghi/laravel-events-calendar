
{{--
    This modal is used in the event.create and event.edit view to add a new teacher
    It is loaded in view/partials/forms/modal-frame when the 
    button "Add new teacher" is clicked in the event create view
--}}

@extends('laravel-events-calendar::layouts.modal')

@section('javascript-document-ready')
    @parent
    {{-- End date update after start date has changed, and doesn't allow to select a date before the start --}}
    
    
    /*$("#save").click(function(){
        alert("save");        
    });*/
    
    $("form").on('submit', function(event){
        event.preventDefault();
        alert("save_1"); 
        
        $.ajax({
            url: '/create-teacher/modal/',
            data: {
                "_token": "{{ csrf_token() }}",
                name: $("input[name='name']").val(),
                country_id: $("input[name='country_id']").val(),
                bio: $("input[textarea='bio']").val(),
                year_starting_practice: $("input[name='year_starting_practice']").val(),
                year_starting_teach: $("input[name='year_starting_teach']").val(),
                significant_teachers: $("input[textarea='significant_teachers']").val(),
                facebook: $("input[name='facebook']").val(),
                website: $("input[name='website']").val(),
                profile_picture: $("input[name='profile_picture']").val()
            },
            type: 'POST',
            success: function(res) {
                //console.log("teacher created succesfully");
                //$('.modalFrame').modal('hide');
            },
            error: function(error) {
                //$('.modalFrame').modal('hide');
                //console.log(error);
            }
        })
    });
@stop

@section('content')
    
    <div class="row">
        <div class="col-12">
            <button type="button" class="close" data-dismiss="modal"
                aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="col-12 pb-3">
            <h4>@lang('laravel-events-calendar::teacher.add_new_teacher')</h4>
        </div>
    </div>

    @include('laravel-form-partials::error-management', [
          'style' => 'alert-danger',
    ])

    <form action="{{ route('teachers.storeFromModal') }}" method="POST" enctype="multipart/form-data">
        @csrf

         <div class="row">
            <div class="col-12">
                @include('laravel-form-partials::input', [
                      'title' => __('laravel-events-calendar::general.name'),
                      'name' => 'name',
                      'placeholder' => 'Teacher name',
                      'required' => true,
                ])
            </div>

            <div class="col-12">
                @include('laravel-form-partials::select', [
                      'title' => __('laravel-events-calendar::general.country'),
                      'name' => 'country_id',
                      'placeholder' => 'Select country',
                      'records' => $countries,
                      'liveSearch' => 'true',
                      'mobileNativeMenu' => false,
                      'required' => false,
                ])
            </div>

            <div class="col-12">
                @include('laravel-form-partials::textarea-plain', [
                      'title' =>  __('laravel-events-calendar::teacher.bio'),
                      'name' => 'bio',
                      'required' => true,
                ])
            </div>
            <div class="col-12">
                @include('laravel-form-partials::input', [
                      'title' => __('laravel-events-calendar::teacher.year_of_starting_to_practice'),
                      'name' => 'year_starting_practice',
                      'placeholder' => 'AAAA',
                      'value' => '',
                      'required' => true,
                ])
            </div>
            <div class="col-12">
                @include('laravel-form-partials::input', [
                      'title' => __('laravel-events-calendar::teacher.year_of_starting_to_teach'),
                      'name' => 'year_starting_teach',
                      'placeholder' => 'AAAA',
                      'value' => '',
                      'required' => true,
                ])
            </div>
            <div class="col-12">
                @include('laravel-form-partials::textarea-plain', [
                      'title' =>  __('laravel-events-calendar::teacher.significant_teachers'),
                      'name' => 'significant_teachers',
                      'required' => true,
                ])
            </div>
            <div class="col-12">
                @include('laravel-form-partials::input', [
                      'title' => __('laravel-events-calendar::teacher.facebook_profile'),
                      'name' => 'facebook',
                      'placeholder' => 'https://...',
                      'value' => '',
                      'required' => false,
                ])
            </div>
            <div class="col-12">
                @include('laravel-form-partials::input', [
                      'title' => __('laravel-events-calendar::general.website'),
                      'name' => 'website',
                      'placeholder' => 'https://...',
                      'value' => '',
                      'required' => false,
                ])
            </div>
            
            @include('laravel-form-partials::upload-image', [
                  'title' => __('laravel-events-calendar::teacher.upload_profile_picture'), 
                  'name' => 'profile_picture',
                  'value' => ''
            ])
        </div>

        <div class="row mt-5">
            <div class="col-6 pull-left">
                <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('laravel-events-calendar::general.close')</button>
            </div>
            <div class="col-6 pull-right">
              <button id="save" type="submit" class="btn btn-primary float-right">@lang('laravel-events-calendar::general.submit')</button>
            </div>
        </div>

    </form>

@endsection
