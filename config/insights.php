<?php

// facebook application information
$config['app_id'] 			= 'YOUR_APP_ID';
$config['app_secret']		= 'YOUR_APP_SECRET';
$config['app_redirect_uri']	= base_url() . 'insights_test';	# this must live somewhere in the URL you have set in FB

// database table name for caching
$config['db_table_name']	= 'facebook_insights';

// enable/disable cache (bool)
$config['cache_enabled']	= TRUE;
// how long should we cache the results for
$config['cache_length'] 	= '1 week';