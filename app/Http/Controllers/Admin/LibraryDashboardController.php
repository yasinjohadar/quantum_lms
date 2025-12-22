<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\Subject;
use App\Services\LibraryStatsService;
use Illuminate\Http\Request;

class LibraryDashboardController extends Controller
{
    public function __construct(
        private LibraryStatsService $statsService
    ) {
    }

    /**
     * عرض لوحة إحصائيات المكتبة.
     */
    public function index()
    {
        $totalItems = LibraryItem::count();
        $totalDownloads = LibraryItem::sum('download_count');
        $totalViews = LibraryItem::sum('view_count');
        $averageRating = LibraryItem::where('total_ratings', '>', 0)->avg('average_rating');

        $categoriesCount = LibraryCategory::count();
        $subjectsCount = Subject::count();

        $popularItems = $this->statsService->getPopularItems(5);
        $mostViewedItems = $this->statsService->getMostViewedItems(5);
        $highestRatedItems = $this->statsService->getHighestRatedItems(5);

        return view('admin.pages.library.dashboard', compact(
            'totalItems',
            'totalDownloads',
            'totalViews',
            'averageRating',
            'categoriesCount',
            'subjectsCount',
            'popularItems',
            'mostViewedItems',
            'highestRatedItems'
        ));
    }
}

