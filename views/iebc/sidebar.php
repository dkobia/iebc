<h3><a href="#"><?php echo Kohana::lang('uchaguzi.county')?></a></h3>
<ul id="county_switch" class="categorylist">
	<li>
		<a href="#" id="const_0">
		<span class="swatch" style="background-color:#ccc"></span>
		<span class="county_name">ALL</span>
		</a>
	</li>
	<?php
		foreach ($counties as $county)
		{
			$county_name = $county->county_name;

			echo '<li>'
			    . '<a href="#" id=const_'. $county->id .'>'
			    . '<span class="swatch" style="background-color:#ccc"></span>'
			    . '<span class="county_name">'.$county_name.'</span>'
			    . '</a>';

			echo '</li>';
		}
	?>
</ul>