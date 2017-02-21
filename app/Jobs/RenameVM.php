<?php

namespace App\Jobs;

use App\Server;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenameVM extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $name;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server, $task = '', $name = '')
    {
        parent::__construct($server, $task);
        $this->name = $name;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->startUp();

        while ($this->inProgress()) {
            usleep(.5 * 1000000);
        }

        if ($this->server->status != 'error') {
            $this->server->name = $this->name;
            $this->server->save();
        }
    }
}
