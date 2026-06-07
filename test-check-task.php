<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = App\Models\Task::query()->find(1);

if ($task === null) {
    echo "task not found\n";
    exit(1);
}

echo 'status=' . $task->status->value . "\n";
echo 'updated_at=' . $task->updated_at?->toISOString() . "\n";
echo 'cutoff=' . now()->subDays(30)->toISOString() . "\n";
echo 'is_stale=' . ($task->updated_at <= now()->subDays(30) ? 'yes' : 'no') . "\n";
