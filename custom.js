jQuery(function($){
	var today = new Date();
	$('#tarih_sec').datepicker({ 
		dateFormat: 'dd-mm-yy',
		maxDate: today.getHours() >= 21 ? 2 : 1,
		onSelect: function(date){
            
			$('.loaderr').css('display','inline-block');
			
			var ajaxurl = frontend_ajax_object.ajaxurl;
		
			var data = {
				date: date,
				action: 'ilgili_tarihin_egazetesine_yonlendir',
			}
			$.post(ajaxurl, data, function(response){
				$('.loaderr').css('display','none');
				if( response == 'none' ){
					alert('Bu tarih için e-gazete bulunamadı!');
				}else{
					//window.location.href(response);
					window.location.href = response;
				}
				
				
				
			});
			
        }
	});
	
});