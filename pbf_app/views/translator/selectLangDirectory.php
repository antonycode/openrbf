<div class="block">
			
				<div class="block_head">
					<?php
                    echo (isset($mod_title)?$this->pbf->get_mod_title($mod_title):'');
					?>
				</div>		<!-- .block_head ends -->
				<div class="block_content">

<?php

/* Forms */

foreach ( $langdirs as $langdir ) {
	
	echo form_open('translator');
	
	echo form_submit('langDir', $langdir);
	
	echo form_close();
	
}

?>
</div>		<!-- .block_content ends -->
				
				<div class="bendl"></div>
				<div class="bendr"></div>
					
			</div>
