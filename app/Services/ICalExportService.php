<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ICalExportService
{
    /**
     * تصدير الأحداث إلى iCal
     */
    public function exportToICal(Collection $events, User $user): string
    {
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Quantum LMS//Calendar//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:Quantum LMS Calendar\r\n";
        $ical .= "X-WR-TIMEZONE:Asia/Riyadh\r\n";

        foreach ($events as $event) {
            $ical .= $this->formatEventForICal($event);
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * إنشاء ملف .ics
     */
    public function generateICSFile(Collection $events, User $user): Response
    {
        $icalContent = $this->exportToICal($events, $user);
        $filename = 'quantum_lms_calendar_' . now()->format('Y-m-d') . '.ics';

        return response($icalContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($icalContent),
        ]);
    }

    /**
     * تنسيق حدث واحد لـ iCal
     */
    private function formatEventForICal(array $event): string
    {
        $ical = "BEGIN:VEVENT\r\n";
        $ical .= "UID:" . $event['id'] . "@quantum-lms.com\r\n";
        $ical .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
        
        $startDate = Carbon::parse($event['start']);
        $ical .= "DTSTART" . ($event['allDay'] ?? false ? ";VALUE=DATE" : "") . ":" . 
                 $this->formatDateForICal($startDate, $event['allDay'] ?? false) . "\r\n";

        if (isset($event['end']) && $event['end']) {
            $endDate = Carbon::parse($event['end']);
            $ical .= "DTEND" . ($event['allDay'] ?? false ? ";VALUE=DATE" : "") . ":" . 
                     $this->formatDateForICal($endDate, $event['allDay'] ?? false) . "\r\n";
        } else {
            // إذا لم يكن هناك تاريخ انتهاء، نضيف ساعة واحدة
            $endDate = $startDate->copy()->addHour();
            $ical .= "DTEND:" . $this->formatDateForICal($endDate, false) . "\r\n";
        }

        $ical .= "SUMMARY:" . $this->escapeICalText($event['title']) . "\r\n";

        if (isset($event['description']) && $event['description']) {
            $ical .= "DESCRIPTION:" . $this->escapeICalText($event['description']) . "\r\n";
        }

        if (isset($event['location']) && $event['location']) {
            $ical .= "LOCATION:" . $this->escapeICalText($event['location']) . "\r\n";
        }

        $ical .= "STATUS:CONFIRMED\r\n";
        $ical .= "SEQUENCE:0\r\n";
        $ical .= "END:VEVENT\r\n";

        return $ical;
    }

    /**
     * تنسيق التاريخ لـ iCal
     */
    private function formatDateForICal(Carbon $date, bool $allDay): string
    {
        if ($allDay) {
            return $date->format('Ymd');
        }
        return $date->utc()->format('Ymd\THis\Z');
    }

    /**
     * تهريب النص لـ iCal
     */
    private function escapeICalText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);
        return $text;
    }
}

