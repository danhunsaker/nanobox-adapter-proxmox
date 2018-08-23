<?php

namespace App\Jobs;

use App\Server;
use Illuminate\Bus\Queueable;
use Log;
use Proxmox;
use SSH;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */
    use Queueable;

    protected $server;

    protected $task;

    /**
     * Create a new job instance.
     */
    public function __construct(Server $server, $task = '')
    {
        $this->server = $server;
        $this->task   = $task;
    }

    protected function startUp()
    {
        config(['proxmox.server' => $this->server->user->makeVisible('password')->toArray()]);
        Proxmox::login();

        Log::info('Starting job ' . get_class($this) . " (Task ID: {$this->task})...");
    }

    protected function newTask($result, $success, $ignoreTaskErrors = false)
    {
        if (isset($result['errors'])) {
            $this->server->status = 'error';
            $this->server->save();
        } else {
            $this->task = $result['data'];

            while ($this->inProgress($ignoreTaskErrors)) {
                usleep(.5 * 1000000);
            }

            if ($this->server->status != 'error') {
                call_user_func_array($success, [$this]);
            }
        }
    }

    protected function inProgress($ignoreTaskErrors = false)
    {
        $result = Proxmox::get("/nodes/{$this->server->node}/tasks/{$this->task}/status");

        if (isset($result['errors'])) {
            $this->server->status = 'error';
            $this->server->save();

            return false;
        }

        if ($result['data']['status'] == 'stopped') {
            if ($result['data']['exitstatus'] != 'OK' && ! $ignoreTaskErrors) {
                $this->server->status = 'error';
                $this->server->save();
            }

            return false;
        }

        return true;
    }

    protected function untilOK($method, $uri, $payload = [], $timeout = 900, $success = null)
    {
        // loop requests until the response is OK
        $abort = microtime(true) + $timeout;
        while ($result = Proxmox::{$method}($uri, $payload)) {
            if ( ! isset($result['errors']) && ! empty($result['data'])) {
                // Technically OK?
                if (is_callable($success)) {
                    call_user_func_array($success, [$this, $result]);
                }
            } elseif (microtime(true) >= $abort) {
                // Timed Out!
                $this->server->status = 'error';
                $this->server->save();
            } else {
                usleep(.25 * 1000000);
                continue;
            }

            break;
        }
    }

    protected function afterBoot()
    {
        $this->untilOK('create', "/nodes/{$this->server->node}/qemu/{$this->server->vmid}/agent", ['command' => 'network-get-interfaces'], 15 * 60, function ($self, $result) {
            $data = collect($result['data']['result'])->pluck('ip-addresses', 'name')->except('lo')->map(function ($item, $key) {
                return collect($item)->reject(function ($value, $key) {
                    return substr($value['ip-address'], 0, 6) == 'fe80::';
                })->pluck('ip-address', 'ip-address-type');
            });

            $self->server->external_ip = $data->first()->get('ipv4');
            $self->server->internal_ip = $data->last()->get('ipv4');

            try {
                config(["remote.connections.{$self->server->unique_id}" => [
                    'host'      => $self->server->external_ip,
                    'username'  => 'gonano',
                    'password'  => 'gonano',
                    'timeout'   => 10,
                ]]);

                SSH::into($self->server->unique_id)->run([
                    "passwd << EOF\ngonano\n{$self->server->password}\n{$self->server->password}\nEOF",
                ]);
            } catch (\RuntimeException $e) {
                // We have nothing to worry about from this step not working - just means the password is already changed.
                // But, if it's anything other than a connection attempt failure, we wanna pass it up the chain, as is.
                if ($e->getMessage() != 'Unable to connect to remote server.') {
                    throw $e;
                }
            }

            if ( ! empty($self->server->key)) {
                try {
                    config(["remote.connections.{$self->server->unique_id}.password" => $self->server->password]);

                    SSH::into($self->server->unique_id)->run([
                        "( cat ~/.ssh/authorized_keys ; echo {$self->server->key->key} ) | sort -hu > ~/.ssh/authorized_keys",
                        "  sudo -ksS <<<'{$self->server->password}' ( cat ~/.ssh/authorized_keys ; echo {$self->server->key->key} ) | sort -hu > ~/.ssh/authorized_keys",
                    ]);
                } catch (\RuntimeException $e) {
                    // Similar story from a connection failure here - just means the key is already installed (and the password invalid).
                    // But, if it's anything other than a connection attempt failure, we wanna pass it up the chain, as is.
                    if ($e->getMessage() != 'Unable to connect to remote server.') {
                        throw $e;
                    }
                }

                $self->server->password = '';
            }

            $self->server->status = 'active';
            $self->server->save();
        });
    }
}
