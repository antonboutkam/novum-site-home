<?php
namespace NovumHome\Wizard;

use Core\DataType\Composer\License;
use Core\Json\JsonUtils;
use Core\MainController;
use Core\Translate;
use Core\Utils;
use Hi\Helpers\DirectoryStructure;

class Controller extends MainController {

    private static string $sCurrState = '{}';
    private static string $sCurrHash = '';

    private function getState(string $sHash = null):string
    {
        if($sHash)
        {
            return $_SESSION['STATES'][$sHash] ?? '';
        }
        return self::$sCurrState;

    }
    public function doStoreState()
    {
        $sState = $this->post('state');
        $sState = empty($sState) ? '{}' : $sState;

        $aCurrentData = JsonUtils::decode($sState);
        $aNewData = $this->post('data');

        self::$sCurrState = JsonUtils::encode(array_merge($aCurrentData, $aNewData ?? []));
        self::$sCurrHash = md5(self::$sCurrState);
        $_SESSION['STATES'][self::$sCurrHash] = self::$sCurrState;

    }
    private function getBaseUrl()
    {
        return '/new/api';
    }
    private function getStep()
    {
        $sRequestUri = Utils::getRequestUri(false);
        $aParts = explode('/', $sRequestUri);
        if(count($aParts) === 3) {
            return 'start';
        }
        return array_reverse($aParts)[0];
    }

    private function getDomain(string $sServiceName):string {
        return $sServiceName . '.demo.novum.nu';
    }
    private function getApiDomain(string $sServiceName):string{
        return 'api.' . $this->getDomain($sServiceName);
    }
    private function getAdminDomain(string $sServiceName):string{
        return 'admin.' . $this->getDomain($sServiceName);
    }

	function run()
	{

        $sStateHash = $this->get('state');
        if(self::$sCurrHash)
        {
            $sStateHash = self::$sCurrHash;
        }

        $sStep = $this->getStep();

	    $aSteps = [
            'start' => new Step('start', 'Api identification', 'Wizard/Steps/start.twig', 'info'),
	        'info' => new Step('info', 'Describe your API', 'Wizard/Steps/info.twig', 'dns', 'start'),
            'dns' => new Step('dns', 'DNS settings', 'Wizard/Steps/dns.twig', 'account', 'info'),
	        'account' => new Step('account', 'Create admin account', 'Wizard/Steps/account.twig', 'installing', 'dns'),
            'installing' => new Step('installing', 'Installing', 'Wizard/Steps/installing.twig', 'account', 'dns'),
        ];

	    $oCurrentStep = $aSteps[$sStep];

        $aStepData = [
            'step' => $oCurrentStep,
            'next' => $aSteps[$oCurrentStep->getNext()] ?? null,
            'prev' => $aSteps[$oCurrentStep->getPrev()] ?? null,
            'state' => $this->getState($sStateHash),
            'data' => JsonUtils::decode($this->getState($sStateHash), true),
            'state_hash' => $sStateHash,
            'base_url' => $this->getBaseUrl()
        ];

        if($sStep === 'info')
        {
            // Setting default value for license field
            $aStepData['data'] = array_merge(['license' => License::MIT], $aStepData['data']);
            $aStepData['licenses'] = License::availableLicenses();
        }
        else if($sStep === 'dns')
        {
            $aStepData['data']['admin_url'] = 'https://' . $this->getAdminDomain($aStepData['data']['service_name']);
            $aStepData['data']['api_url'] = 'https://' . $this->getApiDomain($aStepData['data']['service_name']);

            $aStepData['licenses'] = License::availableLicenses();
        }
        else if($sStep === 'installing')
        {
            $oDirectoryStructure = new DirectoryStructure();
            $oPath = Utils::makePath($oDirectoryStructure->getDataDir(true), 'queue', 'system', 'create');
            $oPath->makeDir();
            $oDestinationFile = $oPath->extend(self::$sCurrHash . '.json');
            $aStepData['data']['type'] = 'api';
            $aStepData['data']['domain'] = $this->getDomain($aStepData['data']['service_name']);

            $oDestinationFile->write(JsonUtils::encode($aStepData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        }

        $aViewData = [
            'form' => $this->parse($aSteps[$sStep]->getTemplate(), $aStepData)
        ];


	    $aResult['content'] = $this->parse('Wizard/wizard.twig', $aViewData);
	    $aResult['title'] = Translate::fromCode("Nieuwe API aanmaken");
	    return $aResult;
	}

}





