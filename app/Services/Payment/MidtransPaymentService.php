<?php

namespace App\Services\Payment;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransPaymentService implements PaymentInterface
{
    public function __construct()
    {
        $this->configureMiddleware();
    }

    protected function configureMiddleware(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Order $order): array
    {
        try {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                ],
                'item_details' => $this->getItemDetails($order),
            ];

            $snapToken = Snap::getSnapToken($params);

            // Update order with snap token
            $order->update([
                'midtrans_snap_token' => $snapToken
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'order_number' => $order->order_number
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleNotification(array $notificationData): array
    {
        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;

            $paymentStatus = 'pending';

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $paymentStatus = 'paid';
                }
            } elseif ($transactionStatus == 'settlement') {
                $paymentStatus = 'paid';
            } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $paymentStatus = 'failed';
            } elseif ($transactionStatus == 'pending') {
                $paymentStatus = 'pending';
            }

            return [
                'success' => true,
                'order_number' => $orderId,
                'payment_status' => $paymentStatus,
                'transaction_id' => $notification->transaction_id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function getItemDetails(Order $order): array
    {
        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product_name,
            ];
        }

        return $items;
    }
}
