<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckMissingTests extends Command
{
    protected $signature = 'test:missing
                            {--controller= : Bitta controller tekshirish (masalan: UserController)}
                            {--show-covered : Test yozilgan actionlarni ham ko\'rsatish}';

    protected $description = 'Qaysi controller actionlari uchun feature test yozilmaganini ko\'rsatadi va umumiy hisobot beradi';

    private array $coveredActions = [];

    public function handle(): int
    {
        $this->info('🔍 Testlar tekshirilmoqda...');
        $this->newLine();

        $this->parseFeatureTests();

        $controllers = $this->getControllers();

        if ($filterController = $this->option('controller')) {
            $controllers = array_filter(
                $controllers,
                fn($c) => str_contains(basename($c), $filterController)
            );
        }

        $missingActionsCount = 0;
        $coveredActionsCount = 0;
        
        $totalControllers = count($controllers);
        $controllersWithTests = 0;
        $controllersMissingTests = 0;

        foreach ($controllers as $controllerFile) {
            $result = $this->checkController($controllerFile);
            
            $missingActionsCount += $result['missing'];
            $coveredActionsCount += $result['covered'];
            
            if ($result['has_any_test']) {
                $controllersWithTests++;
            } else {
                $controllersMissingTests++;
            }
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Xulosa (Controllerlar):");
        $this->info("   🏢 Jami controllerlar    : <fg=blue>{$totalControllers}</>");
        $this->info("   ✅ Test yozilganlari     : <fg=green>{$controllersWithTests}</>");
        $this->info("   ❌ Test yozilmaganlari   : <fg=red>{$controllersMissingTests}</>");
        $this->newLine();
        
        $this->info("📊 Xulosa (Actionlar):");
        $totalActions = $missingActionsCount + $coveredActionsCount;
        $this->info("   🎯 Jami actionlar          : <fg=blue>{$totalActions}</>");
        $this->info("   ✅ Test yozilgan actionlar : <fg=green>{$coveredActionsCount}</>");
        $this->info("   ❌ Test yozilmagan actionlar: <fg=red>{$missingActionsCount}</>");

        $totalActions = $missingActionsCount + $coveredActionsCount;
        if ($totalActions > 0) {
            $percent = round(($coveredActionsCount / $totalActions) * 100);
            $color = $percent >= 80 ? 'green' : ($percent >= 50 ? 'yellow' : 'red');
            $this->info("   📈 Coverage (Actionlar)    : <fg={$color}>{$percent}%</>");
        }

        return $missingActionsCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function parseFeatureTests(): void
    {
        $testPath = base_path('tests/Feature');

        if (!File::isDirectory($testPath)) {
            $this->warn('tests/Feature papkasi topilmadi!');
            return;
        }

        $testFiles = File::allFiles($testPath);

        foreach ($testFiles as $file) {
            $content = File::get($file->getPathname());
            
            $filename = $file->getFilename();
            if (str_ends_with($filename, 'ControllerTest.php')) {
                $controllerName = str_replace('Test.php', '', $filename);
                
                if (!isset($this->coveredActions[$controllerName])) {
                    $this->coveredActions[$controllerName] = [];
                }
                
                preg_match_all(
                    "/(?:it|test)\s*\(\s*['\"]([^'\"]+)['\"]\s*,/m",
                    $content,
                    $pestMatches
                );

                if (!empty($pestMatches[1])) {
                    foreach ($pestMatches[1] as $testDescription) {
                        $this->guessActionFromTestDescription($controllerName, $testDescription);
                    }
                }
            }
        }
    }

    private function guessActionFromTestDescription(string $controllerName, string $description): void
    {
        $knownActions = [
            'index' => ['list', 'returns user list', 'index'],
            'store' => ['create', 'store', 'new', 'register', 'registers a new user'],
            'show' => ['show', 'single', 'get', 'returns authenticated user'],
            'update' => ['update', 'edit', 'changes'],
            'destroy' => ['delete', 'destroy', 'remove'],
            'login' => ['login', 'authenticate', 'credentials'],
            'logout' => ['logout', 'revokes the token'],
            'register' => ['registers a new user']
        ];

        // Specific Auth actions mapping
        if ($controllerName === 'AuthController') {
             if (str_contains(strtolower($description), 'returns authenticated user') || str_contains(strtolower($description), 'current user')) {
                 $this->coveredActions[$controllerName]['user'] = true;
             }
        }

        $normalizedDesc = strtolower($description);

        foreach ($knownActions as $action => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalizedDesc, strtolower($keyword))) {
                    $this->coveredActions[$controllerName][$action] = true;
                    break;
                }
            }
        }

        // Fallback: if description contains the action name exactly
        $words = explode(' ', str_replace(['-', '_'], ' ', $normalizedDesc));
        foreach ($words as $word) {
            // we don't know the full list of actions here, but we can just store the word as covered.
            // If the controller has an action with this name (lowercase), it will match.
            $this->coveredActions[$controllerName][$word] = true;
            // also store camelCase just in case (though we lowered the description, so we can only match lowered)
        }
        
        // Let's also just extract the action name directly if it follows "tests " or "tests action "
        if (preg_match('/tests\s+([a-zA-Z0-9_]+)/i', $description, $matches)) {
             // Store the exact case action name
             $this->coveredActions[$controllerName][$matches[1]] = true;
        }
    }

    private function getControllers(): array
    {
        $controllerPath = app_path('Http/Controllers');

        if (!File::isDirectory($controllerPath)) {
            $this->error('app/Http/Controllers papkasi topilmadi!');
            return [];
        }

        $files = File::allFiles($controllerPath);
        $controllers = [];
        foreach ($files as $f) {
            if ($f->getFilename() !== 'Controller.php') {
                $controllers[] = $f->getPathname();
            }
        }
        return $controllers;
    }

    private function checkController(string $filePath): array
    {
        $content = File::get($filePath);
        $className = $this->extractClassName($content);

        if (!$className) {
            return ['missing' => 0, 'covered' => 0, 'has_any_test' => false];
        }

        $ignoredMethods = [
            '__construct', 'middleware', 'callAction', 'authorize',
            'authorizeForUser', 'authorizeResource', 'validate',
            'validateWith', 'dispatchNow', 'dispatch', 'getMiddleware',
        ];

        preg_match_all(
            '/public\s+function\s+([a-zA-Z_]+)\s*\(/m',
            $content,
            $methodMatches
        );

        $actions = array_filter(
            $methodMatches[1],
            fn($m) => !in_array($m, $ignoredMethods)
        );

        $hasTestFile = isset($this->coveredActions[$className]);
        
        if (empty($actions)) {
            return ['missing' => 0, 'covered' => 0, 'has_any_test' => $hasTestFile];
        }

        $missingActions = [];
        $coveredActions = [];

        foreach ($actions as $action) {
            $isCovered = isset($this->coveredActions[$className][$action]);

            if ($isCovered) {
                $coveredActions[] = $action;
            } else {
                $missingActions[] = $action;
            }
        }

        $hasMissing = count($missingActions) > 0;
        $hasCovered = count($coveredActions) > 0;

        if ($hasMissing || ($this->option('show-covered') && $hasCovered)) {
            $relativePath = str_replace(base_path() . '/', '', $filePath);
            $this->line("<fg=cyan>📁 {$className}</> <fg=gray>({$relativePath})</>");

            if ($this->option('show-covered')) {
                foreach ($coveredActions as $action) {
                    $this->line("   <fg=green>✅ {$action}</>");
                }
            }

            foreach ($missingActions as $action) {
                $this->line("   <fg=red>❌ {$action}</> — test yo'q!");
            }

            $this->newLine();
        }

        return [
            'missing' => count($missingActions),
            'covered' => count($coveredActions),
            'has_any_test' => $hasTestFile
        ];
    }

    private function extractClassName(string $content): ?string
    {
        preg_match('/class\s+([A-Za-z_]+)\s/', $content, $match);
        return $match[1] ?? null;
    }
}
