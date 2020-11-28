<?php
namespace NovumHome\Store\Plugin;

use Api\Packagist\Api;
use Core\DataType\Composer;
use Core\MainController;
use Core\Reflection\System\Finder;
use Core\Translate;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;

class Controller extends MainController {


    function doInstallPackage():void
    {
        $sPackageName = $this->post('package');


        exit();
    }

	function run()
	{

	    $oPackageFinder = new PackageFinder();

        $aViewData = [
            'installable_packages' => $oPackageFinder->getPackages()
        ];

	    $aResult['content'] = $this->parse('Store/Plugin/plugin.twig', $aViewData);
	    $aResult['title'] = Translate::fromCode("");
	    return $aResult;
	}

}


