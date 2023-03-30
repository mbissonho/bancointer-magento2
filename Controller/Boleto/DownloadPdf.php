<?php

namespace Mbissonho\BancoInter\Controller\Boleto;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Mbissonho\BancoInter\Controller\DownloadPdfAction;

class DownloadPdf implements HttpGetActionInterface
{
    protected DownloadPdfAction $downloadPdfAction;

    public function __construct(
        DownloadPdfAction $downloadPdfAction
    )
    {
        $this->downloadPdfAction = $downloadPdfAction;
    }

    public function execute()
    {
        return $this->downloadPdfAction->execute();
    }
}
