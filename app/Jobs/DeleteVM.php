<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Proxmox;

class DeleteVM extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->startUp();

        while ($this->inProgress(true)) {
            usleep(.5 * 1000000);
        }

        if ($this->server->status != 'error') {
            $this->newTask(Proxmox::create("/nodes/{$this->server->node}/qemu/{$this->server->vmid}/config", ['protection' => 0]), function ($self) {
                $self->newTask(Proxmox::delete("/nodes/{$self->server->node}/qemu/{$self->server->vmid}"), function ($self) {
                    $self->server->delete();
                });
            });
        }
    }
}
