<?php

namespace App\Jobs;

use App\Server;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackVMCreate extends Job implements ShouldQueue
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
            $this->server->status = 'creating';
            $this->server->save();

            $this->newTask(Proxmox::create("/nodes/{$this->server->node}/qemu/{$this->server->vmid}/config", [
                'onboot'      => true,
                'cores'       => $this->server->serverSize->cpu,
                'memory'      => $this->server->serverSize->ram,
                'name'        => $this->server->name,
                'description' => "Created by Nanobox Adapter Proxmox\n\nSize: {$this->server->serverSize->code}\nOwner: {$this->server->user->username}@{$this->server->user->realm}",
                'reboot'      => true,
                'protection'  => true,
            ]), function ($self) {
                $self->newTask(Proxmox::create("/nodes/{$self->server->node}/qemu/{$self->server->vmid}/status/start"), function ($self) {
                    $self->afterBoot();
                }, true);
            });
        }
    }
}
