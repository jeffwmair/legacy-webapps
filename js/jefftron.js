var JEFFTRON = (function() {
	
	var _qtip = function(el, text) {
		el.qtip({content:text, position: { corner: { target:'rightTop', tooltip:'leftMiddle' } }, style: { fontFamily:'Helvetica', padding:8, name: 'cream' }});
	};
	var _mapPlaces = [];
	var addMapPlace = function(lat,lon,title) {
		_mapPlaces.push({lat:lat,lon:lon,title:title});
	};
	
	var obj = {
		ajaxCall: function(url, callback) {
			$.ajax({
				url: url,
				accepts: 'text/html',
				success: function(data) {
					callback(data);
				},
				error: function() {
					alert('Error in ajax call');
				}
			});
		},
		createNavigationDropdown: function() {
			var header = $('#header');
			var dropdown = $('<select id="navDropdown">' +
			  '<option value="navHome">Home</option>' +
			  '<option value="navAbout">About</option>' +
			  '<option value="navDrawings">Drawings</option>' +
			  '<option value="navSchool">School</option>' +
			  '<option value="navWebApps">My Web Apps</option>' +
			  '<option value="navInternetUsage"> ---> Internet Usage</option>' +
			  '<option value="navKorean"> ---> Korean Study</option>' +
			  '<option value="navRyerson"> ---> Ryerson Blackboard</option>' + 
			  '<option value="navTemperature"> ---> Temperature Trending</option>' +
			  '<option value="navBookmarks">Bookmarks</option>' +
				'</select>');
				
			var navValues = new Array();
			navValues['navHome'] = '/';
			navValues['navAbout'] = '/About/';
			navValues['navDrawings'] = '/Drawings/';
			navValues['navSchool'] = '/School/';
			navValues['navWebApps'] = '/WebApps/';
			navValues['navInternetUsage'] = '/159Huron/';
			navValues['navKorean'] = '/Korean/';
			navValues['navRyerson'] = '/Ryerson/';			
			navValues['navTemperature'] = '/159Huron/Temperature.php';
			navValues['navBookmarks'] = '/Bookmarks/';			
			
			dropdown.change(function(e) { 
				window.location.href = navValues[$(e.target).val()];
				}
			);
			header.after(dropdown);
		},
		generateNavigationMenu: function(container) {
			var linksContainer = $('<div id="links"></div>')
			linksContainer.append('<div><a id=oldsite"navHome" href="/oldsite/" class="secondaryFontColor">Home</a></div>');
 			linksContainer.append('<div><a id="navAbout" href="/About/" class="secondaryFontColor">About</a></div>')
			linksContainer.append('<div><a id="navDrawings" href="/Drawings/" class="secondaryFontColor">Drawings</a></div>');
			// linksContainer.append('<div><label id="navProgramming" class="secondaryFontColor">Programming</label></div>');
			linksContainer.append('<div><a href="/School/" id="navSchool" class="secondaryFontColor">School</a></div>');
			var webAppsDiv = $('<div></div');
			webAppsDiv.append('<a id="navWebApps" href="/WebApps/" class="secondaryFontColor">My Web Apps</a>');
			webAppsDiv.append('<a id="navInternetUsage" href="/159Huron/" class="thirdFontColor appSecure">Internet Usage</a>');
			webAppsDiv.append('<a href="/Korean/" id="navKorean" class="thirdFontColor appSecure">Korean Study</a>');
			webAppsDiv.append('<a href="/Journal" id="navJournal" class="thirdFontColor appSecure">Journal</a>');
			webAppsDiv.append('<a id="navTemperature" href="/159Huron/Temperature.php" class="thirdFontColor appUnsecure">Temperature Trending</a>');
			webAppsDiv.append('<a href="/Ryerson/" class="thirdFontColor appUnsecure">Ryerson Blackboard</a>');
			linksContainer.append(webAppsDiv);
			linksContainer.append('<div><a id="navBookmarks" href="/Bookmarks/" class="secondaryFontColor">Bookmarks</a></div>');
			
			container.append(linksContainer);
			
			// setup qTips
			_qtip($('#navProgramming'), 'I haven\'t put this page up yet');
			_qtip($('#navInternetUsage, #navKorean, #navJournal'), 'Credentials required');
		},
		setCurrentLocation: function(id) {
			$('#' + id).addClass('curLocation');
			$('select#navDropdown option[value='+id+']').attr('selected', 'selected');
		},
		generateGoogleMap: function(containerId) {
			
			var mapOptions = {center: new google.maps.LatLng(37.4, -88), zoom: 5, mapTypeId: google.maps.MapTypeId.ROADMAP  };
	        var map = new google.maps.Map(document.getElementById(containerId),
	            mapOptions);
			// toyota cambridge 43.420385,-80.368865

			addMapPlace(43.420385,-80.368865,'Toyota - Cambridge ON');
			addMapPlace(38.261839,-84.531568, 'Toyota - Georgetown KY');
			addMapPlace(43.483909,-79.667365, 'Ford - Oakville ON');
			addMapPlace(41.488392,-82.06412, 'Ford - Cleveland OH');
			addMapPlace(42.331107,-83.200371, 'Ford - iTek Center Dearborn');
			addMapPlace(40.460271,-86.111876, 'Chrysler - Kokomo Transmission Plant IL (KTP)');
			addMapPlace(44.14831,-79.844348, 'Honda Alliston ON');
			addMapPlace(43.385339,-79.787573, 'Umicore Burlington ON');
			addMapPlace(47.572588,7.798525, 'Umicore - Rheinfelden');
			addMapPlace(43.08594,-89.397228, 'Tower Automotive - Madison WI');
			addMapPlace(29.26304,-98.542952, 'Toyota - San Antonio TX')
			addMapPlace(43.87618,-81.312739, 'Westcast North Huron');
			addMapPlace(43.014877,-80.88548, 'CAMI Automotive');
			addMapPlace(42.320795,-82.996136, 'Ford - Windsor Engine Place');
			for(var i = 0, len = _mapPlaces.length; i < len; i++)
			{
				var place = _mapPlaces[i];
				new google.maps.Marker({
				      position: new google.maps.LatLng(place.lat,place.lon),
				      map: map,
				      title:place.title
				  });
			}
		}
	};
	return obj;
}());