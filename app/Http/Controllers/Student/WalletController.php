<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('auth');
        $this->middleware('check.user.active');
    }

    /**
     * عرض المحفظة
     */
    public function index()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('student.pages.wallet.index', compact('wallet', 'transactions'));
    }

    /**
     * شحن المحفظة
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();
            $transaction = $this->walletService->deposit(
                $user,
                $request->amount,
                $request->description ?: 'شحن المحفظة'
            );

            return response()->json([
                'success' => true,
                'message' => 'تم شحن المحفظة بنجاح',
                'balance' => $transaction->balance_after,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء شحن المحفظة: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * معاملات المحفظة
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $query = $wallet->transactions();

        // فلترة حسب النوع
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('student.pages.wallet.partials.transactions', compact('transactions'))->render(),
            ]);
        }

        return view('student.pages.wallet.transactions', compact('wallet', 'transactions'));
    }
}
