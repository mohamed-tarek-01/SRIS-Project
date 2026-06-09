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

        $user = $payment->user;
        
        // Add points to user's balance
        $user->increment('balance', $points);
        
        // Process pending fines
        $fines = \App\Models\Fine::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $paidFinesAmount = 0;
        foreach ($fines as $fine) {
            if ($user->balance >= $fine->amount) {
                $user->decrement('balance', $fine->amount);
                $fine->update(['status' => 'paid']);
                $paidFinesAmount += $fine->amount;
            }
        }

        $adminNotes = 'Approved ' . $points . ' points.';
        if ($paidFinesAmount > 0) {
            $adminNotes .= ' Automatically deducted ' . $paidFinesAmount . ' points for pending fines.';
        }

        $payment->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes,
        ]);

        $successMessage = 'Payment approved and points added.';
        if ($paidFinesAmount > 0) {
            $successMessage .= ' Also, ' . $paidFinesAmount . ' points were deducted to pay off pending fines.';
        }

        return back()->with('success', $successMessage);
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
