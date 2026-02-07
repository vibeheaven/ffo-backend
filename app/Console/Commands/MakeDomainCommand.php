<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDomainCommand extends Command
{
    protected $signature = 'make:ddd {name : The name of the domain}';
    protected $description = 'Create a new Domain with DDD structure';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $domainPath = app_path("Domain/{$name}");

        if ($this->files->exists($domainPath)) {
            $this->error("Domain {$name} already exists!");
            return;
        }

        $directories = [
            "$domainPath/Actions",
            "$domainPath/DataTransferObjects",
            "$domainPath/Models",
            "$domainPath/Repositories",
            app_path("Infrastructure/Repositories/{$name}"),
            app_path("Http/Controllers/Api/{$name}"),
        ];

        foreach ($directories as $dir) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        $this->createModel($name, $domainPath);
        $this->createDto($name, $domainPath);
        $this->createAction($name, $domainPath);
        $this->createController($name);
        $this->createRepositoryInterface($name, $domainPath);
        $this->createRepositoryImplementation($name);

        $this->info("Domain {$name} created successfully.");
        $this->info("Don't forget to register the repository in a ServiceProvider!");
    }

    protected function createModel($name, $path)
    {
        $content = "<?php\n\nnamespace App\Domain\\{$name}\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$name} extends Model\n{\n    protected \$guarded = [];\n}\n";
        $this->files->put("$path/Models/{$name}.php", $content);
    }

    protected function createDto($name, $path)
    {
        $content = "<?php\n\nnamespace App\Domain\\{$name}\DataTransferObjects;\n\nclass {$name}DTO\n{\n    public function __construct(\n        // public readonly string \$title,\n    ) {}\n\n    public static function fromRequest(\$request): self\n    {\n        return new self(\n            // \$request->validated('title'),\n        );\n    }\n}\n";
        $this->files->put("$path/DataTransferObjects/{$name}DTO.php", $content);
    }

    protected function createAction($name, $path)
    {
        $content = "<?php\n\nnamespace App\Domain\\{$name}\Actions;\n\nuse App\Domain\\{$name}\Models\\{$name};\nuse App\Domain\\{$name}\DataTransferObjects\\{$name}DTO;\n\nclass Create{$name}Action\n{\n    public function execute({$name}DTO \$dto): {$name}\n    {\n        return {$name}::create([\n            // 'title' => \$dto->title,\n        ]);\n    }\n}\n";
        $this->files->put("$path/Actions/Create{$name}Action.php", $content);
    }

    protected function createRepositoryInterface($name, $path)
    {
        $content = "<?php\n\nnamespace App\Domain\\{$name}\Repositories;\n\nuse App\Domain\\{$name}\Models\\{$name};\n\ninterface {$name}RepositoryInterface\n{\n    public function all();\n    public function find(int \$id): ?{$name};\n}\n";
        $this->files->put("$path/Repositories/{$name}RepositoryInterface.php", $content);
    }

    protected function createRepositoryImplementation($name)
    {
        $content = "<?php\n\nnamespace App\Infrastructure\Repositories\\{$name};\n\nuse App\Domain\\{$name}\Repositories\\{$name}RepositoryInterface;\nuse App\Domain\\{$name}\Models\\{$name};\n\nclass {$name}Repository implements {$name}RepositoryInterface\n{\n    public function all()\n    {\n        return {$name}::all();\n    }\n\n    public function find(int \$id): ?{$name}\n    {\n        return {$name}::find(\$id);\n    }\n}\n";
        $this->files->put(app_path("Infrastructure/Repositories/{$name}/{$name}Repository.php"), $content);
    }

    protected function createController($name)
    {
        $content = "<?php\n\nnamespace App\Http\Controllers\Api\\{$name};\n\nuse App\Http\Controllers\Controller;\nuse App\Traits\ApiResponse;\n\nclass {$name}Controller extends Controller\n{\n    use ApiResponse;\n\n    /**\n     * @OA\Get(\n     *      path=\"/api/{$name}s\",\n     *      operationId=\"get{$name}sList\",\n     *      tags={\"{$name}s\"},\n     *      summary=\"Get list of {$name}s\",\n     *      description=\"Returns list of {$name}s\",\n     *      @OA\Response(\n     *          response=200,\n     *          description=\"Successful operation\",\n     *       )\n     *     )\n     */\n    public function index()\n    {\n        return \$this->success([], 'List of {$name}s');\n    }\n}\n";
        $this->files->put(app_path("Http/Controllers/Api/{$name}/{$name}Controller.php"), $content);
    }
}
