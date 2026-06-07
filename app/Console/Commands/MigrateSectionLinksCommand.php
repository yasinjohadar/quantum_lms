<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Models\SubjectSection;
use App\Services\Curriculum\SectionCloneService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSectionLinksCommand extends Command
{
    protected $signature = 'sections:migrate-links-to-copies {--dry-run : List pivot links without migrating}';

    protected $description = 'Migrate legacy section_subjects pivot links to full section copies with sync peers';

    public function handle(SectionCloneService $cloneService): int
    {
        $rows = DB::table('section_subjects')->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $this->info('No legacy section_subjects rows found.');

            return self::SUCCESS;
        }

        $this->info('Found '.$rows->count().' legacy link(s).');

        if ($this->option('dry-run')) {
            foreach ($rows as $row) {
                $section = SubjectSection::find($row->section_id);
                $subject = Subject::find($row->subject_id);
                $this->line(sprintf(
                    '  section #%d (%s) -> subject #%d (%s)',
                    $row->section_id,
                    $section?->title ?? '?',
                    $row->subject_id,
                    $subject?->name ?? '?'
                ));
            }

            return self::SUCCESS;
        }

        $migrated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $section = SubjectSection::find($row->section_id);
            $subject = Subject::find($row->subject_id);

            if (! $section || ! $subject) {
                $this->warn("Skipping row #{$row->id}: missing section or subject.");
                $skipped++;

                continue;
            }

            $existing = SubjectSection::query()
                ->where('cloned_from_section_id', $section->id)
                ->where('subject_id', $subject->id)
                ->exists();

            if ($existing) {
                DB::table('section_subjects')->where('id', $row->id)->delete();
                $skipped++;

                continue;
            }

            try {
                $cloneService->cloneSectionTreeToSubject($section, $subject);
                DB::table('section_subjects')->where('id', $row->id)->delete();
                $migrated++;
                $this->line("Migrated section #{$section->id} -> subject #{$subject->id}");
            } catch (\Throwable $e) {
                $this->error("Failed row #{$row->id}: ".$e->getMessage());
            }
        }

        $this->info("Done. Migrated: {$migrated}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
