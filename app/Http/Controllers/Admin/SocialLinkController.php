<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:social-links-list'])->only(['index', 'show']);
        $this->middleware(['permission:social-links-create'])->only(['create', 'store']);
        $this->middleware(['permission:social-links-edit'])->only(['edit', 'update']);
        $this->middleware(['permission:social-links-delete'])->only('destroy');
    }

    public function index()
    {
        $items = SocialLink::ordered()->paginate(15);
        return view('admin.pages.social-links.index', compact('items'));
    }

    public function create()
    {
        $suggestedIcons = SocialLink::suggestedIcons();
        return view('admin.pages.social-links.create', compact('suggestedIcons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'url'        => 'required|url',
            'icon_class' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        SocialLink::create($data);
        return redirect()->route('admin.social-links.index')
            ->with('success', 'تمت إضافة رابط التواصل بنجاح.');
    }

    public function edit(SocialLink $socialLink)
    {
        $suggestedIcons = SocialLink::suggestedIcons();
        return view('admin.pages.social-links.edit', compact('socialLink', 'suggestedIcons'));
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'url'        => 'required|url',
            'icon_class' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        $socialLink->update($data);
        return redirect()->route('admin.social-links.index')
            ->with('success', 'تم تحديث رابط التواصل بنجاح.');
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();
        return redirect()->route('admin.social-links.index')
            ->with('success', 'تم حذف رابط التواصل بنجاح.');
    }
}
