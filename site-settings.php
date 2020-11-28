<?php
function getSiteSettings()
{

    return [
        'config_dir' => 'novum.home',
        'namespace' => 'NovumHome',
	    'protocol' => isset($_SERVER['IS_DEVEL']) ? 'http' : 'https',
	    'live_domain' => 'home.demo.novum.nu',
        'dev_domain' => 'home.demo.novum.nu',
        'test_domain' => 'home.demo.novum.nu',
    ];
}


