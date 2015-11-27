<?php
echo "Upload plugin to Klandestino Plugins? (yes/no) ";
	if ( 'yes' == trim( fgets( STDIN ) ) ) {
		echo "Uploading... \n";
		chdir( $parent_dir );
		system( 'scp readme.txt lakrisgubben@naturkontakt.se:/home/lakrisgubben' );
		chdir( $build_cfg_dir );
		system( 'scp ' . $zip_name . ' lakrisgubben@naturkontakt.se:/home/lakrisgubben' );
		echo "All done, have fun! \n";
	} else {
		echo "Allright, then you have to do it yourself\n";
	}

