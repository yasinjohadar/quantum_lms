<?php

namespace App\Events;

use App\Models\LibraryItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * حدث داخلي لإشعار الطلاب عند إضافة عنصر للمكتبة (بدون بث WebSocket مباشر).
 */
class LibraryItemCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LibraryItem $item
    ) {}
}
