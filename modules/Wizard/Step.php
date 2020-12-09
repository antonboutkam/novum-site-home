<?php


namespace NovumHome\Wizard;


class Step
{
    private string $sTag;
    private string $sTitle;
    private ?string $sNext;
    private ?string $sPrev;

    private string $sTemplate;

    function __construct(string $sTag, string $sTitle, string $sTemplate, string $sNext = null, string $sPrev = null)
    {
        $this->sTag = $sTag;
        $this->sTitle = $sTitle;
        $this->sTemplate = $sTemplate;
        $this->sNext = $sNext;
        $this->sPrev = $sPrev;
    }
    function getTag():string
    {
        return $this->sTag;
    }

    function getTemplate():string
    {
        return $this->sTemplate;
    }
    function getTitle():string
    {
        return $this->sTitle;
    }
    function getNext():?string
    {
        return $this->sNext;
    }
    function getPrev():?string
    {
        return $this->sPrev;
    }

}
