<!-- Breadcrumb -->
<div class="breadcrumb-bar p-3" style="margin-top: 60px;background-color: #009688">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        @foreach ($data as $item)  
                            <li class="breadcrumb-item {{$item['active'] ? 'active' : ''}}"><a href="{{url($item['href'])}}">{{$item['text']}}</a></li>
                        @endforeach
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">{{$title}}</h2>
            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->