<?php

namespace App\Console\Commands;

use App\Models\SubjectSection;
use App\Models\Unit;
use App\Services\Curriculum\UnitCloneService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUnitLinksCommand extends Command
{
    protected $signature = 'units:migrate-links-to-copies {--dry-run : List pivot links without migrating}';

    protected $description = 'Migrate legacy section_unit pivot links to full unit copies with sync peers';

    public function handle(UnitCloneService $cloneService): int
    {
        $rows = DB::table('section_unit')->orderBy('id')->get();

        if ($rows->isEmpty()) {
            $this->info('No legacy section_unit rows found.');

            return self::SUCCESS;
        }

        $this->info('Found '.$rows->count().' legacy link(s).');

        if ($this->option('dry-run')) {
            foreach ($rows as $row) {
                $unit = Unit::find($row->unit_id);
                $section = SubjectSection::find($row->subject_section_id);
                $this->line(sprintf(
                    '  unit #%d (%s) -> section #%d (%s)',
                    $row->unit_id,
                    $unit?->title ?? '?',
                    $row->subject_section_id,
                    $section?->title ?? '?'
                ));
            }

            return self::SUCCESS;
        }

        $migrated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $unit = Unit::find($row->unit_id);
            $section = SubjectSection::find($row->subject_section_id);

            if (! $unit || ! $section) {
                $this->warn("Skipping row #{$row->id}: missing unit or section.");
                $skipped++;

                continue;
            }

            $existing = Unit::query()
                ->where('cloned_from_unit_id', $unit->id)
                ->where('section_id', $section->id)
                ->whereNull('parent_id')
                ->exists();

            if ($existing) {
                DB::table('section_unit')->where('id', $row->id)->delete();
                $skipped++;

                continue;
            }

            try {
                $cloneService->cloneUnitTreeToSection($unit, $section);
                DB::table('section_unit')->where('id', $row->id)->delete();
                $migrated++;
                $this->line("Migrated unit #{$unit->id} -> section #{$section->id}");
            } catch (\Throwable $e) {
                $this->error("Failed row #{$row->id}: ".$e->getMessage());
            }
        }

        $this->info("Done. Migrated: {$migrated}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
