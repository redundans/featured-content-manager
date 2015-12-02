<?php
// echo "Upload plugin to Klandestino Plugins? (yes/no) ";
// 	if ( 'yes' == trim( fgets( STDIN ) ) ) {
// 		echo "What's your username on the plugin server? ";
// 		if ( $username = trim( fgets( STDIN ) ) ) {
// 				echo "Uploading... \n";
// 				chdir( $parent_dir );
// 				system( 'scp readme.txt ' . $username . '@skalman.klandestino.se:/home/' . $username );
// 				chdir( $build_cfg_dir );
// 				system( 'scp ' . $zip_name . ' ' . $username . '@skalman.klandestino.se:/home/' . $username );
// 				system( 'ssh ' . $username . '@skalman.klandestino.se');
// 				system( 'sudo mv readme.txt /var/www/plugins.klandestino.se/wp-content/uploads/2015/11' );
// 				echo "All done, remember to update the version number on plugins.klandestino.se as well! \n";
// 		}
// 	} else {
// 		echo "Alright, then you have to do it yourself\n";
// 	}
echo "Tag version on Github? (yes/no) ";
	if ( 'yes' == trim( fgets( STDIN ) ) ) {
		system( "git tag $version" );
		system( 'git push origin --tags' );
	}