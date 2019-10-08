
(function($) {

	skel
		.breakpoints({
			xlarge:	'(max-width: 1680px)',
			large:	'(max-width: 1280px)',
			medium:	'(max-width: 980px)',
			small:	'(max-width: 736px)',
			xsmall:	'(max-width: 480px)'
		});

	$(function() {

		var	$window = $(window),
			$body = $('body'),
			$wrapper = $('#page-wrapper'),
			$banner = $('#banner'),
			$header = $('#header');

            // Disable animations/transitions until the page has loaded.
			$body.addClass('is-loading');

			$window.on('load', function() {
				window.setTimeout(function() {
					$body.removeClass('is-loading');
				}, 100);
			});
            
            $body.find('.container').show();

            // Mobile?
			if (skel.vars.mobile){
				$body.addClass('is-mobile');
                $('#social-media-icons').find('.box-wrapper').removeClass('align-center');
            }
			else
				skel
					.on('-medium !medium', function() {
						$body.removeClass('is-mobile');
					})
					.on('+medium', function() {
						$body.addClass('is-mobile');
					});

            // Fix: Placeholder polyfill.
			$('form').placeholder();
            
            // Prioritize "important" elements on medium.
			skel.on('+medium -medium', function() {
				$.prioritize(
					'.important\\28 medium\\29',
					skel.breakpoint('medium').active
				);
			});

		// Scrolly.
			$('.scrolly')
				.scrolly({
					speed: 1500,
					offset: $header.outerHeight()
				});

		// Menu.
			$('#menu')
				.append('<a href="#menu" class="close"></a>')
				.appendTo($body)
				.panel({
					delay: 500,
					hideOnClick: true,
					hideOnSwipe: true,
					resetScroll: true,
					resetForms: true,
					side: 'right',
					target: $body,
					visibleClass: 'is-menu-visible'
				});

		      // Header.
			if (skel.vars.IEVersion < 9)
				$header.removeClass('alt');

			if ($banner.length > 0 &&	$header.hasClass('alt')) {
				$window.on('resize', function() {
					$window.trigger('scroll');
				});

				$banner.scrollex({
					bottom:		$header.outerHeight() + 1,
					terminate:	function() { $header.removeClass('alt'); },
					enter:		function() { $header.addClass('alt'); },
					leave:		function() { $header.removeClass('alt'); }
				});
			}

		$('#page-wrapper').css('opacity', 1);
		
		if (window.innerWidth <= 320) {
			var offerImgWidth = window.innerWidth - 60
			$('.box-wrapper .image').css('max-width', offerImgWidth).css('width', offerImgWidth);
			$('.box-wrapper .image img').css('max-width', offerImgWidth).css('width', offerImgWidth);

		}

        $.each( $('.offers-list'), function( key, box ) {
            if ($(box).find('li').length > 1) {
                $(box).css('margin-right', 0);
            }
        });
        

		// $('.box-wrapper__scroll').width(window.innerWidth - 50);
		$('.box-wrapper__scroll').perfectScrollbar({useBothWheelAxes:true, suppressScrollY:true});
        // $('#social-media-icons').perfectScrollbar({useBothWheelAxes:true, suppressScrollY:true});

        $('.image').on('click', function(){
            var loginDialog = new ModalDialog({
                'el': 'loginModal'
            });

            loginDialog.open();
        });
	});

})(jQuery);

function ModalDialog(options) {
    var self = this;
    this.modalEl = options.el;

    // Get the modal
    this.modal = document.getElementById(this.modalEl);
    this.close = document.getElementsByClassName("close")[0];
    
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == self.modal) {
            self.modal.style.display = "none";
        }
    }

    this.close.onclick = function() {
        self.modal.style.display = "none";
    }
}

ModalDialog.prototype.open = function() {
    this.modal.style.display = "block";
}


function initMap()
{
	var mapEl = document.getElementById('map-canvas');
    if (!mapEl) return false;
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        zoom: 8,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = new google.maps.Map(
        document.getElementById("map-canvas"),
        mapOptions
    );
  
    getBusinessBranches();

    function getBusinessBranches()
    {
        //alert("Loading"); 
        var marker, i;

        var url = 'ajax.php/?q=branches';
        var name;
        var lat;
        var lon;
        var locations;

        var httpRequest = new XMLHttpRequest();
        if (!httpRequest) {
            return false;
        }
        httpRequest.onreadystatechange = alertContents;
        httpRequest.open('GET', url);
        httpRequest.send();

        function alertContents() {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                if (httpRequest.status === 200) {
                    var data =  JSON.parse(httpRequest.responseText);
                    for (var i = 0; i < data.length; i++) {
                        handleData(data[i]);
                    }
                }
            }
        }
    }

    function handleData(branch)
    {
        var branchname = branch.name;

        //getting the lat and long 
        // var address = branch.address.formatted.replace(new RegExp("\\\\", "g"), "")+', '+branch.city_name+', '+branch.region_name+', '+branch.country_name;
        var address = branch.address.formatted;
        
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({
        	'address': address.formatted
        }, function(results, status) {

            if(branch.address.lat==null)
                var lat = results[0].geometry.location.lat();
            else
                var lat = branch.address.lat;

            if(branch.address.long==null)
                var lon = results[0].geometry.location.lng();
            else
                var lon = branch.address.long;

            var branchLatLng = new google.maps.LatLng(lat,lon);
            var marker = new google.maps.Marker({
                position: branchLatLng,
               // icon:image,
                map: map,
                title: branchname,
                zIndex: 1
            });

            bounds.extend(marker.position);

            var contentString = '<div id="content">'+
              '<div id="siteNotice">'+
              '</div>'+
              '<div id="bodyContent">'+
                  '<div style="font-size:14px; font-weight: 500; color: #000">'+
                        address +
                  '</div>'+
                  '<div style="font-size:14px; font-weight: 500; color: #54c1e2; ">'+
                        branchname +
                  '</div>'+
              '</div>'+
              '</div>';

            var infowindow = new google.maps.InfoWindow({
                content: contentString,
                maxWidth: 300
            });

             google.maps.event.addListener(marker, 'click', function() {
                infowindow.open(map,marker);
            });

            google.maps.event.addListener(marker, 'mouseover', function() {
                branchAddress = buildBranchAddress(branch);
            });

            map.fitBounds(bounds);
            var zoom = map.getZoom();
            map.setZoom(zoom > 10 ? 10 : zoom);
        });//geocode result,status
          
    }//handle data

    //called on map item hover, change address regarding the hovered business
    function buildBranchAddress (branch) {
        // var branchAddress = '';

        // branchAddress+= branch.address.replace(new RegExp("\\\\", "g"), "") + '<br>';
        // branchAddress+=branch.city+ '<br>';
        // branchAddress+=branch.region+ '<br>';
        // branchAddress+=branch.country + '<br>';
        
        return branch.address.formatted;
    }
}