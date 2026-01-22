<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPaymentMethod;
use Illuminate\Http\Request;

class CustomPaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $methods = CustomPaymentMethod::orderBy('order')->paginate(20);
        return view('admin.pages.custom-payment-methods.index', compact('methods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.custom-payment-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:iban,code,other',
            'account_info' => 'required|array',
            'code_prefix' => 'nullable|string|max:50',
            'instructions' => 'nullable|string|max:2000',
            'requires_receipt' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        CustomPaymentMethod::create([
            'name' => $request->name,
            'type' => $request->type,
            'account_info' => $request->account_info,
            'code_prefix' => $request->code_prefix,
            'instructions' => $request->instructions,
            'requires_receipt' => $request->has('requires_receipt'),
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.custom-payment-methods.index')
            ->with('success', 'تم إنشاء وسيلة الدفع بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $method = CustomPaymentMethod::findOrFail($id);
        return view('admin.pages.custom-payment-methods.show', compact('method'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $method = CustomPaymentMethod::findOrFail($id);
        return view('admin.pages.custom-payment-methods.edit', compact('method'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $method = CustomPaymentMethod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:iban,code,other',
            'account_info' => 'required|array',
            'code_prefix' => 'nullable|string|max:50',
            'instructions' => 'nullable|string|max:2000',
            'requires_receipt' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $method->update([
            'name' => $request->name,
            'type' => $request->type,
            'account_info' => $request->account_info,
            'code_prefix' => $request->code_prefix,
            'instructions' => $request->instructions,
            'requires_receipt' => $request->has('requires_receipt'),
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.custom-payment-methods.index')
            ->with('success', 'تم تحديث وسيلة الدفع بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $method = CustomPaymentMethod::findOrFail($id);
        $method->delete();

        return redirect()->route('admin.custom-payment-methods.index')
            ->with('success', 'تم حذف وسيلة الدفع بنجاح');
    }
}
