    <head>
        <style>
            /* Loading Spinner */
            .spinner{margin:0;width:70px;height:18px;margin:-35px 0 0 -9px;position:absolute;top:50%;left:50%;text-align:center}.spinner > div{width:18px;height:18px;background-color:#333;border-radius:100%;display:inline-block;-webkit-animation:bouncedelay 1.4s infinite ease-in-out;animation:bouncedelay 1.4s infinite ease-in-out;-webkit-animation-fill-mode:both;animation-fill-mode:both}.spinner .bounce1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.spinner .bounce2{-webkit-animation-delay:-.16s;animation-delay:-.16s}@-webkit-keyframes bouncedelay{0%,80%,100%{-webkit-transform:scale(0.0)}40%{-webkit-transform:scale(1.0)}}@keyframes bouncedelay{0%,80%,100%{transform:scale(0.0);-webkit-transform:scale(0.0)}40%{transform:scale(1.0);-webkit-transform:scale(1.0)}}
        </style>
        <meta charset="UTF-8">
    <!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
    <title> APOTEKEREN </title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Favicons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{asset('assets/frontend/images/icons/apple-touch-icon-144-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{asset('assets/frontend/images/icons/apple-touch-icon-114-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{asset('assets/frontend/images/icons/apple-touch-icon-72-precomposed.png')}}">
    <link rel="apple-touch-icon-precomposed" href="{{asset('assets/frontend/images/icons/apple-touch-icon-57-precomposed.png')}}">
    <link rel="shortcut icon" href="{{asset('assets/frontend/images/icons/favicon.png')}}">
    
    <meta name="robots" content="noindex, nofollow">
    {!! Html::style('assets/frontend/widgets/layerslider/skins/fullwidth/skin.css') !!}



        <!-- HELPERS -->
    {!! Html::style('assets/frontend/helpers/animate.css') !!}
    {!! Html::style('assets/frontend/helpers/backgrounds.css') !!}
    {!! Html::style('assets/frontend/helpers/boilerplate.css') !!}
    {!! Html::style('assets/frontend/helpers/border-radius.css') !!}
    {!! Html::style('assets/frontend/helpers/grid.css') !!}
    {!! Html::style('assets/frontend/helpers/page-transitions.css') !!}
    {!! Html::style('assets/frontend/helpers/spacing.css') !!}
    {!! Html::style('assets/frontend/helpers/typography.css') !!}
    {!! Html::style('assets/frontend/helpers/utils.css') !!}
    {!! Html::style('assets/frontend/helpers/colors.css') !!}

    <!-- ELEMENTS -->
    
    {!! Html::style('assets/frontend/elements/badges.css') !!}
    {!! Html::style('assets/frontend/elements/buttons.css') !!}
    {!! Html::style('assets/frontend/elements/content-box.css') !!}
    {!! Html::style('assets/frontend/elements/dashboard-box.css') !!}
    {!! Html::style('assets/frontend/elements/forms.css') !!}
    {!! Html::style('assets/frontend/elements/images.css') !!}
    {!! Html::style('assets/frontend/elements/info-box.css') !!}
    {!! Html::style('assets/frontend/elements/invoice.css') !!}
    {!! Html::style('assets/frontend/elements/loading-indicators.css') !!}
    {!! Html::style('assets/frontend/elements/menus.css') !!}
    {!! Html::style('assets/frontend/elements/panel-box.css') !!}
    {!! Html::style('assets/frontend/elements/response-messages.css') !!}
    {!! Html::style('assets/frontend/elements/responsive-tables.css') !!}
    {!! Html::style('assets/frontend/elements/ribbon.css') !!}
    {!! Html::style('assets/frontend/elements/social-box.css') !!}
    {!! Html::style('assets/frontend/elements/tables.css') !!}
    {!! Html::style('assets/frontend/elements/tile-box.css') !!}
    {!! Html::style('assets/frontend/elements/timeline.css') !!}

    <!-- FRONTEND ELEMENTS -->
    {!! Html::style('assets/frontend/frontend-elements/blog.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/cta-box.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/feature-box.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/footer.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/hero-box.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/icon-box.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/portfolio-navigation.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/pricing-table.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/sliders.css') !!}
    {!! Html::style('assets/frontend/frontend-elements/testimonial-box.css') !!}

    <!-- ICONS -->
    {!! Html::style('assets/frontend/icons/fontawesome/fontawesome.css') !!}
    {!! Html::style('assets/frontend/icons/linecons/linecons.css') !!}
    {!! Html::style('assets/frontend/icons/spinnericon/spinnericon.css') !!}

    <!-- WIDGETS -->
    {!! Html::style('assets/frontend/widgets/accordion-ui/accordion.css') !!}
    {!! Html::style('assets/frontend/widgets/calendar/calendar.css') !!}
    {!! Html::style('assets/frontend/widgets/carousel/carousel.css') !!}
    {!! Html::style('assets/frontend/widgets/charts/justgage/justgage.css') !!}
    {!! Html::style('assets/frontend/widgets/charts/morris/morris.css') !!}
    {!! Html::style('assets/frontend/widgets/charts/piegage/piegage.css') !!}
    {!! Html::style('assets/frontend/widgets/charts/xcharts/xcharts.css') !!}
    {!! Html::style('assets/frontend/widgets/chosen/chosen.css') !!}
    {!! Html::style('assets/frontend/widgets/colorpicker/colorpicker.css') !!}
    {!! Html::style('assets/frontend/widgets/datatable/datatable.css') !!}
    {!! Html::style('assets/frontend/widgets/datepicker/datepicker.css') !!}
    {!! Html::style('assets/frontend/widgets/datepicker-ui/datepicker.css') !!}
    {!! Html::style('assets/frontend/widgets/daterangepicker/daterangepicker.css') !!}
    {!! Html::style('assets/frontend/widgets/dialog/dialog.css') !!}
    {!! Html::style('assets/frontend/widgets/dropdown/dropdown.css') !!}
    {!! Html::style('assets/frontend/widgets/dropzone/dropzone.css') !!}
    {!! Html::style('assets/frontend/widgets/file-input/fileinput.css') !!}
    {!! Html::style('assets/frontend/widgets/input-switch/inputswitch.css') !!}
    {!! Html::style('assets/frontend/widgets/input-switch/inputswitch-alt.css') !!}
    {!! Html::style('assets/frontend/widgets/ionrangeslider/ionrangeslider.css') !!}
    {!! Html::style('assets/frontend/widgets/jcrop/jcrop.css') !!}
    {!! Html::style('assets/frontend/widgets/jgrowl-notifications/jgrowl.css') !!}
    {!! Html::style('assets/frontend/widgets/loading-bar/loadingbar.css') !!}
    {!! Html::style('assets/frontend/widgets/maps/vector-maps/vectormaps.css') !!}
    {!! Html::style('assets/frontend/widgets/markdown/markdown.css') !!}
    {!! Html::style('assets/frontend/widgets/modal/modal.css') !!}
    {!! Html::style('assets/frontend/widgets/multi-select/multiselect.css') !!}
    {!! Html::style('assets/frontend/widgets/multi-upload/fileupload.css') !!}
    {!! Html::style('assets/frontend/widgets/nestable/nestable.css') !!}
    {!! Html::style('assets/frontend/widgets/noty-notifications/noty.css') !!}
    {!! Html::style('assets/frontend/widgets/popover/popover.css') !!}
    {!! Html::style('assets/frontend/widgets/pretty-photo/prettyphoto.css') !!}
    {!! Html::style('assets/frontend/widgets/progressbar/progressbar.css') !!}
    {!! Html::style('assets/frontend/widgets/range-slider/rangeslider.css') !!}
    {!! Html::style('assets/frontend/widgets/slider-ui/slider.css') !!}
    {!! Html::style('assets/frontend/widgets/summernote-wysiwyg/summernote-wysiwyg.css') !!}
    {!! Html::style('assets/frontend/widgets/tabs-ui/tabs.css') !!}
    {!! Html::style('assets/frontend/widgets/theme-switcher/themeswitcher.css') !!}
    {!! Html::style('assets/frontend/widgets/timepicker/timepicker.css') !!}
    {!! Html::style('assets/frontend/widgets/tocify/tocify.css') !!}
    {!! Html::style('assets/frontend/widgets/tooltip/tooltip.css') !!}
    {!! Html::style('assets/frontend/widgets/touchspin/touchspin.css') !!}
    {!! Html::style('assets/frontend/widgets/uniform/uniform.css') !!}
    {!! Html::style('assets/frontend/widgets/wizard/wizard.css') !!}
    {!! Html::style('assets/frontend/widgets/xeditable/xeditable.css') !!}

    <!-- FRONTEND WIDGETS -->
    {!! Html::style('assets/frontend/widgets/layerslider/layerslider.css') !!}
    {!! Html::style('assets/frontend/widgets/owlcarousel/owlcarousel.css') !!}
    {!! Html::style('assets/frontend/widgets/fullpage/fullpage.css') !!}

    <!-- SNIPPETS -->
    {!! Html::style('assets/frontend/snippets/chat.css') !!}
    {!! Html::style('assets/frontend/snippets/files-box.css') !!}
    {!! Html::style('assets/frontend/snippets/login-box.css') !!}
    {!! Html::style('assets/frontend/snippets/notification-box.css') !!}
    {!! Html::style('assets/frontend/snippets/progress-box.css') !!}
    {!! Html::style('assets/frontend/snippets/todo.css') !!}
    {!! Html::style('assets/frontend/snippets/user-profile.css') !!}
    {!! Html::style('assets/frontend/snippets/mobile-navigation.css') !!}

    <!-- Frontend theme -->
    {!! Html::style('assets/frontend/themes/frontend/layout.css') !!}
    {!! Html::style('assets/frontend/themes/frontend/color-schemes/default.css') !!}

    <!-- Components theme -->
    {!! Html::style('assets/frontend/themes/components/default.css') !!}
    {!! Html::style('assets/frontend/themes/components/border-radius.css') !!}

    <!-- Frontend responsive -->
    {!! Html::style('assets/frontend/helpers/responsive-elements.css') !!}
    {!! Html::style('assets/frontend/helpers/frontend-responsive.css') !!}

    <!-- Custom css -->
    {!! Html::style('assets/frontend/custom/v3.css') !!}

    <!-- JS Core -->
    {!! Html::script('assets/frontend/js-core/jquery-core.js') !!}
    {!! Html::script('assets/frontend/js-core/jquery-ui-core.js') !!}
    {!! Html::script('assets/frontend/js-core/jquery-ui-widget.js') !!}
    {!! Html::script('assets/frontend/js-core/jquery-ui-mouse.js') !!}
    {!! Html::script('assets/frontend/js-core/jquery-ui-position.js') !!}
    {!! Html::script('assets/frontend/js-core/transition.js') !!}
    {!! Html::script('assets/frontend/js-core/modernizr.js') !!}
    {!! Html::script('assets/frontend/js-core/jquery-cookie.js') !!}


    <script type="text/javascript">
        $(window).load(function(){
            setTimeout(function() {
                $('#loading').fadeOut( 400, "linear" );
            }, 300);
        });
    </script>
    </head>


    