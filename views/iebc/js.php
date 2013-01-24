<?php echo html::script(url::file_loc('js').'plugins/iebc/media/js/WKT', TRUE); ?>
<script type="text/javascript" charset="utf-8">
var iebcLayer, wkt;
var iebcFeatures = new Array();
iebcFeatures['counties'] = new Array();
wkt = new OpenLayers.Format.WKT();
jQuery(function() {
	// Toggle County
	$("ul#county_switch li > a").click(function(e) {
		// Item Type (county, constituency)
		var type = this.id.substring(0, 4);
		// Item ID
		var id = this.id.substring(5);

		// Remove All active + Hide All Children DIV
		if (type == 'coun') {
			$("a[id^='coun_']").removeClass("iebc_active");
			$("[id^='countyChild_']").hide();
		} else if (type == 'cons') {
			$("a[id^='cons_']").removeClass("iebc_active");
			$("a[id^='poll_']").removeClass("iebc_active");
			$("[id^='constituencyChild_']").hide();
		}

		// Add Active Class
		$(this).addClass("iebc_active");

		// Update map report filters
		if (type == 'coun') { // County Filter
			// Show children DIV
			$("#countyChild_" + id).show();
			$(this).parents("div").show();
			map.updateReportFilters({cty: id, cst: 0});
		} else if (type == 'cons') { // Constituency Filter
			// Show children DIV
			$("#constituencyChild_" + id).show();
			$(this).parents("div").show();
			map.updateReportFilters({cty: 0, cst: id});
		}

		// Add Poll Stations
		if (type == 'poll') {
			getPolling(id)
		} else {
			// Destroy existing IEBC features (if any)
			if (iebcLayer.destroyFeatures !== undefined)
				iebcLayer.destroyFeatures();
			// Remove polling station layer
			deletePolling();
		}

		if (id != 0 && (type == 'coun' || type == 'cons') )  {
			getWKT(id, type);
		};

		e.stopPropagation();
		return false;
	});

	var proj_4326 = new OpenLayers.Projection('EPSG:4326');
	var proj_900913 = new OpenLayers.Projection('EPSG:900913');

	style_default = {
		fillOpacity: 0.30,
		strokeColor: "#fff",
		strokeWidth: 2,
		fillColor: "#9DA700",
		label:"${name}",
		fontColor: "${fontColor}",
		fontSize: "${fontSize}",
		fontFamily: "Courier New, monospace",
		fontWeight: "bold",
		labelOutlineColor: "white",
		labelOutlineWidth: 10,
		labelAlign: "${labelAlign}",
	};

	style_selected = {
		fillOpacity: 0.45,
		strokeColor: "#CC7666",
		strokeWidth: 2,
		fillColor: "#AD361F"
	};

	var layerStyle = new OpenLayers.StyleMap( { 
		'default': new OpenLayers.Style(style_default),
		'select': new OpenLayers.Style(style_selected)
	});
	iebcLayer = new OpenLayers.Layer.Vector("IEBC Layer", {projection: proj_4326, styleMap: layerStyle});
	Ushahidi._currentMap._olMap.addLayers([iebcLayer]);

});

function getWKT(id, type){
	// Get County Shapes
	if (type == 'coun') {
		$.get("iebc/county/"+id, function(data){
			if (data.features) {
				// Add Counties to Map
				parseWKT(data, id, 'county');
				// Add Counties Constituencies to Map
				for(var i=0; i<data.children.length; ++i) {
					parseWKT(data.children[i], 0, 'constituency');
				}
			}
		}, 'json');

	// Get Constituency Shapes
	} else if (type == 'cons') {
		$.get("iebc/constituency/"+id, function(data){
			if (data.features) {
				// Add Constituencies to Map
				parseWKT(data, id, 'county');
			}
		}, 'json');
	};
}

function parseWKT(data, id, type) {
	var features = wkt.read(data.features);
	features.geometry.transform(proj_4326, proj_900913);
	var bounds;
	if(features) {
		if(features.constructor != Array) {
			features = [features];
		}
		if (type == 'county') {
			iebcFeatures['counties'][id] = new Array();
			for(var i=0; i<features.length; ++i) {
				if (!bounds) {
					bounds = features[i].geometry.getBounds();
				} else {
					bounds.extend(features[i].geometry.getBounds());
				}

				features[i].attributes = {
					name: data.name,
					fontSize: "14px",
					fontColor: "#000000",
					labelAlign: "cm"
				};
				iebcFeatures['counties'][id].push(features[i]);
			}
		} else {
			for(var i=0; i<features.length; ++i) {
				if (!bounds) {
					bounds = features[i].geometry.getBounds();
				} else {
					bounds.extend(features[i].geometry.getBounds());
				}

				features[i].attributes = {
					name: data.name,
					fontSize: "10px",
					fontColor: "#676B2E",
					labelAlign: "rb"
				};
			}
		}
			
		iebcLayer.addFeatures(features);
		if (type == 'county') {
			Ushahidi._currentMap._olMap.zoomToExtent(bounds);
		}
	} else {
		alert('Invalid WKT Data');
	}
}

function getPolling(id){
	deletePolling();

	// Styling for the checkins
	var pollingStyle = new OpenLayers.Style({
		pointRadius: 6,
		fillColor: "#333",
		strokeColor: "#FFFFFF",
		fillOpacity: 0.75,
		strokeOpacity: 0.75,
		strokeWidth: 1.5
	});

	var pollingStyleMap = new OpenLayers.StyleMap({
		default: pollingStyle
	});

	map.addLayer(Ushahidi.GEOJSON, {
		name: "Polling Stations",
		url: "iebc/polling/" + id,
		styleMap: pollingStyleMap,
	});
}

function deletePolling(){
	// Remove polling station layer
	var layers = Ushahidi._currentMap._olMap.getLayersByName('Polling Stations');
	for (var i=0; i < layers.length; i++) {
		if (layers[i].destroyFeatures !== undefined)
			layers[i].destroyFeatures();
		Ushahidi._currentMap._olMap.removeLayer(layers[i]);
	}
}

function iebcOnFeatureSelect(feature) {
	selectedFeature = feature;
	popup = new OpenLayers.Popup.FramedCloud("chicken", 
		feature.geometry.getBounds().getCenterLonLat(),
		null,
		"<div><h3>" + feature.name +"</h3></div>",
		null, true, iebcOnPopupClose);
	feature.popup = popup;
	Ushahidi._currentMap._olMap.addPopup(popup);
}

function iebcOnFeatureUnselect(feature) {
	Ushahidi._currentMap._olMap.removePopup(feature.popup);
	feature.popup.destroy();
	feature.popup = null;
}

function iebcOnPopupClose(evt) {
	iebcSelectControl.unselect(selectedFeature);
}
</script>