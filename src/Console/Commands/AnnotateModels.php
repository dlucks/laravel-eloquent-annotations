<?php

namespace EloquentAnnotations\Console\Commands;

use EloquentAnnotations\ClassParser;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Finder;

class AnnotateModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'models:annotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '...';

    /**
     * @param ClassParser $parser
     */
    public function handle(ClassParser $parser)
    {
        // TODO: load from config file.
        $directory = app_path() . '/Models';

        $modelInformation = $this->collectModelInformation($directory, $parser);

        foreach ($modelInformation as $class => $classInformation) {

            $newLines = [];
            $oldLines = file($classInformation['file']);
            $classEntered = false;

            foreach ($oldLines as $line) {

                // Skip existing class PHPDoc.
                if (starts_with($line, ['/**', ' *', ' */']) && !$classEntered) {
                    continue;
                }

                if (starts_with($line, 'class ')) {

                    $newLines[] = "/**\n";

                    foreach ($classInformation['columns'] as $column) {
                        $newLines[] = " * @property \$$column\n";
                    }

                    $newLines[] = " */\n";

                    $classEntered = true;
                }

                $newLines[] = $line;
            }

            // TODO: keep file permissions, ownership, etc.
            file_put_contents($classInformation['file'], $newLines);

            $this->info('updated: ' . $class);
        }
    }

    /**
     * @param string $directory
     * @param ClassParser $parser
     * @return array
     */
    protected function collectModelInformation($directory, ClassParser $parser)
    {
        $models = [];

        foreach (Finder::create()->in($directory)->name('*.php') as $file) {

            $parser->parse($file->getRealPath());

            $modelClass = $parser->getFullQualifiedClass();

            try {
                $modelInstance = new $modelClass();
            }

            catch (\Exception $e) {
                $this->warn($e->getMessage());
                continue;
            }

            if (!($modelInstance instanceof Model)) {
                $this->warn('skipped: ' . $modelClass);
                continue;
            }

            $models[$modelClass] = [
                'file' => $file->getRealPath(),
                'columns' => Schema::getColumnListing($modelInstance->getTable())
            ];

        }

        return $models;
    }
}
