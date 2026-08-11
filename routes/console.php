<?php

use App\Console\Commands\CloseExpiredJobPostingsCommand;
use App\Console\Commands\GenerateAggregationFeedsCommand;
use App\Console\Commands\PurgeUnverifiedJobSeekersCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| スケジューラ
|--------------------------------------------------------------------------
| 本番は cron から毎分 `php artisan schedule:run` を実行する
| (常駐プロセスが使えないため。CLAUDE.md 4章)。
*/
Schedule::command(CloseExpiredJobPostingsCommand::class)
    ->daily()
    ->withoutOverlapping();

// アグリゲーション媒体向け XML フィードの日次生成(SPEC.md 10.2)。
Schedule::command(GenerateAggregationFeedsCommand::class)
    ->daily()
    ->withoutOverlapping();

// メール認証が24時間以内に完了しなかった仮登録の削除(TASKS.md T-27)。
Schedule::command(PurgeUnverifiedJobSeekersCommand::class)
    ->daily()
    ->withoutOverlapping();
