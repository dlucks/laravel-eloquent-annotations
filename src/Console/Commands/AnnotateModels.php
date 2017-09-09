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
    protected $description = 'Annotate Eloquent model classes with @property tags';

    /**
     * @param ClassParser $parser
     */
    public function handle(ClassParser $parser)
    {
        $directory = config('eloquent_annotations.model_directory', app_path());

        $modelInformation = $this->collectModelInformation($directory, $parser);

        foreach ($modelInformation as $class => $classInformation) {

            $newLines = [];
            $oldLines = file($classInformation['file']);
            $classEntered = false;

            foreach ($oldLines as $line) {

                // Remove existing class PHPDoc.
                if (starts_with($line, ['/**', ' *', ' */']) && !$classEntered) {
                    continue;
                }

                // Add new class PHPDoc.
                if (starts_with($line, 'class ')) {
                    $newLines = array_merge($newLines, $this->createDocLines($classInformation['columns']));
                    $classEntered = true;
                }

                $newLines[] = $line;
            }

            file_put_contents($classInformation['file'], $newLines);

            $this->info('updated: ' . $class);
        }
    }

    /**
     * @param array $columns
     * @return array
     */
    protected function createDocLines($columns)
    {
        $lines[] = "/**\n";

        foreach ($columns as $column) {
            $lines[] = " * @property \$$column\n";
        }

        $lines[] = " */\n";

        return $lines;
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

            // We are only interested in classes extending `Model`.
            if (!$this->extendsEloquentModelClass($modelClass)) {
                $this->warn('skipped: ' . $modelClass);
                continue;
            }

            try {
                $modelInstance = new $modelClass();
            }

            catch (\Exception $e) {
                $this->warn($e->getMessage());
                continue;
            }

            $models[$modelClass] = [
                'file' => $file->getRealPath(),
                'columns' => Schema::getColumnListing($modelInstance->getTable())
            ];
        }

        return $models;
    }

    /**
     * @param string $class
     * @return bool
     */
    protected function extendsEloquentModelClass($class)
    {
        $instance = new \ReflectionClass($class);

        while ($instance = $instance->getParentClass()) {
            if ($instance->getName() === Model::class) {
                return true;
            }
        }

        return false;
    }
}
