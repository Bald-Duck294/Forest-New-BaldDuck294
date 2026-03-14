<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Conversations extends Model
{
    protected $table = "conversations";
    protected $fillable = ['id', 'name', 'creator_id',  'receiver_id', 'receiver_name','last_message','last_message_date','last_message_by','last_message_creator_name','is_read','type'];
    public $timestamps = false;
}
