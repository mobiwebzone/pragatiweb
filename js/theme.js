
var opencart_responsive_current_width = window.innerWidth;
var opencart_responsive_min_width = 992;
var opencart_responsive_mobile = opencart_responsive_current_width < opencart_responsive_min_width;
var header_link_default = $('#_desktop_link_menu').html(); 

$(document).ready(() => {

    $('#siteloader').fadeOut();
    $('#spin-wrapper').fadeOut();

    // var headerHeight = $('#header').height();
    // va  if ($(window).scrollTop() > headerHeight) {
    //         $('.nav-full-width').addClass('fixed-header');
    //     }
    //     else {
    //         $('.nav-full-width').removeClass('fixed-header');
    //     }
    // });r navHeight = $('#header .nav-full-width').height();
    // $(window).scroll(function(){
    //   

    $('.dropdown').on('show.bs.dropdown', function (e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideDown(600);
    });
    $('.dropdown').on('hide.bs.dropdown', function (e) {
        $(this).find('.dropdown-menu').first().stop(true, true).slideUp(600);
    });

    $('#search_widget .search-logo').click(function() {
        $(this).toggleClass('active').parents('#search_widget').find('form').stop(true,true).slideToggle('medium');
    });

    $(document).on('click', '.btn-block', function () {
        $(this).siblings('.cart-dropdown').stop(true, true).slideToggle();
    });

    /* SlideTop*/
    $(window).scroll(function() {
        if ($(this).scrollTop() > 500) {
            $('#slidetop').fadeIn(500);
        } else {
            $('#slidetop').fadeOut(500);
        }
    });

    $('#slidetop').click(function(e) {
        e.preventDefault();     
        $('html, body').animate({scrollTop: 0}, 800);
    });   

    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
    if(!isMobile) {
    	if($(".parallax").length) {
    		$(".parallax").sitManParallex({  invert: false });
    	};
    } else {
    	$(".parallax").sitManParallex({  invert: true });
    }

    if($('.ishiparallaxbannerblock .parallax').data('deal') == '1') {
       var time = $('.ishiparallaxbannerblock .parallax').data('counter');
        var container = $('.ishiparallaxbannerblock .parallax').find('#parallaxcountdown');

        $(container).countdown(time, function(event) {
            $(this).find(".countdown-days .data").html(event.strftime('%D'));
            $(this).find(".countdown-hours .data").html(event.strftime('%H'));
            $(this).find(".countdown-minutes .data").html(event.strftime('%M'));
            $(this).find(".countdown-seconds .data").html(event.strftime('%S'));
            ;
        });
    }
    
    $(".banner-subtitle").html(function () { var t = $(this).text().trim().split(" "), i = t.shift(); return (t.length > 0 ? "<span>" + i + "</span> " : i) + t.join(" ") });

    $('#menu-icon').on('click', function () {
        $("#mobile_top_menu_wrapper").animate({
            width: "toggle"
        });
        $('#menu_wrapper').toggleClass('active');
    });

    $('#top_menu_closer i').on('click', function () {
        $("#mobile_top_menu_wrapper").animate({
            width: "toggle"
        });
        $('#menu_wrapper').toggleClass('active');
    });

    $('#menu_wrapper').on('click', function () {
        $("#mobile_top_menu_wrapper").animate({
            width: "toggle"
        });
        $('#menu_wrapper').toggleClass('active');
    });

    $('body').on('click', function () {
        $('.ajaxishi-search').hide(); 
    });

    if (opencart_responsive_mobile) {
        toggleMobileStyles();
    }

    adjustTopMenu();

    jQuery(".product-list-js .product-layout .image,.product-list-js .product-layout .caption,.ishicollectionsection .item,.ishispecialblock .item,.ishiproductsblock .item,.related-product .item").each(function() {
        var ishicategorytime = $(this).data('countdowntime');
        var ishicategorycontainer = $(this).find('.countdown-container');
         $(ishicategorycontainer).countdown(ishicategorytime, function (event) {
             $(this).find(".countdown-days .data").html(event.strftime('%D'));
            $(this).find(".countdown-hours .data").html(event.strftime('%H'));
            $(this).find(".countdown-minutes .data").html(event.strftime('%M'));
            $(this).find(".countdown-seconds .data").html(event.strftime('%S'));
        });
    });
    
	function myFunction(x) {
	    if (x.matches) {
	    	var list = $('.headerlink-dropdown').find('.small').html();
	    	$('.viewmore-container').remove();
	    	$('.link_container').append(list);
            $('.link_container').removeClass('large');
            $('.link_container').addClass('small');  
            $('#header_ishiheaderlinks').append('<span class="link-icon"><div class="wrapper-menu"><div class="line-menu half start"></div><div class="line-menu"></div><div class="line-menu half end"></div></div></span>')
            .append('<h4 class="small-title">'+ $('#menu_text').text()  +'</h4>');

	    } else {
            $('#header_ishiheaderlinks .link-icon').remove();
            $('#header_ishiheaderlinks .small-title').remove();
            $('.link_container').removeClass('small');
            $('.link_container').addClass('large');
	    	var bigList = $('<ul class="bullet large link_container"></ul>');
			var smallList = $('<ul class="small"></ul>');
            if ($('#_desktop_link_menu ul.link_container li').length <= 6)
                return;
	    	$.each($('#_desktop_link_menu ul.link_container li'), function( i, val){
		    	if(i < 6) {
		    		bigList.append(val);
		    	} else {
		    		smallList.append(val);
		    	}
		    });
	    	bigList.append('<li class="viewmore-container"><a class="viewmore" href="javascript:void(0)">'+ $('#view_text').text() +'</a><div class="headerlink-dropdown"></div></li>');
    		bigList.find('.headerlink-dropdown').append(smallList);
	        $('.link_container').replaceWith(bigList);
	    }
	}
    $('#_desktop_link_menu').show();
	var x = window.matchMedia("(max-width : 1199px)");
	myFunction(x); // Call listener function at run time
	x.addListener(myFunction); // Attach listener function on state changes 
});

 
$(window).on('resize', function() {
    var _cw = opencart_responsive_current_width;
    var _mw = opencart_responsive_min_width;
    var _w = window.innerWidth;
    var _toggle = (_cw >= _mw && _w < _mw) || (_cw < _mw && _w >= _mw);
    opencart_responsive_current_width= _w;
    opencart_responsive_mobile = opencart_responsive_current_width < opencart_responsive_min_width;
    if (_toggle) {
        toggleMobileStyles();
    }
});     


  
function adjustTopMenu() {
    if (window.matchMedia('(min-width: 1200px)').matches) {
        $( "#_desktop_top_menu #top-menu .top_level_category" ).each(function( index ) {
          var subdiv = $(this).find('.sub-menu .category_dropdownmenu').length;
          var submenu = $(this).find('.sub-menu');
          if (subdiv == 1){
                submenu.css('width','230px');   
            }
            else{
                submenu.css('width',subdiv*200+30+'px');
            }
        });
    }
    else if (window.matchMedia('(min-width: 991px)').matches) {
        $( "#_desktop_top_menu #top-menu .top_level_category" ).each(function( index ) {
          var subdiv = $(this).find('.sub-menu .category_dropdownmenu').length;
          var submenu = $(this).find('.sub-menu');
          if (subdiv == 1){
                submenu.css('width','230px');
            } else if(subdiv < 4) {
                submenu.css('width',subdiv*200+30+'px');
            }
            else{
                submenu.css('width','830px');
            }
        });
    }
    else if (window.matchMedia('(max-width: 991px)').matches) {
        $( "#_mobile_top_menu #top-menu .top_level_category" ).each(function( index ) {
          var subdiv = $(this).find('.sub-menu .category_dropdownmenu').length;
          var submenu = $(this).find('.sub-menu');
          if (subdiv == 1){
                submenu.css('width','auto');   
            }
            else{
                submenu.css('width','auto');
            }
        });
    }

}


function swapChildren(obj1, obj2)
{
    var temp = obj2.children().detach();
    obj2.empty().append(obj1.children().detach());
    obj1.append(temp);
}



function toggleMobileStyles()
{
    if (opencart_responsive_mobile) {
        $("*[id^='_desktop_']").each(function(idx, el) {
            var target = $('#' + el.id.replace('_desktop_', '_mobile_'));
            if (target.length) {
                swapChildren($(el), target);
            }
        });
    } else {
        $("*[id^='_mobile_']").each(function(idx, el) {
            var target = $('#' + el.id.replace('_mobile_', '_desktop_'));
            if (target.length) {
                swapChildren($(el), target);
            }
        });
    }
}

// CHNAGE LOGO
let masterURL = "code/checkSession.php";
var BRAND = new FormData();
BRAND.append("type", 'getBrandLogo');
var rfr = new XMLHttpRequest();
rfr.open("POST", masterURL, true);
rfr.onreadystatechange = function() {
    if (rfr.readyState === 4 && rfr.status === 200) {
        var response = JSON.parse(rfr.responseText);
        // console.log(response);
        if(Object.keys(response.data).length>0){
            setTimeout(()=>{
                var logo = document.getElementById('brand_logo')
                // console.log(logo);
                if(logo){
                    logo.src = response.data['LOGO'];
                    logo.style.display = 'block';
                }
            },1000)
        }
        
    }
};
rfr.onerror = function() {
    console.error('An error occurred during the request.');
    // Handle error here
};
rfr.send(BRAND);


