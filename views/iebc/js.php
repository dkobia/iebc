<?php echo html::script(url::file_loc('js').'plugins/iebc/media/js/WKT', TRUE); ?>
<script type="text/javascript" charset="utf-8">
var iebcLayer, wkt;
var iebcFeatures = new Array();
iebcFeatures['counties'] = new Array();
wkt = new OpenLayers.Format.WKT();
jQuery(function() {
	// Toggle County
	$("ul#county_switch li > a").click(function(e) {
		var countyId = this.id.substring(6);

		// Remove All active
		$("a[id^='const_']").removeClass("iebc_active");

		// Add Active Class
		$(this).addClass("iebc_active");

		// Update report filters
		map.updateReportFilters({cty: countyId});

		// Destroy existing IEBC layers (if any)
		if (iebcLayer.destroyFeatures !== undefined)
			iebcLayer.destroyFeatures();

		if (countyId != 0) {
			getWKT(countyId);
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

function getWKT(id){
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