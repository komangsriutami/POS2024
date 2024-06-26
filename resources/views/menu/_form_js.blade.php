<script type="text/javascript">
	var token = "";

	$(document).ready(function(){
		token = $('input[name="_token"]').val();

		action_set_subparent_from_parent();
		
		function formatState (state) {
            if (!state.id) { 
                return state.text; 
            }
            var $state = $(
                '<span><i class="' + state.element.value.toLowerCase() + '"></i> ' + state.text + '</span>'
            );
            return $state;
        };

        $(".input_select").select2({
            templateResult: formatState
        });
	})

	function action_set_subparent_from_parent(){
		$('body').on('change', '.parent', function(){
			set_subparent_from_parent();
		})
	}

	function set_subparent_from_parent(){
		options = $("#sub_parent option");
		parent_selected = $("#sub_parent option:selected").data('parent');
		parent = $("#parent").val();
		
		if(parent_selected != parent){
			$("#sub_parent").val('');
		}

		$(options).each(function(i,l){
			if( $(l).data('parent') == parent || $(l).val() == '' || parent == ''){
				$(l).show();
			}else{
				$(l).hide();
			}
		})

	}

</script>