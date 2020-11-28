<?php

namespace NovumHome\Store\Plugin;

use Api\Packagist\Api;
use Api\Store\Plugin\Finder;
use Core\DataType\Composer;
use Core\DataType\PluginType;
use Core\Json\JsonUtils;
use Core\Utils;
use Exception;
use GuzzleHttp\Client;
use Hi\Helpers\DirectoryStructure;

class PackageFinder
{
    private function getMainComposer():Composer
    {
        $oDirectoryStructure = new DirectoryStructure();
        $sMainComposerPath = Utils::makePath($oDirectoryStructure->getSystemRoot(), 'composer.json');
        return new Composer(file_get_contents($sMainComposerPath));
    }
    function getPackages():array
    {
        $oStore = new Finder();
        $oDataSourceCollection = $oStore->find();

        $oMainComposer = $this->getMainComposer();
        $oDependencyList = $oMainComposer->getDependencyList(true);
        unset($_SESSION['package_details']);
        if(!isset($_SESSION['package_details']))
        {
            $oPackagist = new Api();
            $aFoundPackages = $oPackagist->getInstallablePackages();
            $aPackageDetails = [];
            foreach ($aFoundPackages as $aFoundPackage)
            {
                $aFullPackageInfo = $oPackagist->getPackageDetails($aFoundPackage['name']);
                $aSimplifiedPackageInfo = current(current(current($aFullPackageInfo)));
                $aSimplifiedPackageInfo['is_installed'] = false;

                if($oDependencyList->hasDependency($aFoundPackage['name']))
                {
                    $aSimplifiedPackageInfo['is_installed'] = true;
                    $aSimplifiedPackageInfo['installed'] = $oDependencyList->findOne($aFoundPackage['name']);
                }

                $oDirectoryStructure = new DirectoryStructure();
                $sLocalAvatarlocation = Utils::makePath($oDirectoryStructure->getSystemRoot(), 'vendor', $aSimplifiedPackageInfo['name'], 'style', 'logo-300.png');

                $bHasAvatar = false;
                if($aSimplifiedPackageInfo['is_installed'] && file_exists($sLocalAvatarlocation))
                {
                    $imgData = base64_encode(file_get_contents($sLocalAvatarlocation));
                    $aSimplifiedPackageInfo['avatar_url'] = 'data: '.mime_content_type($sLocalAvatarlocation).';base64,'.$imgData;
                    $bHasAvatar = true;
                }

                if($bHasAvatar === false && $oDataSource = $oDataSourceCollection->findOneByComposerPackageName($aFoundPackage['name']))
                {
                    $aSimplifiedPackageInfo['avatar_url'] = $oDataSource->getAvatarUrl300();
                    $bHasAvatar = true;
                }

                if($bHasAvatar === false && strpos($aSimplifiedPackageInfo['source']['url'], 'gitlab'))
                {
                    /// https://gitlab.com/NovumGit/innovation-app-brp-domain/-/raw/master/composer.json
                    $sBaseUrl = preg_replace('/\.git$/', '', $aSimplifiedPackageInfo['source']['url']);
                    $sComposerUrl = file_get_contents("$sBaseUrl/-/raw/master/composer.json");
                    $oComposer = new Composer($sComposerUrl);

                    if((string) $oComposer->getType() === PluginType::DOMAIN)
                    {
                        try
                        {
                            $sGitLabAvatarlocation = "$sBaseUrl/-/raw/master/style/logo-300.png";
                            $client = new Client();
                            $response =  $client->request('GET', $sGitLabAvatarlocation);
                            if($response->getStatusCode() === 200)
                            {
                                $imgData = base64_encode($response->getBody());
                                $aSimplifiedPackageInfo['avatar_url'] = 'data: image/png;base64,'.$imgData;
                                $bHasAvatar = true;
                            }
                        }
                        catch (Exception $e)
                        {
                            // Just continue
                        }
                    }
                    if(in_array((string) $oComposer->getType(), [PluginType::SITE, PluginType::API]))
                    {
                        try
                        {
                            $sSiteJsonConfigUrl = "$sBaseUrl/-/raw/master/site.json";
                            $sSiteConfigJson = file_get_contents($sSiteJsonConfigUrl);
                            $aSiteConfig = JsonUtils::decode($sSiteConfigJson);

                            // $aSiteConfig['config_dir']

                            // echo __FILE__ . ':' . __LINE__."<br>";
                            // echo $sBaseUrl;
                            $sGitLabAvatarlocation = "$sBaseUrl/-/raw/master/style/logo-300.png";
                            $client = new Client();
                            $response =  $client->request('GET', $sGitLabAvatarlocation);
                            if($response->getStatusCode() === 200)
                            {
                                $imgData = base64_encode($response->getBody());
                                $aSimplifiedPackageInfo['avatar_url'] = 'data: image/png;base64,'.$imgData;
                                $bHasAvatar = true;
                            }
                        }
                        catch (Exception $e)
                        {
                            // Just continue
                        }
                    }
                }

                if(!$bHasAvatar)
                {
                    $aSimplifiedPackageInfo['avatar_url'] = '/img/no-avatar.png';
                }

                $aPackageDetails[] = $aSimplifiedPackageInfo;
            }
            $_SESSION['package_details'] = $aPackageDetails;
        }
        return $_SESSION['package_details'];
    }

}
