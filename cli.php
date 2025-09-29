<?php
// Simple CLI for QuikAPI
// Usage examples:
// php QuikAPI/cli.php make:controller User
// php QuikAPI/cli.php make:model User
// php QuikAPI/cli.php make:module Post fillable="title,body,user_id" relations="user:belongsTo:User,user_id,id;comments:hasMany:Comment,post_id,id"

$root = dirname(__DIR__);
$base = __DIR__;

function studly($name) { return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))); }

function parseOptions(array $argv): array {
    $opts = [];
    foreach ($argv as $arg) {
        if (str_contains($arg, '=')) {
            [$k, $v] = explode('=', $arg, 2);
            $opts[$k] = trim($v, "\"' ");
        }
    }
    return $opts;
}

function writeFileIfNotExists($path, $content) {
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    if (file_exists($path)) {
        echo "Skip (exists): $path\n";
        return false;
    }
    file_put_contents($path, $content);
    echo "Created: $path\n";
    return true;
}

function makeController($base, $name) {
    $class = studly($name) . 'Controller';
    $ns = 'QuikAPI\\Controllers';
    $path = $base . "/Controllers/{$class}.php";
    $tpl = <<<PHP
<?php
namespace $ns;

use QuikAPI\\Http\\Request;
use QuikAPI\\Support\\Responses;

class $class
{
    public function index(Request $req): array { return ['items' => []]; }
    public function show(Request $req): array { return ['item' => ['id' => $req->params['id'] ?? null]]; }
    public function store(Request $req): array { return Responses::success(['created' => $req->body]); }
    public function update(Request $req): array { return Responses::success(['updated' => ['id' => $req->params['id'] ?? null] + $req->body]); }
    public function destroy(Request $req): array { return Responses::success(['deleted' => ['id' => $req->params['id'] ?? null]]); }
}
PHP;
    return writeFileIfNotExists($path, $tpl);
}

function makeModel($base, $name) {
    $class = studly($name);
    $ns = 'QuikAPI\\Models';
    $path = $base . "/Models/{$class}.php";
    $tpl = <<<PHP
<?php
namespace $ns;

use Illuminate\\Database\\Eloquent\\Model;

class $class extends Model
{
    protected \$fillable = [];
}
PHP;
    return writeFileIfNotExists($path, $tpl);
}

function appendRoutes($base, $name) {
    $lower = strtolower($name);
    $routesFile = $base . '/routes.php';
    $snippet = "\n// {$stud} resource routes\n".
               "use QuikAPI\\Controllers\\{$stud}Controller;\n".
               "// $lower routes\n".
               "\\$router->get('/$lower', [{$stud}Controller::class, 'index']);\n".
               "\\$router->post('/$lower', [{$stud}Controller::class, 'store']);\n".
               "\\$router->get('/$lower/{id}', [{$stud}Controller::class, 'show']);\n".
               "\\$router->put('/$lower/{id}', [{$stud}Controller::class, 'update']);\n".
               "\\$router->delete('/$lower/{id}', [{$stud}Controller::class, 'destroy']);\n";

    $current = file_exists($routesFile) ? file_get_contents($routesFile) : '';
    if (strpos($current, "Controllers\\{$stud}Controller") !== false) {
        echo "Routes already contain {$stud}Controller entries. Skipping route append.\n";
        return false;
    }
    file_put_contents($routesFile, rtrim($current) . "\n" . $snippet);
    echo "Appended routes for {$stud}.\n";
    return true;
}

function relationMethodTemplate($name, $type, $related, $fk=null, $rk=null): string {
    $method = strtolower($name);
    $relatedStud = studly($related);
    $args = ["\\QuikAPI\\Models\\$relatedStud::class"];
    if ($fk) $args[] = var_export($fk, true);
    if ($rk) $args[] = var_export($rk, true);
    $argsStr = implode(', ', $args);
    return "    public function {$method}() { return \$this->{$type}({$argsStr}); }\n";
}

function buildModelTemplate($name, array $fillable, array $relations): string {
    $class = studly($name);
    $fillableArr = array_map(fn($f)=>"'".trim($f)."'", array_filter($fillable));
    $fillableStr = implode(', ', $fillableArr);
    $relationsCode = '';
    foreach ($relations as $rel) {
        // relation string: name:type:Related[,fk,rk]
        $parts = array_map('trim', explode(':', $rel));
        if (count($parts) >= 3) {
            [$rName, $rType, $rRelated] = $parts;
            $fk = $parts[3] ?? null; $rk = $parts[4] ?? null;
            $relationsCode .= relationMethodTemplate($rName, $rType, $rRelated, $fk, $rk);
        }
    }
    return <<<PHP
<?php
namespace QuikAPI\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class $class extends Model
{
    protected \$fillable = [$fillableStr];

$relationsCode}
PHP;
}

function buildRequests($name): array {
    $stud = studly($name);
    $ns = 'QuikAPI\\Requests\\' . $stud;
    $baseReq = <<<PHP
<?php
namespace QuikAPI\\Requests;

use QuikAPI\\Http\\Request;

abstract class FormRequest
{
    abstract public function rules(): array;
    public function messages(): array { return []; }
    public function authorize(): bool { return true; }

    public function validated(Request $req): array
    {
        if (!isset(\$GLOBALS['quikapi']['validator'])) return \$req->body;
        \$factory = \$GLOBALS['quikapi']['validator'];
        \$data = array_merge(\$req->query, \$req->body);
        \$v = \$factory->make(\$data, \$this->rules(), \$this->messages());
        if (\$v->fails()) {
            throw new \InvalidArgumentException(json_encode(\$v->errors()->toArray()));
        }
        return \$v->validated();
    }
}
PHP;
    $index = <<<PHP
<?php
namespace $ns;

use QuikAPI\\Requests\\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:200',
            'operations' => 'sometimes|array',
            'select' => 'sometimes|array',
            'order_by' => 'sometimes|string',
            'order_type' => 'sometimes|in:asc,desc',
            'group_by' => 'sometimes|string|nullable',
            'return_type' => 'sometimes|in:data,count',
            'with' => 'sometimes|string',
        ];
    }
}
PHP;
    $store = <<<PHP
<?php
namespace $ns;

use QuikAPI\\Requests\\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}
PHP;
    $update = <<<PHP
<?php
namespace $ns;

use QuikAPI\\Requests\\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}
PHP;
    return [$baseReq, $index, $store, $update];
}

function buildControllerTemplate($name): string {
    $stud = studly($name);
    $model = "\\\\QuikAPI\\\\Models\\\\$stud";
    return <<<PHP
<?php
namespace QuikAPI\\Controllers;

use QuikAPI\\Http\\Request;
use QuikAPI\\Support\\Responses;
use QuikAPI\\Support\\QueryOps;
use $model as Model;
use QuikAPI\\Requests\\{$stud}\\IndexRequest;
use QuikAPI\\Requests\\{$stud}\\StoreRequest;
use QuikAPI\\Requests\\{$stud}\\UpdateRequest;

class {$stud}Controller
{
    public function index(Request $request): array
    {
        try {
            $validated = (new IndexRequest())->validated($request);

            $perPage = $validated['per_page'] ?? 25;
            $operations = $validated['operations'] ?? [];
            $select = $validated['select'] ?? [];
            $orderBy = $validated['order_by'] ?? 'id';
            $orderType = $validated['order_type'] ?? 'desc';
            $groupBy = $validated['group_by'] ?? null;
            $returnType = $validated['return_type'] ?? 'data';
            $with = $validated['with'] ?? '';

            $query = Model::query();
            if ($select) { $query->select($select); }
            $user = $request->authUser;
            if ($user) { $query->where('user_id', $user->id); }
            $query = QueryOps::addOperationsInQuery($query, $operations);
            if ($groupBy) { $query->groupBy($groupBy); }
            if ($orderBy) { $query->orderBy($orderBy, $orderType); }

            if ($returnType === 'count') {
                $data = $query->count();
            } else {
                $data = $query->paginate($perPage);
                if ($with) { $rels = explode(',', $with); $data->load($rels); }
            }
            return Responses::success($data, 200);
        } catch (\Illuminate\\Database\\QueryException $ex) {
            return Responses::fail([$ex->getMessage()], 500);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function show(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $with = $request->input('with', '');
            $m = Model::query();
            if ($with) { $m->with(explode(',', $with)); }
            $row = $m->findOrFail($id);
            return Responses::success($row, 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 404);
        }
    }

    public function store(Request $request): array
    {
        try {
            $validated = (new StoreRequest())->validated($request);
            $row = Model::create($validated);
            return Responses::success($row, 201);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function update(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $validated = (new UpdateRequest())->validated($request);
            $row = Model::findOrFail($id);
            $row->fill($validated);
            $row->save();
            return Responses::success($row, 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function destroy(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $row = Model::findOrFail($id);
            $row->delete();
            return Responses::success(['deleted' => $id], 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }
}
PHP;
}

function makeRichModel($base, $name, array $fillable, array $relations): bool {
    $modelPath = $base . "/Models/" . studly($name) . ".php";
    $tpl = buildModelTemplate($name, $fillable, $relations);
    return writeFileIfNotExists($modelPath, $tpl);
}

function makeRequests($base, $name): bool {
    [$baseReq, $index, $store, $update] = buildRequests($name);
    $changed = false;
    $changed |= writeFileIfNotExists($base . "/Requests/FormRequest.php", $baseReq);
    $nsDir = $base . "/Requests/" . studly($name);
    $changed |= writeFileIfNotExists($nsDir . "/IndexRequest.php", $index);
    $changed |= writeFileIfNotExists($nsDir . "/StoreRequest.php", $store);
    $changed |= writeFileIfNotExists($nsDir . "/UpdateRequest.php", $update);
    return (bool)$changed;
}

function makeRichController($base, $name): bool {
    $controllerPath = $base . "/Controllers/" . studly($name) . "Controller.php";
    $tpl = buildControllerTemplate($name);
    return writeFileIfNotExists($controllerPath, $tpl);
}

function makeModule($base, $name, array $opts = []) {
    $fillable = isset($opts['fillable']) ? array_map('trim', explode(',', $opts['fillable'])) : [];
    $relations = isset($opts['relations']) ? array_map('trim', explode(';', $opts['relations'])) : [];

    $ok1 = makeRichModel($base, $name, $fillable, $relations);
    $ok2 = makeRequests($base, $name);
    $ok3 = makeRichController($base, $name);
    $ok4 = appendRoutes($base, $name);
    return $ok1 || $ok2 || $ok3 || $ok4;
}

array_shift($argv); // script
$command = $argv[0] ?? '';
$name = $argv[1] ?? '';
$opts = parseOptions(array_slice($argv, 2));

if (!$command) {
    echo "QuikAPI CLI\n";
    echo "Usage:\n";
    echo "  php QuikAPI/cli.php make:controller Name\n";
    echo "  php QuikAPI/cli.php make:model Name\n";
    echo "  php QuikAPI/cli.php make:module Name fillable=\"col1,col2\" relations=\"user:belongsTo:User,user_id,id;comments:hasMany:Comment,post_id,id\"\n";
    exit(0);
}

switch ($command) {
    case 'make:controller':
        if (!$name) { echo "Name required\n"; exit(1);} 
        makeController($base, $name);
        break;
    case 'make:model':
        if (!$name) { echo "Name required\n"; exit(1);} 
        makeModel($base, $name);
        break;
    case 'make:module':
        if (!$name) { echo "Name required\n"; exit(1);} 
        makeModule($base, $name, $opts);
        break;
    default:
        echo "Unknown command: $command\n";
        exit(1);
}
