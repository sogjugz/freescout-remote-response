<?php

namespace Modules\SsRemoteResponse\Entities;

use Illuminate\Database\Eloquent\Model;

class RemoteResponseSettings extends Model
{
    protected $table = 'ss_remote_responses';

    protected $primaryKey = 'mailbox_id';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = ['mailbox_id', 'enabled', 'url', 'timeout', 'method', 'headers'];

    protected $casts = [
        'enabled' => 'boolean',
    ];    
}
