<?php
namespace NovumHome\Home;

use Core\MainController;
use Core\Reflection\System\DomainFinder;
use Core\Reflection\System\SiteFinder;
use Core\Reflection\System\Finder;
use Core\Translate;

class Controller extends MainController {


	function run()
	{
        $oDomainFinder = new DomainFinder();
        $oSiteFinder = new SiteFinder();

        $aViewData = [
            'domains' => $oDomainFinder->find(),
            'sites' => $oSiteFinder->find()
        ];

	    $aResult['content'] = $this->parse('Home/home.twig', $aViewData);
	    $aResult['title'] = Translate::fromCode("");
	    return $aResult;
	}

}


