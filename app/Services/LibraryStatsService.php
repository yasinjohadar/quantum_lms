<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LibraryStatsService
{
    /**
     * الحصول على إحصائيات عنصر معين
     */
    public function getItemStats(LibraryItem $item): array
    {
        $recentDownloads = $item->downloads()->recent(30)->count();
        $recentViews = $item->views()->recent(30)->count();

        return [
            'total_downloads' => $item->download_count,
            'total_views' => $item->view_count,
            'recent_downloads' => $recentDownloads,
            'recent_views' => $recentViews,
            'average_rating' => $item->average_rating,
            'total_ratings' => $item->total_ratings,
            'downloads_by_day' => $this->getDownloadsByDay($item),
            'views_by_day' => $this->getViewsByDay($item),
        ];
    }

    /**
     * الحصول على إحصائيات تصنيف معين
     */
    public function getCategoryStats(LibraryCategory $category): array
    {
        $items = $category->items;
        $totalDownloads = $items->sum('download_count');
        $totalViews = $items->sum('view_count');
        $totalRatings = $items->sum('total_ratings');
        $averageRating = $items->where('total_ratings', '>', 0)->avg('average_rating');

        return [
            'total_items' => $items->count(),
            'total_downloads' => $totalDownloads,
            'total_views' => $totalViews,
            'total_ratings' => $totalRatings,
            'average_rating' => round($averageRating ?? 0, 2),
            'most_downloaded' => $items->sortByDesc('download_count')->take(5)->values(),
            'most_viewed' => $items->sortByDesc('view_count')->take(5)->values(),
        ];
    }

    /**
     * الحصول على إحصائيات مادة معينة
     */
    public function getSubjectStats(Subject $subject): array
    {
        $items = LibraryItem::where('subject_id', $subject->id)->get();
        $totalDownloads = $items->sum('download_count');
        $totalViews = $items->sum('view_count');
        $totalRatings = $items->sum('total_ratings');
        $averageRating = $items->where('total_ratings', '>', 0)->avg('average_rating');

        return [
            'total_items' => $items->count(),
            'total_downloads' => $totalDownloads,
            'total_views' => $totalViews,
            'total_ratings' => $totalRatings,
            'average_rating' => round($averageRating ?? 0, 2),
            'items_by_category' => $this->getItemsByCategory($items),
            'items_by_type' => $this->getItemsByType($items),
        ];
    }

    /**
     * الحصول على أكثر العناصر تحميلاً
     */
    public function getPopularItems(int $limit = 10, ?string $type = null): array
    {
        $query = LibraryItem::with(['category', 'subject', 'uploader'])
                           ->orderBy('download_count', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * الحصول على أكثر العناصر مشاهدة
     */
    public function getMostViewedItems(int $limit = 10, ?string $type = null): array
    {
        $query = LibraryItem::with(['category', 'subject', 'uploader'])
                           ->orderBy('view_count', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * الحصول على أكثر العناصر تقييماً
     */
    public function getHighestRatedItems(int $limit = 10, ?string $type = null): array
    {
        $query = LibraryItem::with(['category', 'subject', 'uploader'])
                           ->where('total_ratings', '>', 0)
                           ->orderBy('average_rating', 'desc')
                           ->orderBy('total_ratings', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * الحصول على نشاط المستخدم
     */
    public function getUserActivity(User $user): array
    {
        $downloads = $user->libraryDownloads()->with('item')->recent(30)->get();
        $views = $user->libraryViews()->with('item')->recent(30)->get();
        $ratings = $user->libraryRatings()->with('item')->recent(30)->get();

        return [
            'total_downloads' => $user->libraryDownloads()->count(),
            'total_views' => $user->libraryViews()->count(),
            'total_ratings' => $user->libraryRatings()->count(),
            'recent_downloads' => $downloads,
            'recent_views' => $views,
            'recent_ratings' => $ratings,
            'downloads_by_day' => $this->getUserDownloadsByDay($user),
            'views_by_day' => $this->getUserViewsByDay($user),
        ];
    }

    /**
     * الحصول على التحميلات حسب اليوم
     */
    private function getDownloadsByDay(LibraryItem $item, int $days = 30): array
    {
        return $item->downloads()
                   ->select(DB::raw('DATE(downloaded_at) as date'), DB::raw('COUNT(*) as count'))
                   ->where('downloaded_at', '>=', now()->subDays($days))
                   ->groupBy('date')
                   ->orderBy('date', 'asc')
                   ->get()
                   ->pluck('count', 'date')
                   ->toArray();
    }

    /**
     * الحصول على المشاهدات حسب اليوم
     */
    private function getViewsByDay(LibraryItem $item, int $days = 30): array
    {
        return $item->views()
                   ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('COUNT(*) as count'))
                   ->where('viewed_at', '>=', now()->subDays($days))
                   ->groupBy('date')
                   ->orderBy('date', 'asc')
                   ->get()
                   ->pluck('count', 'date')
                   ->toArray();
    }

    /**
     * الحصول على العناصر حسب التصنيف
     */
    private function getItemsByCategory($items): array
    {
        return $items->groupBy('category_id')
                    ->map(function($categoryItems) {
                        return $categoryItems->count();
                    })
                    ->toArray();
    }

    /**
     * الحصول على العناصر حسب النوع
     */
    private function getItemsByType($items): array
    {
        return $items->groupBy('type')
                    ->map(function($typeItems) {
                        return $typeItems->count();
                    })
                    ->toArray();
    }

    /**
     * الحصول على تحميلات المستخدم حسب اليوم
     */
    private function getUserDownloadsByDay(User $user, int $days = 30): array
    {
        return $user->libraryDownloads()
                   ->select(DB::raw('DATE(downloaded_at) as date'), DB::raw('COUNT(*) as count'))
                   ->where('downloaded_at', '>=', now()->subDays($days))
                   ->groupBy('date')
                   ->orderBy('date', 'asc')
                   ->get()
                   ->pluck('count', 'date')
                   ->toArray();
    }

    /**
     * الحصول على مشاهدات المستخدم حسب اليوم
     */
    private function getUserViewsByDay(User $user, int $days = 30): array
    {
        return $user->libraryViews()
                   ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('COUNT(*) as count'))
                   ->where('viewed_at', '>=', now()->subDays($days))
                   ->groupBy('date')
                   ->orderBy('date', 'asc')
                   ->get()
                   ->pluck('count', 'date')
                   ->toArray();
    }
}

