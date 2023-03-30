<?php

namespace Mbissonho\BancoInter\Gateway\Boleto\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class IssueResponseHandler implements HandlerInterface
{

    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);
        $stateObject = SubjectReader::readStateObject($handlingSubject);

        $payment = $paymentDataObject->getPayment();

        if (!$payment instanceof Payment) {
            throw new \LogicException('Order Payment should be provided');
        }

        $responseBody = json_decode($response['body']);

        $payment->getOrder()->setCanSendNewEmailFlag(true);
        $baseTotalDue = $payment->getOrder()->getBaseTotalDue();
        $totalDue = $payment->getOrder()->getTotalDue();

        $payment->setTransactionId($responseBody->nossoNumero);
        $payment->setIsTransactionClosed(true);

        $stateObject->setData('state', Order::STATE_PENDING_PAYMENT);
        $payment->getOrder()->setState(Order::STATE_PENDING_PAYMENT);
        $payment->getOrder()->setStatus($payment->getOrder()->getConfig()->getStateDefaultStatus(Order::STATE_PENDING_PAYMENT));

        $message = __('Authorized amount of %1.', $totalDue);
        $payment->setShouldCloseParentTransaction(true);

        $isSameCurrency = $payment->isSameCurrency();
        if (!$isSameCurrency || !$payment->isCaptureFinal($totalDue)) {
            $payment->setIsFraudDetected(true);
        }

        $amount = $payment->formatAmount($totalDue, true);
        $payment->setBaseAmountAuthorized($amount);

        $transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);

        $payment->setAmountAuthorized($totalDue);
        $payment->setBaseAmountAuthorized($baseTotalDue);

        $payment->setAdditionalInformation(
            [
                'own_number' => $responseBody->seuNumero,
                'our_number' => $responseBody->nossoNumero,
                'bar_code' => $responseBody->codigoBarras,
                'digitable_line' => $responseBody->linhaDigitavel
            ]
        );
    }
}
