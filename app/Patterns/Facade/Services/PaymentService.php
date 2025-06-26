<?php

namespace App\Patterns\Facade\Services;

class PaymentService
{
    public function processPayment(array $paymentData): array
    {
        // Simulate payment processing
        $success = rand(1, 10) > 2; // 80% success rate

        if ($success) {
            return [
                'success' => true,
                'payment_id' => 'PAY_' . time() . '_' . rand(1000, 9999),
                'amount' => $paymentData['amount'],
                'status' => 'completed'
            ];
        }

        return [
            'success' => false,
            'message' => 'Thẻ bị từ chối hoặc không đủ tiền'
        ];
    }

    public function processRefund(string $paymentId): array
    {
        // Simulate refund processing
        return [
            'success' => true,
            'payment_id' => $paymentId,
            'amount' => rand(50000, 200000),
            'status' => 'refunded'
        ];
    }
}