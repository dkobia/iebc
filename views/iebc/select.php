<script type="text/javascript" charset="utf-8">
$(function () {
	$('#constituency').change(function() {

		var constituency = jQuery.parseJSON($(this).attr('value'));
		
		// Clear the map first
		vlayer.removeFeatures(vlayer.features);
		$('input[name="geometry[]"]').remove();
		
		point = new OpenLayers.Geometry.Point(constituency.longitude, constituency.latitude);
		OpenLayers.Projection.transform(point, proj_4326,proj_900913);
		
		f = new OpenLayers.Feature.Vector(point);
		vlayer.addFeatures(f);
		
		// create a new lat/lon object
		myPoint = new OpenLayers.LonLat(constituency.longitude, constituency.latitude);
		myPoint.transform(proj_4326, map.getProjectionObject());

		// display the map centered on a latitude and longitude
		map.setCenter(myPoint, <?php echo $default_zoom; ?>);
								
		// Update form values
		$("#latitude").val(constituency.latitude);
		$("#longitude").val(constituency.longitude);
		$("#location_name").val(constituency.name);

		return false;
	});
});
</script>