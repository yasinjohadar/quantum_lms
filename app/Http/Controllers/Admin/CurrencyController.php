<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::ordered()->paginate(20);
        return view('admin.pages.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.currencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // إذا تم تحديدها كافتراضية، إلغاء الافتراضية من العملات الأخرى
            if ($request->has('is_default') && $request->is_default) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }

            Currency::create($request->all());

            DB::commit();

            return redirect()->route('admin.currencies.index')
                ->with('success', 'تم إضافة العملة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إضافة العملة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currency = Currency::findOrFail($id);
        return view('admin.pages.currencies.show', compact('currency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $currency = Currency::findOrFail($id);
        return view('admin.pages.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $currency = Currency::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code,' . $id,
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // إذا تم تحديدها كافتراضية، إلغاء الافتراضية من العملات الأخرى
            if ($request->has('is_default') && $request->is_default && !$currency->is_default) {
                Currency::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $currency->update($request->all());

            DB::commit();

            return redirect()->route('admin.currencies.index')
                ->with('success', 'تم تحديث العملة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث العملة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $currency = Currency::findOrFail($id);

            // منع حذف العملة الافتراضية
            if ($currency->is_default) {
                return redirect()->route('admin.currencies.index')
                    ->with('error', 'لا يمكن حذف العملة الافتراضية');
            }

            // التحقق من وجود أسعار مرتبطة
            if ($currency->prices()->count() > 0) {
                return redirect()->route('admin.currencies.index')
                    ->with('error', 'لا يمكن حذف العملة لأنها مرتبطة بأسعار');
            }

            $currency->delete();

            return redirect()->route('admin.currencies.index')
                ->with('success', 'تم حذف العملة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('admin.currencies.index')
                ->with('error', 'حدث خطأ أثناء حذف العملة: ' . $e->getMessage());
        }
    }
}
