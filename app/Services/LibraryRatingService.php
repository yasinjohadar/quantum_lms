<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\LibraryRating;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LibraryRatingService
{
    /**
     * تقييم عنصر
     */
    public function rateItem(LibraryItem $item, User $user, int $rating, ?string $comment = null): LibraryRating
    {
        // التحقق من أن التقييم بين 1 و 5
        $rating = max(1, min(5, $rating));

        $libraryRating = LibraryRating::updateOrCreate(
            [
                'library_item_id' => $item->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $rating,
                'comment' => $comment,
            ]
        );

        // تحديث متوسط التقييم للعنصر
        $item->calculateAverageRating();

        Log::info('Library item rated', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'rating' => $rating,
        ]);

        return $libraryRating;
    }

    /**
     * تحديث التقييم
     */
    public function updateRating(LibraryRating $rating, int $newRating, ?string $comment = null): LibraryRating
    {
        $newRating = max(1, min(5, $newRating));

        $rating->update([
            'rating' => $newRating,
            'comment' => $comment,
        ]);

        // تحديث متوسط التقييم للعنصر
        $rating->item->calculateAverageRating();

        Log::info('Library rating updated', [
            'rating_id' => $rating->id,
            'new_rating' => $newRating,
        ]);

        return $rating->fresh();
    }

    /**
     * حذف التقييم
     */
    public function deleteRating(LibraryRating $rating): bool
    {
        $item = $rating->item;
        $rating->delete();

        // تحديث متوسط التقييم للعنصر
        $item->calculateAverageRating();

        Log::info('Library rating deleted', ['rating_id' => $rating->id]);

        return true;
    }

    /**
     * الحصول على تقييمات عنصر معين
     */
    public function getItemRatings(LibraryItem $item, int $perPage = 10)
    {
        return $item->ratings()
                   ->with('user')
                   ->orderBy('created_at', 'desc')
                   ->paginate($perPage);
    }

    /**
     * حساب متوسط التقييم
     */
    public function calculateAverageRating(LibraryItem $item): float
    {
        $ratings = $item->ratings;
        $totalRatings = $ratings->count();

        if ($totalRatings === 0) {
            return 0;
        }

        $averageRating = $ratings->avg('rating');
        $item->update([
            'average_rating' => round($averageRating, 2),
            'total_ratings' => $totalRatings,
        ]);

        return round($averageRating, 2);
    }

    /**
     * الحصول على توزيع التقييمات
     */
    public function getRatingDistribution(LibraryItem $item): array
    {
        $ratings = $item->ratings;
        $distribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];

        foreach ($ratings as $rating) {
            $distribution[$rating->rating] = ($distribution[$rating->rating] ?? 0) + 1;
        }

        return $distribution;
    }
}

