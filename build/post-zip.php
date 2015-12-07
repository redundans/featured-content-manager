<?php
echo "Tag version on Github? (yes/no) ";
	if ( 'yes' == trim( fgets( STDIN ) ) ) {
		chdir( $src_dir );
		system( 'git add .' );
		system( "git commit -m 'Deploying version $version'" );
		system( 'git push origin master' );
		system( "git tag $version" );
		system( 'git push origin --tags' );
	}