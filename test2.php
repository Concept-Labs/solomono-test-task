<?php
use PDO;
use Traversable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

const DRIVER = 'mysql';
const DB_HOST = 'localhost';
const DB_NAME = 'solomono_test';
const DB_USER = '';
const DB_PASSWORD = '';

class TreeBuilder
{
    private ?PDO $pdo = null;
    private ?array $tree = null;
    private ?float $time = null;
    private ?float $memoryUsage = null;
    private ?float $memoryPeakUsage = null;

    public function tree(): array
    {
        if ($this->tree === null) {
            $this->build();
        }

        return $this->tree;
    }

    private function build()
    {
        $this->start()
        ->buildTree(
            $this->initItems(
                $this->readCategories()
            )
        )
        ->stop();
    }

    private function connect(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf('%s:host=%s;dbname=%s;charset=utf8', DRIVER, DB_HOST, DB_NAME);
            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        }

        return $this->pdo;
    }

    private function readCategories(): Traversable
    {
        $this->connect();

        $sql = 'SELECT categories_id, parent_id FROM categories';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    private function initItems(Traversable $raw): array
    {
        $items = [];
        foreach ($raw as $row) {
            $items[$row['categories_id']] = [
                'id' => $row['categories_id'],
                'parent_id' => $row['parent_id'],
                'children' => []
            ];
        }
        return $items;
    }

    private function buildTree(array $items): static
    {
        foreach ($items as $id => &$item) {
            if (isset($item['parent_id']) && $item['parent_id'] !== null) {
                $items[$item['parent_id']]['children'][$id] = &$item;
            }
        }

        unset($item);

        foreach ($items as $id => $item) {
            if (!isset($item['parent_id']) || $item['parent_id'] === null) {
                $this->tree[$id] = $this->normalizeNode($item);
            }
        }
    
        $this->tree = $this->tree[0] ?? [];

        return $this;
    }

    private function normalizeNode(array $node)
    {
        if (empty($node['children'])) {
            return $node['id'];
        }

        $result = [];
        foreach ($node['children'] as $childId => $child) {
            $result[$childId] = $this->normalizeNode($child);
        }

        return $result;
    }

    private function start(): static
    {
        $this->time = microtime(true);

        return $this;
    }

    private function stop(): static
    {
        $this->time = microtime(true) - $this->time;
        $this->memoryUsage = memory_get_usage();
        $this->memoryPeakUsage = memory_get_peak_usage();

        return $this;
    }

    public function stats(): array
    {
        return [
            'execution_time' => $this->time,
            'memory_usage' => $this->memoryUsage,
            'memory_peak_usage' => $this->memoryPeakUsage,
        ];
    }
    
}

$treeBuilder = new TreeBuilder();
$tree = $treeBuilder->tree();
$stats = $treeBuilder->stats();

?>
Execution time: <?= $stats['execution_time'] ?> sec<br>
Memory usage: <?= $stats['memory_usage'] ?> bytes<br>
Memory peak usage: <?= $stats['memory_peak_usage'] ?> bytes<br>
<hr>
<pre><?=print_r($tree, true)  ?></pre>


