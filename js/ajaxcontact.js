/**********************
This is custom JS script 
for our custom functions
@author: Aakash
***********************/

/**
AJAX handler for form submission
*/
jQuery(document).ready(function (e) {
	jQuery('.loader').hide();
	jQuery('#postForm').on('submit',function(e) {
		e.preventDefault();
		jQuery('.loader').show();
		var formData = new FormData(this);
		formData.append('action', 'ajaxform_send_mail');
		var ajaxurl = jQuery('#ajaxurl').val();
		//alert(ajaxurl);
		jQuery.ajax({
			type:'POST',
			url: ajaxurl,
			data:formData,
			contentType: false,
			processData: false,
			dataType: 'html',
			success:function(data){
				console.log("success");
				var id = '#ajax_response';
				jQuery(id).html('');
				jQuery(id).append(data);
				jQuery(id).show();
				jQuery('.loader').hide();
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
				jQuery('.loader').hide();
			}
		});
	});
});

jQuery("#post_image").on("change", function() {
	jQuery("#postForm").submit();
});



/**
Get Image URL and set in a hidden field
*/
function imgchange(event){
    jQuery("#changedImage").val(URL.createObjectURL(event.target.files[0]));
}
