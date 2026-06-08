<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $receipts = PaymentReceipt::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.wallet.index', compact('user', 'receipts'));
    }

    public function uploadReceipt(Request $request)
    {
        $request->validate([
            'receipt_image' => ['required', 'image', 'max:5120'], // max 5MB
            'requested_points' => ['required', 'integer', 'min:10', 'max:10000'],
        ]);

        $path = $request->file('receipt_image')->store('payment_receipts', 'public');

        PaymentReceipt::create([
            'user_id' => auth()->id(),
            'image_path' => $path,
            'requested_points' => $request->requested_points,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Payment receipt uploaded successfully. It will be reviewed by an admin shortly.');
    }
}
