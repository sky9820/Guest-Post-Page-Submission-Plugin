/**********************
This is custom JS script 
for our custom functions
@author: Aakash
***********************/

jQuery(document).ready(function (e) {
	jQuery('#postForm').on('submit',function(e) {
		e.preventDefault();
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
				//alert('OK');
				var id = '#ajax_response';
				jQuery(id).html('');
				jQuery(id).append(data);
				jQuery(id).show();
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
});

jQuery("#post_image").on("change", function() {
	jQuery("#postForm").submit();
});


/**
AJAX handler for form submission
*/
function ajaxformsendmail(post_title, post_description, post_excerpt, thumbnail)
{
	console.log(thumbnail);
	alert('fdfd');
	
	jQuery.ajax({
		type: 'POST',
		url: form_ajax.ajaxurl,
		data: {
		action: 'ajaxform_send_mail',
		title: post_title,
		description: post_description,
		excerpt: post_excerpt,
		thumbnail: thumbnail
	},
	success:function(data, textStatus, XMLHttpRequest){
		alert('OK');
		console.log(data);
		var id = '#ajax_response';
		jQuery(id).html('');
		jQuery(id).append(data);
	},
	error: function(MLHttpRequest, textStatus, errorThrown){
		alert(errorThrown);
	}

	});

}

/**
Get Image URL and set in a hidden field
*/
function imgchange(event){
    jQuery("#changedImage").val(URL.createObjectURL(event.target.files[0]));
}
