<h3><a href="#"><?php echo Kohana::lang('uchaguzi.county')?></a></h3>
<ul id="county_switch" class="categorylist">
	<li>
		<a href="#" id="coun_0">
		<span class="swatch" style="background-color:#ccc"></span>
		<span class="county_name">ALL</span>
		</a>
	</li>
	<?php
		foreach ($counties as $county)
		{
			$county_name = $county->county_name;

			echo '<li>'
			    . '<a href="#" id=coun_'. $county->id .'>'
			    . '<span class="swatch" style="background-color:#ccc"></span>'
			    . '<span class="county_name">'.$county_name.'</span>'
			    . '</a>'
				. '</li>';

			// Get Children
			echo '<div class="hide" id="countyChild_'. $county->id .'"><ul>';
			foreach ($county->constituency
				//->orderby('constituency_name', 'ASC')
				//->find_all()
				 as $constituency)
			{
				echo '<li style="padding-left:20px;">'
					. '<a href="#" id="cons_'. $constituency->id .'">'
					. '<span class="swatch" style="background-color:#666"></span>'
					. '<span class="category-title">'.$constituency->constituency_name.'</span>'
					. '</a>'
					. '</li>';

					// Child Functions
					echo '<div class="hide" id="constituencyChild_'. $constituency->id .'"><ul>';
					// Reports
					echo '<li style="padding-left:40px;">'
						. '<a href="#" id="cons_'. $constituency->id .'">'
						. '<span class="swatch" style="background-color:#1BB6A4"></span>'
						. '<span class="category-title">CITIZEN REPORTS</span>'
						. '</a>'
						. '</li>';
					// Polling Stations
					echo '<li style="padding-left:40px;">'
						. '<a href="#" id="poll_'. $constituency->id .'">'
						. '<span class="swatch" style="background-color:#E0BA52"></span>'
						. '<span class="category-title">POLLING STATIONS</span>'
						. '</a>'
						. '</li>';
					echo '</ul></div></li>';	
			}		
			echo '</ul></div></li>';			
		}
	?>
</ul>