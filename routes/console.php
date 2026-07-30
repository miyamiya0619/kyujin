<?php

use App\Console\Commands\CloseExpiredJobPostingsCommand;
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
