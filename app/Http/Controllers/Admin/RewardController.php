<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    /**
     * عرض قائمة المكافآت
     */
    public function index()
    {
        $rewards = Reward::orderBy('points_cost')->get();
        return view('admin.pages.rewards.index', compact('rewards'));
    }

    /**
     * عرض نموذج إنشاء مكافأة
     */
    public function create()
    {
        return view('admin.pages.rewards.create');
    }

    /**
     * حفظ مكافأة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:certificate,discount,badge,points,access',
            'points_cost' => 'required|integer|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ]);

        Reward::create($request->all());

        return redirect()->route('admin.rewards.index')
            ->with('success', 'تم إنشاء المكافأة بنجاح');
    }

    /**
     * عرض نموذج تعديل مكافأة
     */
    public function edit(Reward $reward)
    {
        return view('admin.pages.rewards.edit', compact('reward'));
    }

    /**
     * تحديث مكافأة
     */
    public function update(Request $request, Reward $reward)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:certificate,discount,badge,points,access',
            'points_cost' => 'required|integer|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ]);

        $reward->update($request->all());

        return redirect()->route('admin.rewards.index')
            ->with('success', 'تم تحديث المكافأة بنجاح');
    }

    /**
     * حذف مكافأة
     */
    public function destroy(Reward $reward)
    {
        $reward->delete();

        return redirect()->route('admin.rewards.index')
            ->with('success', 'تم حذف المكافأة بنجاح');
    }
}

