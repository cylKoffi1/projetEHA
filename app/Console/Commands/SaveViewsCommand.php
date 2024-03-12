<?php
// app/Console/Commands/SaveViewsCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\View;

class SaveViewsCommand extends Command
{
    protected $signature = 'save:views';
    protected $description = 'Save views in the database';

    public function handle()
    {
        $viewsPath = resource_path('views');

        $views = $this->getViews($viewsPath);

        foreach ($views as $view) {
            View::create(['path' => $view]);
            $this->info("View saved: $view");
        }

        $this->info('Views saved successfully.');
    }

    private function getViews($path)
    {
        $views = [];

        $files = File::allFiles($path);

        foreach ($files as $file) {
            $viewPath = str_replace([$path, '.blade.php'], '', $file->getPathname());
            $view = str_replace(DIRECTORY_SEPARATOR, '.', $viewPath);
            $views[] = $view;
        }

        return $views;
    }
}
