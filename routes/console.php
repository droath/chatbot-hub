<?php

use Droath\ChatbotHub\Console\Commands\MemoryCleanupCommand;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Chatbot Hub Console Routes
|--------------------------------------------------------------------------
|
| This file contains the console route definitions for the Chatbot Hub
| package. You can copy these schedules to your application's 
| routes/console.php file or bootstrap/app.php file.
|
*/

/**
 * Schedule memory cleanup based on configuration.
 * 
 * Copy this code to your application's routes/console.php file:
 */

// Memory cleanup scheduling - only if enabled in config
if (config('chatbot-hub.memory.cleanup.enabled', true)) {
    $scheduleFrequency = config('chatbot-hub.memory.cleanup.schedule', 'daily');
    
    $command = Schedule::command(MemoryCleanupCommand::class);
    
    // Apply the configured schedule frequency
    match ($scheduleFrequency) {
        'hourly' => $command->hourly(),
        'daily' => $command->daily(),
        'twiceDaily' => $command->twiceDaily(),
        'weekly' => $command->weekly(),
        'monthly' => $command->monthly(),
        default => $command->daily(), // fallback to daily
    };
    
    // Add production safety options
    $command->withoutOverlapping()
        ->onOneServer()
        ->runInBackground();
}