<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebootVM extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

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
            $this->newTask(Proxmox::create("/nodes/{$this->server->node}/qemu/{$this->server->vmid}/status/start"), function ($self) {
                $self->afterBoot();
            }, true);
        } else {
            $this->server->status = 'rebooting';
            $this->server->save();
            $this->newTask(Proxmox::create("/nodes/{$this->server->node}/qemu/{$this->server->vmid}/status/reset"), function ($self) {
                $self->afterBoot();
            }, true);
        }
    }
}
