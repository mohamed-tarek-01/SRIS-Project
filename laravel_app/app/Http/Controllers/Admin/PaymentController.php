<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $receipts = PaymentReceipt::with('user')
            ->orderByRaw("FIELD(status, 'pending') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.payments.index', compact('receipts'));
    }

    public function approve(Request $request, PaymentReceipt $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending receipts can be approved.');
        }

        $points = $request->input('granted_points', $payment->requested_points);

        $payment->user->increment('balance', $points);
        
        $payment->update([
            'status' => 'approved',
            'admin_notes' => 'Approved ' . $points . ' points.',
        ]);

        return back()->with('success', 'Payment approved and points added.');
    }

    public function reject(Request $request, PaymentReceipt $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Only pending receipts can be rejected.');
        }

        $payment->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'Rejected by admin.'),
        ]);

        return back()->with('success', 'Payment rejected.');
    }
}
