<?php
echo "Upload plugin to Klandestino Plugins? (Y/n) ";
	if ( 'Y' == trim( fgets( STDIN ) ) ) {
		// system( 'git clone git@github.com:bradt/wp-migrate-db.git github1' );
		// system( 'mkdir github' );
		// system( 'mv github1/.git* github/' );
		// system( 'rm -R github1/' );
		// system( "rsync -r $plugin_slug/* github/" );
		// chdir( 'github' );
		// system( 'git add -A .' );
		// system( 'git status' );
		echo "Uploading... \n";

		// echo "Commit and push to Github? (Y/n)? ";
		// if ( 'Y' == trim( fgets( STDIN ) ) ) {
		// 	system( "git commit -m 'Deploying version $version'" );
		// 	system( 'git push origin master' );
		// 	system( "git tag $version" );
		// 	system( 'git push origin --tags' );
		// }

		// chdir( $tmp_dir );
		echo "All done, have fun! \n";
	} else {
		echo "Allright, then you have to do it yourself\n";
	}

