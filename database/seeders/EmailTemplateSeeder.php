<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;
use App\Services\Email\EmailTemplateService;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templateService = new EmailTemplateService();
        $defaultTemplates = $templateService->getDefaultTemplates();

        foreach ($defaultTemplates as $templateData) {
            EmailTemplate::updateOrCreate(
                ['slug' => $templateData['slug']],
                $templateData
            );
        }
    }
}
