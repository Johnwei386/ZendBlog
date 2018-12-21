$(function($){
	$('.nav li').mouseover(function(){
		$('.nav .active').removeClass('active');
		$(this).addClass('active');
	});
})