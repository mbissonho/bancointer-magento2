<?php

namespace Mbissonho\BancoInter\Cron;

use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Mbissonho\BancoInter\Logger\LoggerInterface;
use Mbissonho\BancoInter\Model\Api\Client;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Mbissonho\BancoInter\Model\Ui\ConfigProvider as GeneralConfigProvider;
use Magento\Framework\DB\TransactionFactory;

class UpdatePaymentStatus
{

    protected TimezoneInterface $timezone;
    protected StoreManagerInterface $storeManager;
    protected OrderCollectionFactory $orderCollectionFactory;
    protected GeneralConfigProvider $generalConfigProvider;
    protected Client $client;
    protected LoggerInterface $logger;
    protected TransactionFactory $transactionFactory;


    public function __construct(
        TransactionFactory $transactionFactory,
        TimezoneInterface $timezone,
        StoreManagerInterface $storeManager,
        OrderCollectionFactory $orderCollectionFactory,
        GeneralConfigProvider $generalConfigProvider,
        Client $client,
        LoggerInterface $logger
    )
    {
        $this->transactionFactory = $transactionFactory;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->generalConfigProvider = $generalConfigProvider;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {

            foreach ($this->storeManager->getStores() as $storeId => $store) {

             if(!$this->generalConfigProvider->isModuleEnabled($storeId)) {
                 continue;
             }

                $collection = $this->orderCollectionFactory->create();
                $collection
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('state', ['eq' => Order::STATE_PENDING_PAYMENT])
                    ->addFieldToFilter('store_id', ['eq' => $storeId ])
                    ->join(
                        ['sop' => $collection->getTable('sales_order_payment')],
                        'main_table.entity_id = sop.parent_id and sop.method IN ("mbissonho_bancointer_boleto")',
                        ['add_info' => 'additional_information']
                    )
                    ->addOrder('created_at', Collection::SORT_ORDER_ASC);

                /* @var \Magento\Sales\Model\Order $olderPendingOrder */
                /* @var \Magento\Sales\Model\Order $newestPendingOrder */
                /* @var \Magento\Sales\Model\Order $order */

                $olderPendingOrder = $collection->getFirstItem();
                $newestPendingOrder = $collection->getLastItem();

                $ordersRelatedPayments = $this->client->getPaymentCollection(
                    $this->timezone->date(new \DateTime($olderPendingOrder->getCreatedAt()))->format('Y-m-d'),
                    $this->timezone->date(new \DateTime($newestPendingOrder->getCreatedAt()))->format('Y-m-d'),
                    $storeId
                );

                foreach ($collection as $order) {

                    $additionalInformation = $order->getPayment()->getAdditionalInformation();

                    try {
                        if(isset($ordersRelatedPayments[$additionalInformation['our_number']])) {

                            $relatedPayment = $ordersRelatedPayments[$additionalInformation['our_number']];

                            if($relatedPayment->situacao !== 'PAGO') {
                                continue;
                            }

                            if(!$order->hasInvoices()) {

                                $message = sprintf("Pagamento %s aprovado", $relatedPayment->nossoNumero);

                                $status = $order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING);

                                $amountPaid = $order->getGrandTotal();
                                $baseAmountPaid = $order->getBaseGrandTotal();

                                $invoice = $order->prepareInvoice();

                                $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);

                                $invoice->setBaseAmountPaid($baseAmountPaid);
                                $invoice->setAmountPaid($amountPaid);
                                $invoice->setBaseGrandTotal($baseAmountPaid);
                                $invoice->setGrandTotal($amountPaid);
                                $invoice->setOrder($order);

                                $invoice->register();

                                $order->setState(Order::STATE_PROCESSING)
                                    ->setStatus($status)
                                    ->addCommentToStatusHistory($message, true, true);

                                $dbTransaction = $this->transactionFactory->create();

                                $dbTransaction
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder())
                                    ->save();

                                $this->logger->info("{$order->getIncrementId()} - $relatedPayment->situacao");
                            }

                        }
                    } catch (\Throwable $e) {
                        $this->logger->critical($e->getMessage());
                    }

                }

            }

        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__("An error occurred while running cron. Check 'mbissonho_bancointer_cron.log' file"));
        }
    }

}
