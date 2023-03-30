<?php

namespace Mbissonho\BancoInter\Controller\Adminhtml\Boleto;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Mbissonho\BancoInter\Controller\DownloadPdfAction;

class DownloadPdf extends Action implements HttpGetActionInterface
{
    protected DownloadPdfAction $downloadPdfAction;

    public function __construct(
        Context $context,
        DownloadPdfAction $downloadPdfAction
    )
    {
        $this->downloadPdfAction = $downloadPdfAction;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->downloadPdfAction->execute();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions_view');
    }

}
