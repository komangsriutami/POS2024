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
<link href="http://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.4/summernote.css" rel="stylesheet">

<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('title', 'Judul') !!}
        {!! Form::text('title', $data_->title, ['class' => 'form-control required', 'placeholder' => 'Masukan Judul']) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('content', 'Konten') !!}
        <textarea name="content" class="form-control content-tips" rows="5" placeholder="Masukan Konten">{{ $data_->content }}</textarea>
    </div>
    <div class="form-group col-md-6">
        <div class="row">
            <div class="col-7 text-center">
                @if (!empty($data_->image))
                    <img src="data:image/png;base64, {{ $data_->image}}" width="300" height="200"></a>
                @else
                     <img src="{{ asset('img/default.jpg') }}" width="300" height="200">
                @endif
            </div>
            <div class="col-5">
                {!! Form::label('img', 'Pilih Gambar') !!} <br>
                {!! Form::file('img', null, ['id' => 'img', 'type' => 'file', 'class' => 'form-control required', 'autocomplete' => 'off']) !!} 
                <div class="well well-sm no-shadow">
                    <span style="font-size: 10pt;" class="text-red">| Ukuran : 4 x 6 cm</span>
                </div>
            </div>
        </div>
    </div>
</div>
