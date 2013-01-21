<?php echo html::script(url::file_loc('js').'plugins/iebc/media/js/WKT', TRUE); ?>
<script type="text/javascript" charset="utf-8">
var iebcLayer, wkt;

wkt = new OpenLayers.Format.WKT();
jQuery(function() {
	// County Switch Action
	$("ul#county_switch li > a").click(function(e) {
		var countyId = this.id.substring(4);
		getWKT(countyId);
		return false;
	});

	var proj_4326 = new OpenLayers.Projection('EPSG:4326');
	var proj_900913 = new OpenLayers.Projection('EPSG:900913');

	template = {
		fillOpacity: 0.35,
		strokeColor: "#888888",
		strokeWidth: 2,
		fillColor: "#ccc"
	};

	//var layerStyle = new OpenLayers.StyleMap( { 'default': new OpenLayers.Style(template, {context: context}) });
	iebcLayer = new OpenLayers.Layer.Vector("IEBC Layer", {projection: proj_4326});
	Ushahidi._currentMap._olMap.addLayers([iebcLayer]);
});

function getWKT(id){
	$.get("iebc/county/"+id, function(data){
		parseWKT(data);
	});
}

function parseWKT(data) {
	var features = wkt.read(data);
	features.geometry.transform(proj_4326, proj_900913);
	var bounds;
	if(features) {
		if(features.constructor != Array) {
			features = [features];
		}
		for(var i=0; i<features.length; ++i) {
			if (!bounds) {
				bounds = features[i].geometry.getBounds();
			} else {
				bounds.extend(features[i].geometry.getBounds());
			}

		}
		iebcLayer.addFeatures(features);
		console.log(map);
		Ushahidi._currentMap._olMap.zoomToExtent(bounds);
		var plural = (features.length > 1) ? 's' : '';
	} else {
		alert('Invalid WKT Data');
	}
}
</script>