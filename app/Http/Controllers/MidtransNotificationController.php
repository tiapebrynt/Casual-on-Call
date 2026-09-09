<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\{MidtransService, PaymentService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans, PaymentService $payments): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'string'], 'status_code' => ['required', 'string'], 'gross_amount' => ['required'],
            'signature_key' => ['required', 'string'], 'transaction_status' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'], 'payment_type' => ['nullable', 'string'], 'fraud_status' => ['nullable', 'string'],
        ]);
        abort_unless(hash_equals($midtrans->expectedSignature($payload), $payload['signature_key']), 403);
        $payment = Payment::where('midtrans_order_id', $payload['order_id'])->firstOrFail();
        abort_unless((int) round((float) $payload['gross_amount']) === (int) round((float) $payment->total), 422);
        $status = $payload['transaction_status'];
        if (in_array($status, ['settlement', 'capture'], true) && ($status !== 'capture' || ($payload['fraud_status'] ?? 'accept') === 'accept')) {
            $payment->update(['midtrans_transaction_id' => $payload['transaction_id'] ?? null]);
            $payments->markPaid($payment, 'Midtrans '.($payload['payment_type'] ?? 'Snap'), $payload['transaction_id'] ?? $payload['order_id']);
        } elseif (in_array($status, ['deny', 'cancel', 'expire'], true) && $payment->status !== 'paid') {
            $payment->update([
                'status' => $status === 'expire' ? 'expired' : 'failed',
                'midtrans_order_id' => null,
                'midtrans_snap_token' => null,
            ]);
        }
        return response()->json(['ok' => true]);
    }
}
