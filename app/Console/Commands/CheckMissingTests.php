<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class CheckMissingTests extends Command
{
    protected $signature = 'test:missing
                            {--controller= : Bitta controller tekshirish (masalan: UserController)}
                            {--show-covered : Test yozilgan actionlarni ham ko\'rsatish}';

    protected $description = 'Qaysi controller actionlari uchun feature test yozilmaganini ko\'rsatadi';

    private array $coveredActions = [];

    public function handle(): int
    {
        $this->info('🔍 Testlar tekshirilmoqda...');
        $this->newLine();

        // 1. Barcha feature testlarni parse qil
        $this->parsFeatureTests();

        // 2. Barcha controllerlarni tekshir
        $controllers = $this->getControllers();

        if ($filterController = $this->option('controller')) {
            $controllers = array_filter(
                $controllers,
                fn($c) => str_contains(basename($c), $filterController)
            );
        }

        $missingCount = 0;
        $coveredCount = 0;

        foreach ($controllers as $controllerFile) {
            $result = $this->checkController($controllerFile);
            $missingCount += $result['missing'];
            $coveredCount += $result['covered'];
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 Xulosa:");
        $this->info("   ✅ Test yozilgan actionlar : <fg=green>{$coveredCount}</>");
        $this->info("   ❌ Test yozilmagan actionlar: <fg=red>{$missingCount}</>");

        $total = $missingCount + $coveredCount;
        if ($total > 0) {
            $percent = round(($coveredCount / $total) * 100);
            $color = $percent >= 80 ? 'green' : ($percent >= 50 ? 'yellow' : 'red');
            $this->info("   📈 Coverage: <fg={$color}>{$percent}%</>");
        }

        return $missingCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Barcha Feature test fayllarini o'qib, qaysi controllerlar va
     * actionlar chaqirilganini aniqlaydi.
     */
    private function parsFeatureTests(): void
    {
        $testPath = base_path('tests/Feature');

        if (!File::isDirectory($testPath)) {
            $this->warn('tests/Feature papkasi topilmadi!');
            return;
        }

        $testFiles = File::allFiles($testPath);

        foreach ($testFiles as $file) {
            $content = File::get($file->getPathname());

            preg_match_all(
                '/([A-Za-z]+Controller)/',
                $content,
                $controllerMatches
            );

            preg_match_all(
                "/action\(\[([A-Za-z]+Controller)::class,\s*'([a-zA-Z_]+)'\]\)/",
                $content,
                $actionMatches,
                PREG_SET_ORDER
            );

            foreach ($actionMatches as $match) {
                $controller = $match[1];
                $action = $match[2];
                $this->coveredActions[$controller][$action] = true;
            }

            preg_match('/class\s+([A-Za-z]+Test)/', $content, $classMatch);
            if (!empty($classMatch[1])) {
                $testClassName = $classMatch[1];
                $controllerName = str_replace('Test', '', $testClassName);

                if (str_ends_with($controllerName, 'Controller')) {
                    preg_match_all(
                        '/(?:public\s+)?function\s+(test[A-Za-z_]+|[a-zA-Z_]+)\s*\(\)/m',
                        $content,
                        $methodMatches
                    );

                    foreach ($methodMatches[1] as $testMethod) {
                        if (str_starts_with($testMethod, 'test') || str_starts_with($testMethod, 'it_')) {
                            $this->guessActionFromTestMethod(
                                $controllerName,
                                $testMethod,
                                $content
                            );
                        }
                    }
                }
            }
        }
    }

    private function guessActionFromTestMethod(
        string $controllerName,
        string $testMethod,
        string $fileContent
    ): void {
        $knownActions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

        $normalizedMethod = strtolower(preg_replace('/[^a-zA-Z]/', '_', $testMethod));

        foreach ($knownActions as $action) {
            if (str_contains($normalizedMethod, $action)) {
                $this->coveredActions[$controllerName][$action] = true;
            }
        }
    }

    /**
     * app/Http/Controllers dan barcha PHP fayllarni qaytaradi.
     */
    private function getControllers(): array
    {
        $controllerPath = app_path('Http/Controllers');

        if (!File::isDirectory($controllerPath)) {
            $this->error('app/Http/Controllers papkasi topilmadi!');
            return [];
        }

        return array_map(
            fn($f) => $f->getPathname(),
            File::allFiles($controllerPath)
        );
    }

    /**
     * Bitta controllerni tekshiradi, action bo'yicha natija chiqaradi.
     */
    private function checkController(string $filePath): array
    {
        $content = File::get($filePath);
        $className = $this->extractClassName($content);

        if (!$className) {
            return ['missing' => 0, 'covered' => 0];
        }

        preg_match_all(
            '/public\s+function\s+([a-zA-Z_]+)\s*\(/m',
            $content,
            $methodMatches
        );

        $ignoredMethods = [
            '__construct', 'middleware', 'callAction', 'authorize',
            'authorizeForUser', 'authorizeResource', 'validate',
            'validateWith', 'dispatchNow', 'dispatch', 'getMiddleware',
        ];

        $actions = array_filter(
            $methodMatches[1],
            fn($m) => !in_array($m, $ignoredMethods)
        );

        if (empty($actions)) {
            return ['missing' => 0, 'covered' => 0];
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
        ];
    }

    /**
     * PHP fayldan class nomini chiqaradi.
     */
    private function extractClassName(string $content): ?string
    {
        preg_match('/class\s+([A-Za-z_]+)\s/', $content, $match);
        return $match[1] ?? null;
    }
}
