<?php

namespace SSA\handler;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SSA\library\StorageServiceInterface;

class NewsHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $storage = $request->getAttribute(StorageServiceInterface::class);
        $method = $request->getMethod();
        $route = $request->getAttribute('route');
        $orgId = $route ? $route->getParameter('orgId') : null;
        $newsId = $route ? $route->getParameter('newsId') : null;
        $path = $request->getUri()->getPath();

        if (str_ends_with($path, '/rss')) {
            return $this->generateRss($storage, $orgId);
        }

        switch ($method) {
            case 'GET':
                return $newsId ? $this->getNews($storage, $newsId) : $this->listNews($storage, $orgId, $request);
            case 'POST':
                return $this->createNews($storage, $orgId, $request);
            case 'PUT':
                return $this->updateNews($storage, $newsId, $request);
            case 'DELETE':
                return $this->deleteNews($storage, $newsId);
            default:
                return new Response(405, [], 'Method Not Allowed');
        }
    }

    private function listNews(StorageServiceInterface $storage, ?string $orgId, ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $onlyPublished = ($queryParams['published'] ?? 'false') === 'true';
        
        if ($orgId) {
            $news = $storage->listNews($orgId, $onlyPublished);
        } else {
            $news = $storage->listAllNews($onlyPublished);
        }
        
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($news));
    }

    private function getNews(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        $news = $storage->getNews($id);
        if (!$news) {
            return new Response(404, [], 'News Not Found');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($news));
    }

    private function createNews(StorageServiceInterface $storage, string $orgId, ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }
        $data['organizationId'] = $orgId;
        $id = $storage->createNews($data);
        return new Response(201, ['Content-Type' => 'application/json'], json_encode(['id' => $id]));
    }

    private function updateNews(StorageServiceInterface $storage, string $id, ServerRequestInterface $request): ResponseInterface
    {
        if (!$id) {
            return new Response(400, [], 'News ID required');
        }
        $data = json_decode((string)$request->getBody(), true);
        if (!$data) {
            return new Response(400, [], 'Invalid JSON');
        }
        $success = $storage->updateNews($id, $data);
        if (!$success) {
            return new Response(404, [], 'News Not Found or Update Failed');
        }
        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'updated']));
    }

    private function deleteNews(StorageServiceInterface $storage, string $id): ResponseInterface
    {
        if (!$id) {
            return new Response(400, [], 'News ID required');
        }
        $storage->deleteNews($id);
        return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'deleted']));
    }

    private function generateRss(StorageServiceInterface $storage, string $orgId): ResponseInterface
    {
        $organization = $storage->getOrganization($orgId);
        if (!$organization) {
            return new Response(404, [], 'Organization Not Found');
        }

        $news = $storage->listNews($orgId, true);
        
        $rss = '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
        $rss .= '<rss version="2.0">' . PHP_EOL;
        $rss .= '  <channel>' . PHP_EOL;
        $rss .= '    <title>' . htmlspecialchars($organization['name']) . ' News</title>' . PHP_EOL;
        $rss .= '    <link>' . htmlspecialchars($organization['website'] ?? '') . '</link>' . PHP_EOL;
        $rss .= '    <description>Latest news from ' . htmlspecialchars($organization['name']) . '</description>' . PHP_EOL;
        
        foreach ($news as $item) {
            $rss .= '    <item>' . PHP_EOL;
            $rss .= '      <title>' . htmlspecialchars($item['title']) . '</title>' . PHP_EOL;
            $rss .= '      <description>' . htmlspecialchars($item['content']) . '</description>' . PHP_EOL;
            if ($item['publishDate']) {
                $rss .= '      <pubDate>' . date(DATE_RSS, strtotime($item['publishDate'])) . '</pubDate>' . PHP_EOL;
            }
            $rss .= '      <guid>' . $item['id'] . '</guid>' . PHP_EOL;
            $rss .= '    </item>' . PHP_EOL;
        }
        
        $rss .= '  </channel>' . PHP_EOL;
        $rss .= '</rss>';

        return new Response(200, ['Content-Type' => 'application/rss+xml'], $rss);
    }
}
