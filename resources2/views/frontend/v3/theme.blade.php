<!-- Layerslider -->
    {!! Html::script('assets/frontend/js-core/greensock.js') !!}
    {!! Html::script('assets/frontend/widgets/layerslider/layerslider.js') !!}
    {!! Html::script('assets/frontend/widgets/layerslider/layerslider-transitions.js') !!}
    {!! Html::script('assets/frontend/widgets/layerslider/layerslider-demo.js') !!}

    <div id="loading">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
    <!-- <div id="theme-options">
        <a href="#" class="btn btn-primary theme-switcher tooltip-button" data-placement="left" title="Color schemes and layout options">
            <i class="glyph-icon icon-linecons-cog icon-spin"></i>
        </a>
        <div id="theme-switcher-wrapper">
            <div class="scroll-switcher">
                <h5 class="header">Layout options</h5>
                <ul class="reset-ul">
                    <li>
                        <label for="boxed-layout">Boxed layout</label>
                        <input type="checkbox" data-toggletarget="boxed-layout" id="boxed-layout" class="input-switch-alt">
                    </li>
                </ul>
                <div class="boxed-bg-wrapper hide">
                    <h5 class="header">
                        Boxed Page Background
                        <a class="set-background-style" data-header-bg="" title="Remove all styles" href="#">Clear</a>
                    </h5>
                    <div class="theme-color-wrapper clearfix">
                        <h5>Patterns</h5>
                        <a class="tooltip-button set-background-style pattern-bg-3" data-header-bg="pattern-bg-3" title="Pattern 3" href="#">Pattern 3</a>
                        <a class="tooltip-button set-background-style pattern-bg-4" data-header-bg="pattern-bg-4" title="Pattern 4" href="#">Pattern 4</a>
                        <a class="tooltip-button set-background-style pattern-bg-5" data-header-bg="pattern-bg-5" title="Pattern 5" href="#">Pattern 5</a>
                        <a class="tooltip-button set-background-style pattern-bg-6" data-header-bg="pattern-bg-6" title="Pattern 6" href="#">Pattern 6</a>
                        <a class="tooltip-button set-background-style pattern-bg-7" data-header-bg="pattern-bg-7" title="Pattern 7" href="#">Pattern 7</a>
                        <a class="tooltip-button set-background-style pattern-bg-8" data-header-bg="pattern-bg-8" title="Pattern 8" href="#">Pattern 8</a>
                        <a class="tooltip-button set-background-style pattern-bg-9" data-header-bg="pattern-bg-9" title="Pattern 9" href="#">Pattern 9</a>
                        <a class="tooltip-button set-background-style pattern-bg-10" data-header-bg="pattern-bg-10" title="Pattern 10" href="#">Pattern 10</a>

                        <div class="clear"></div>

                        <h5 class="mrg15T">Solids &amp;Images</h5>
                        <a class="tooltip-button set-background-style bg-black" data-header-bg="bg-black" title="Black" href="#">Black</a>
                        <a class="tooltip-button set-background-style bg-gray mrg10R" data-header-bg="bg-gray" title="Gray" href="#">Gray</a>

                        <a class="tooltip-button set-background-style full-bg-1" data-header-bg="full-bg-1 fixed-bg" title="Image 1" href="#">Image 1</a>
                        <a class="tooltip-button set-background-style full-bg-2" data-header-bg="full-bg-2 fixed-bg" title="Image 2" href="#">Image 2</a>
                        <a class="tooltip-button set-background-style full-bg-3" data-header-bg="full-bg-3 fixed-bg" title="Image 3" href="#">Image 3</a>
                        <a class="tooltip-button set-background-style full-bg-4" data-header-bg="full-bg-4 fixed-bg" title="Image 4" href="#">Image 4</a>
                        <a class="tooltip-button set-background-style full-bg-5" data-header-bg="full-bg-5 fixed-bg" title="Image 5" href="#">Image 5</a>
                        <a class="tooltip-button set-background-style full-bg-6" data-header-bg="full-bg-6 fixed-bg" title="Image 6" href="#">Image 6</a>

                    </div>
                </div>
                <h5 class="header">
                    Top Menu Style
                    <a class="set-topmenu-style" data-header-bg="" title="Remove all styles" href="#">Clear</a>
                </h5>
                <div class="theme-color-wrapper clearfix">
                    <h5>Solids</h5>
                    <a class="tooltip-button set-topmenu-style bg-primary" data-header-bg="bg-primary font-inverse" title="Primary" href="#">Primary</a>
                    <a class="tooltip-button set-topmenu-style bg-green" data-header-bg="bg-green font-inverse" title="Green" href="#">Green</a>
                    <a class="tooltip-button set-topmenu-style bg-red" data-header-bg="bg-red font-inverse" title="Red" href="#">Red</a>
                    <a class="tooltip-button set-topmenu-style bg-blue" data-header-bg="bg-blue font-inverse" title="Blue" href="#">Blue</a>
                    <a class="tooltip-button set-topmenu-style bg-warning" data-header-bg="bg-warning font-inverse" title="Warning" href="#">Warning</a>
                    <a class="tooltip-button set-topmenu-style bg-purple" data-header-bg="bg-purple font-inverse" title="Purple" href="#">Purple</a>
                    <a class="tooltip-button set-topmenu-style bg-black" data-header-bg="bg-black font-inverse" title="Black" href="#">Black</a>

                    <div class="clear"></div>

                    <h5 class="mrg15T">Gradients</h5>
                    <a class="tooltip-button set-topmenu-style bg-gradient-1" data-header-bg="bg-gradient-1 font-inverse" title="Gradient 1" href="#">Gradient 1</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-2" data-header-bg="bg-gradient-2 font-inverse" title="Gradient 2" href="#">Gradient 2</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-3" data-header-bg="bg-gradient-3 font-inverse" title="Gradient 3" href="#">Gradient 3</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-4" data-header-bg="bg-gradient-4 font-inverse" title="Gradient 4" href="#">Gradient 4</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-5" data-header-bg="bg-gradient-5 font-inverse" title="Gradient 5" href="#">Gradient 5</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-6" data-header-bg="bg-gradient-6 font-inverse" title="Gradient 6" href="#">Gradient 6</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-7" data-header-bg="bg-gradient-7 font-inverse" title="Gradient 7" href="#">Gradient 7</a>
                    <a class="tooltip-button set-topmenu-style bg-gradient-8" data-header-bg="bg-gradient-8 font-inverse" title="Gradient 8" href="#">Gradient 8</a>
                </div>
                <h5 class="header">
                    Header Style
                    <a class="set-header-style" data-header-bg="bg-header" title="Remove all styles" href="#">Clear</a>
                </h5>
                <div class="theme-color-wrapper clearfix">
                    <h5>Solids</h5>
                    <a class="tooltip-button set-header-style bg-primary" data-header-bg="bg-primary font-inverse" title="Primary" href="#">Primary</a>
                    <a class="tooltip-button set-header-style bg-green" data-header-bg="bg-green font-inverse" title="Green" href="#">Green</a>
                    <a class="tooltip-button set-header-style bg-red" data-header-bg="bg-red font-inverse" title="Red" href="#">Red</a>
                    <a class="tooltip-button set-header-style bg-blue" data-header-bg="bg-blue font-inverse" title="Blue" href="#">Blue</a>
                    <a class="tooltip-button set-header-style bg-warning" data-header-bg="bg-warning font-inverse" title="Warning" href="#">Warning</a>
                    <a class="tooltip-button set-header-style bg-purple" data-header-bg="bg-purple font-inverse" title="Purple" href="#">Purple</a>
                    <a class="tooltip-button set-header-style bg-black" data-header-bg="bg-black font-inverse" title="Black" href="#">Black</a>

                    <div class="clear"></div>

                    <h5 class="mrg15T">Gradients</h5>
                    <a class="tooltip-button set-header-style bg-gradient-1" data-header-bg="bg-gradient-1 font-inverse" title="Gradient 1" href="#">Gradient 1</a>
                    <a class="tooltip-button set-header-style bg-gradient-2" data-header-bg="bg-gradient-2 font-inverse" title="Gradient 2" href="#">Gradient 2</a>
                    <a class="tooltip-button set-header-style bg-gradient-3" data-header-bg="bg-gradient-3 font-inverse" title="Gradient 3" href="#">Gradient 3</a>
                    <a class="tooltip-button set-header-style bg-gradient-4" data-header-bg="bg-gradient-4 font-inverse" title="Gradient 4" href="#">Gradient 4</a>
                    <a class="tooltip-button set-header-style bg-gradient-5" data-header-bg="bg-gradient-5 font-inverse" title="Gradient 5" href="#">Gradient 5</a>
                    <a class="tooltip-button set-header-style bg-gradient-6" data-header-bg="bg-gradient-6 font-inverse" title="Gradient 6" href="#">Gradient 6</a>
                    <a class="tooltip-button set-header-style bg-gradient-7" data-header-bg="bg-gradient-7 font-inverse" title="Gradient 7" href="#">Gradient 7</a>
                    <a class="tooltip-button set-header-style bg-gradient-8" data-header-bg="bg-gradient-8 font-inverse" title="Gradient 8" href="#">Gradient 8</a>
                </div>
            </div>
        </div>
    </div> -->