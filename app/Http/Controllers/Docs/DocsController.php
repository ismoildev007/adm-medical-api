<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class DocsController extends Controller
{
    public function apiIndex()
    {
        return $this->successResponse($this->docsList());
    }

    public function apiShow(string $name)
    {
        $content = $this->readDocContent($name);

        return $this->successResponse([
            'name' => $name,
            'filename' => $name.'.md',
            'content' => $content,
        ]);
    }

    public function index()
    {
        return view('docs.index', [
            'docs' => $this->docsList(),
        ]);
    }

    public function show(string $name)
    {
        $content = $this->readDocContent($name);

        if ($name === 'tables') {
            $dbml = $this->extractDbmlFromMarkdown($content);
            $via = null;
            $svg = null;
            $diagramError = null;

            try {
                $result = KrokiErDiagramService::fromConfig()->dbmlToSvgWithMeta($dbml);
                $svg = $result['svg'];
                $via = $result['via'];
            } catch (Throwable $e) {
                $diagramError = $e->getMessage();
            }

            return view('docs.tables', [
                'name' => $name,
                'filename' => $name.'.md',
                'dbml' => $dbml,
                'via' => $via,
                'svg' => $svg,
                'diagramError' => $diagramError,
            ]);
        }

        return view('docs.show', [
            'name' => $name,
            'filename' => $name.'.md',
            'content' => $content,
            'html' => Str::markdown($content),
        ]);
    }

    private function docsList(): array
    {
        $docsPath = base_path('docs');

        if (! File::isDirectory($docsPath)) {
            return [];
        }

        return collect(File::files($docsPath))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'md')
            ->sortBy(fn ($file) => $file->getFilename())
            ->map(function ($file) {
                $name = $file->getFilenameWithoutExtension();

                return [
                    'name' => $name,
                    'filename' => $file->getFilename(),
                    'web_url' => '/docs/'.$name,
                    'api_url' => url('/api/v1/docs/'.$name),
                ];
            })
            ->values()
            ->all();
    }

    private function readDocContent(string $name): string
    {
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new ApiException('Invalid documentation file name.', ErrorCode::NOT_FOUND, 404);
        }

        $filePath = base_path('docs'.DIRECTORY_SEPARATOR.$name.'.md');

        if (! File::exists($filePath) || ! File::isFile($filePath)) {
            throw new ApiException('Documentation file not found.', ErrorCode::NOT_FOUND, 404);
        }

        return File::get($filePath);
    }

    private function extractDbmlFromMarkdown(string $markdown): string
    {
        $markdown = trim($markdown);
        if (preg_match('/^\s*```(?:dbml)?\s*\R(.*)\R```\s*$/s', $markdown, $matches)) {
            return trim($matches[1]);
        }

        return $markdown;
    }
}
