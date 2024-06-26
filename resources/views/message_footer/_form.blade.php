<!--
Model : Layout Backend Form Message Footer pada Frontend
Author : Tangkas.
Date : 12/06/2021
-->

@if (count($errors) > 0)
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif
<style type="text/css">
    .select2 {
        width: 100% !important;
        /* overrides computed width, 100px in your demo */
    }

</style>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $data_->name, ['class' => 'form-control required', 'placeholder' => 'Masukan Full Name']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', $data_->email, ['class' => 'form-control required', 'placeholder' => 'Masukan Email']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('phone_number', 'Phone Number') !!}
        {!! Form::text('phone_number', $data_->phone_number, ['class' => 'form-control required', 'placeholder' => 'Masukan Phone Number']) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('additional_message', 'Additional Message') !!}
        {!! Form::text('additional_message', $data_->additional_message, ['class' => 'form-control required', 'placeholder' => 'Masukan Additional Message']) !!}
    </div>
</div>
