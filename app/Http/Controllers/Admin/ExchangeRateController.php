<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::active()->ordered()->get();
        $exchangeRates = ExchangeRate::with(['fromCurrency', 'toCurrency'])
            ->active()
            ->orderBy('from_currency_id')
            ->orderBy('to_currency_id')
            ->paginate(20);

        return view('admin.pages.exchange-rates.index', compact('exchangeRates', 'currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currencies = Currency::active()->ordered()->get();
        return view('admin.pages.exchange-rates.create', compact('currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'rate' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        try {
            // التحقق من عدم وجود سعر صرف موجود
            $existing = ExchangeRate::where('from_currency_id', $request->from_currency_id)
                ->where('to_currency_id', $request->to_currency_id)
                ->first();

            if ($existing) {
                return back()
                    ->withInput()
                    ->with('error', 'يوجد سعر صرف موجود بالفعل بين هاتين العملتين');
            }

            ExchangeRate::create($request->all());

            return redirect()->route('admin.exchange-rates.index')
                ->with('success', 'تم إضافة سعر الصرف بنجاح');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة سعر الصرف: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $exchangeRate = ExchangeRate::with(['fromCurrency', 'toCurrency'])->findOrFail($id);
        return view('admin.pages.exchange-rates.show', compact('exchangeRate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);
        $currencies = Currency::active()->ordered()->get();
        return view('admin.pages.exchange-rates.edit', compact('exchangeRate', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $exchangeRate = ExchangeRate::findOrFail($id);

        $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'rate' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        try {
            // التحقق من عدم وجود سعر صرف موجود (إذا تغيرت العملات)
            if ($exchangeRate->from_currency_id != $request->from_currency_id ||
                $exchangeRate->to_currency_id != $request->to_currency_id) {
                $existing = ExchangeRate::where('from_currency_id', $request->from_currency_id)
                    ->where('to_currency_id', $request->to_currency_id)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existing) {
                    return back()
                        ->withInput()
                        ->with('error', 'يوجد سعر صرف موجود بالفعل بين هاتين العملتين');
                }
            }

            $exchangeRate->update($request->all());

            return redirect()->route('admin.exchange-rates.index')
                ->with('success', 'تم تحديث سعر الصرف بنجاح');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث سعر الصرف: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $exchangeRate = ExchangeRate::findOrFail($id);
            $exchangeRate->delete();

            return redirect()->route('admin.exchange-rates.index')
                ->with('success', 'تم حذف سعر الصرف بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('admin.exchange-rates.index')
                ->with('error', 'حدث خطأ أثناء حذف سعر الصرف: ' . $e->getMessage());
        }
    }
}
