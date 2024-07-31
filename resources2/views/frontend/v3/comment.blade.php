<div class="bg-primary" style="display: flex; align-items: center;">
    <div class="col-xs-12 col-md-4" style="display: flex; padding: 0px;">
        <iframe 
            width="100%" 
            height="300px" 
            src="https://www.youtube.com/embed/S9-XFXshdFg" 
            title="YouTube video player" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>
    <div class="col-xs-12 col-md-8">
        <div class="owl-carousel-5 slider-wrapper">
            @php
                $count = 6;   
            @endphp
            @for ($i = 0; $i < $count; $i++)
                <div class="card-comment">
                    <div class="card-comment-title">
                        Judul
                    </div>
                    <div class="card-comment-subtitle">
                        sub judul
                    </div>
                    <div class="card-comment-body">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Error sint praesentium molestias id vero tempora unde atque sed nam est iste, illum omnis consequatur culpa laboriosam beatae, repudiandae dolor quo!
                    </div>
                    <a href="" class="card-comment-text-link">Pelajari selengkapnya</a>
                </div>
            @endfor
        </div>
    </div>
</div>