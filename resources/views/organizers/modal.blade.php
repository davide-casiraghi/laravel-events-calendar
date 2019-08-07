{{--
    This modal is used in the event.create and event.edit view to add a new teacher
    It is loaded in view/partials/forms/modal-frame when the 
    button "Add new organizer" is clicked in the event create view
--}}

@extends('laravel-events-calendar::layouts.modal')

@section('javascript-document-ready')
    @parent
    
    $('#teacherModalForm').validate({
        rules: {
            name: "required",
            email: {
                required: true,
                email: true
            },
            website: {
                required: false,
                url: true
            }
        },
        submitHandler: function(form) {
            //alert("Do some stuff... 2");
            $.ajax({
                url: '/create-organizer/modal/',
                data: {
                    "_token": "{{ csrf_token() }}",
                    name: $("input[name='name']").val(),
                    country_id: $("select[name='country_id']").val(),
                    bio: $("textarea[name='bio']").val(),
                    year_starting_practice: $("input[name='year_starting_practice']").val(),
                    year_starting_teach: $("input[name='year_starting_teach']").val(),
                    significant_teachers: $("textarea[name='significant_teachers']").val(),                
                    facebook: $("input[name='facebook']").val(),
                    website: $("input[name='website']").val(),
                    profile_picture: $("input[name='profile_picture']").val()
                },
                type: 'POST',
                success: function(res) {
                    console.log("teacher created succesfully");
                    console.log(res.teacherId);
                    $('.modalFrame').modal('hide');
                    //$("input[name='multiple_teachers']").addClass('ciao');
                    
                    // maybe we don't need this line
                    $("input[name='multiple_teachers']").val($("input[name='multiple_teachers']").val() + ", " + res.teacherId);
                    
                    $("select#teacher").append('<option value="'+res.teacherId+'" selected="">'+res.teacherName+'</option>');
                    $("select#teacher").selectpicker("refresh");
                },
                error: function(error) {
                    //$('.modalFrame').modal('hide');
                    console.log(error);
                }          
            });
        }
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
            <h4>@lang('laravel-events-calendar::organizer.add_new_organizer')</h4>
        </div>
    </div>

    @include('laravel-form-partials::error-management', [
          'style' => 'alert-danger',
    ])

    <form action="{{ route('organizers.storeFromModal') }}" method="POST">
        @csrf

        <div class="row">
           <div class="col-12">
               @include('laravel-form-partials::input', [
                   'title' => __('laravel-events-calendar::general.name'),
                   'name' => 'name',
                   'placeholder' => 'Name',
                   'required' => true,
               ])
           </div>
           
           <div class="col-12">
               @include('laravel-form-partials::input', [
                   'title' => __('laravel-events-calendar::general.email_address'),
                   'name' => 'email',
                   'required' => true,
               ])
           </div>
           <div class="col-12">
               @include('laravel-form-partials::input', [
                   'title' => __('laravel-events-calendar::general.phone'),
                   'name' => 'phone',
                   'placeholder' => '',
                   'required' => false,
               ])
           </div>
           <div class="col-12">
               @include('laravel-form-partials::input', [
                   'title' => __('laravel-events-calendar::general.website'),
                   'name' => 'website',
                   'placeholder' => 'https://...',
                   'required' => false,
               ])
           </div>
           <div class="col-12">
               @include('laravel-form-partials::textarea', [
                     'title' => __('laravel-events-calendar::general.description'),
                     'name' => 'description',
                     'placeholder' => 'Organizer description',
                     'required' => false,
               ])
           </div>
       </div>

        <div class="row mt-5">
            <div class="col-6 pull-left">
                <button type="button" class="btn btn-primary" data-dismiss="modal">@lang('laravel-events-calendar::general.close')</button>
            </div>
            <div class="col-6 pull-right">
              <button type="submit" class="btn btn-primary float-right">@lang('laravel-events-calendar::general.submit')</button>
            </div>
        </div>

    </form>

@endsection
