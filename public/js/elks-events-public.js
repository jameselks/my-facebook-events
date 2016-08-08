
jQuery('.accordion .accordion-toggle').click(function(){

  //Expand or collapse this panel
  jQuery(this).next().slideToggle('fast');
  jQuery(this).toggleClass('accordion-open');
  jQuery(this).toggleClass('accordion-closed');

  //Hide the other panels
  //jQuery(".accordion-content").not(jQuery(this).next()).slideUp('fast');

});

var map;
function initMap() {

	var json = jQuery.getJSON(e2js.uploadsUrl + "/e2-map.json", function() {

		// Declare bounds of map - needs to be here so geolocation can access it
		var bounds = new google.maps.LatLngBounds();
		
		//Parse JSON into array
		json = jQuery.parseJSON(json.responseText);

		// Include user's location in map extent
		/*
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				var userLocation = new google.maps.LatLng(Number(position.coords.latitude), Number(position.coords.longitude));
				//Create the marker
				var userLocationMarker = new google.maps.Marker({
					position: userLocation,
					icon: e2js.pluginUrl + '/img/user-location.png',
					map: map
				});
				bounds.extend(userLocation);
				map.fitBounds(bounds);
			});
		}
		*/

		//Check that there are values to plot
		if ((json) && (json.length > 0)) {

			//Create the map and vars
			map = new google.maps.Map(document.getElementById('map'), { center: {lat: -33.867286, lng: 151.089165}, zoom: 10, scrollwheel:  false });
			try {
			  var GeoMarker = new GeolocationMarker(map);
			} catch (e) {
			  console.log(e instanceof ReferenceError); // true
			  console.log(e.message);                   // "undefinedVariable is not defined"
			  console.log(e.name);                      // "ReferenceError"
			  console.log(e.fileName);                  // "Scratchpad/1"
			  console.log(e.lineNumber);                // 2
			  console.log(e.columnNumber);              // 6
			  console.log(e.stack);                     // "@Scratchpad/2:2:7\n"
			}
			var noGeocode = [];
			var flagNoGeo = false;

			//Loop through each item in the array
			for(var i = 0; i < json.length; i++) {

				//Check that the item actually has a geocoded address
				if (!(!json[i].lat)) {

					var latlng = new google.maps.LatLng(json[i].lat, json[i].lng);
					bounds.extend(latlng);

					//Create the icon
					var eventIcon = {
						url: e2js.pluginUrl + '/img/event-location.png',
						//size: new google.maps.Size(142,142),
						scaledSize: new google.maps.Size(40,40)
					}

					//Create the marker
					var marker = new google.maps.Marker({
						position: latlng,
						icon: eventIcon,
						map: map
					});

					// Create HTML for info window
					var infoWindowContentHTML = createInfoWindow(i);

					// Check if any events are at the same location. If so, create HTML and add to the same info window.
					for(var j = 0; j < json.length; j++ ) {
						if ((json[i].lat == json[j].lat) && (json[i].lng == json[j].lng) && (i != j)) {
							infoWindowContentHTML = infoWindowContentHTML + createInfoWindow(j);
						}
					}

					// Create generic HTML for info window.
					function createInfoWindow(jsonArrPos){
						var infoWindowContent = '<div id="infowindow">' +
						'<img src="' + json[jsonArrPos].cover + '">' +
						'<h2><a href="'+ json[jsonArrPos].url +'">' + json[jsonArrPos].title + '</a></h2>' +
						'<p>At ' + json[jsonArrPos].location + ' from ' + json[jsonArrPos].start + '.</p>' +
						'</div>';
						return infoWindowContent;
					}

					var infowindow = new google.maps.InfoWindow();
					var infowindows = [];

					// Show info window on click
					google.maps.event.addListener(marker,'click', (function(marker,infoWindowContentHTML,infowindow){ 
						return function() {
							closeInfoWindows();
							infowindow.setContent(infoWindowContentHTML);
							infowindow.open(map,marker);
							infowindows[0]=infowindow;
						};
					})(marker,infoWindowContentHTML,infowindow));

					// Close info windows when another is clicked
					function closeInfoWindows(){
						
						if(infowindows.length > 0){

							/* detach the info-window from the marker ... undocumented in the API docs */
							infowindows[0].set("marker", null);

							/* and close it */
							infowindows[0].close();

							/* blank the array */
							infowindows.length = 0;
						}
					}
				} else {

				noGeocode.push(json[i]);

				}
			}
			
			//Change language depending on 1 or more events
			if (json.length > 1) {
				var eventText = 'exhibitions';
			} else {
				var eventText = 'exhibition';
			}

			if (noGeocode.length > 0) {
				var noGeocodeHTML = '<section id="no-geo"><h2 id="no-geo">Google couldn\'t find...</h2>';
				for (var k = 0; k < noGeocode.length; k++) {
					noGeocodeHTML = noGeocodeHTML + '<article><a href="'+ noGeocode[k].url +'">'+ noGeocode[k].title +'</a>' + 
						'<br />Starts '+ noGeocode[k].start +' at '+ noGeocode[k].location +'.</article>';
				}
				
				noGeocodeHTML = noGeocodeHTML + "</section>"
				jQuery('#map').after(noGeocodeHTML);
				
				//Show how many events have been plotted
				jQuery('#map').before('<p><a name="today"></a>Showing ' + (json.length - noGeocode.length) + ' of ' + json.length + ' ' + eventText + ' on today. <a href="#no-geo">Show me the rest</a>.</p>');
			
			} else {
			
				//Show how many events have been plotted
				jQuery('#map').before('<p><a name="today"></a>Showing ' + (json.length - noGeocode.length) + ' of ' + json.length + ' ' + eventText + ' on today.</p>');

				//Place all items within map area
				var listener = google.maps.event.addListener(map, "idle", function() { 
				  if (map.getZoom() > 15) map.setZoom(15); 
				  google.maps.event.removeListener(listener); 
				});
				map.fitBounds(bounds);

			}


		} else {
			//Write if there are no exhibitions on.
			jQuery('#map').append('<p>No exhibition openings on today.</p>');
		}
		
	})
		.done(function() {
			//console.log( "second success" );
		})
		.fail(function() {
			//console.log( "error" );
		})
		.always(function() {
			//console.log( "complete" );
		});

}