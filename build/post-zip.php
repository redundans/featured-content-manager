<?php
echo "Tag version on Github? (yes/no) ";
	if ( 'yes' == trim( fgets( STDIN ) ) ) {
		system( 'git add -A' );
		system( "git commit -m 'Deploying version $version'" );
		system( 'git push origin master' );
		system( "git tag $version" );
		system( 'git push origin --tags' );
	}