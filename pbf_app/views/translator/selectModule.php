<div class="block">
			
				<div class="block_head">
					<?php
                    echo (isset($mod_title)?$this->pbf->get_mod_title($mod_title):'');
					?>
				</div>		<!-- .block_head ends -->
				<div class="block_content">
<?php

/* Forms */

foreach ( $masterModules as $masterModule ) {
	
	echo form_open('translator', '', $hidden );
	
	echo form_submit('langModule', str_replace('_lang.php','',$masterModule), 'class="submitlarge"').'<br>';
	
	if ( ! in_array( $masterModule, $slaveModules ) ) {
		echo $slaveLang . " module not found";
	}
	
	echo form_close();
	
}

?>
</div>		<!-- .block_content ends -->
				
				<div class="bendl"></div>
				<div class="bendr"></div>
					
			</div>