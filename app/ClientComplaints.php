<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClientComplaints extends Model
{
    
    protected $table = "client_complaints";
    protected $fillable = [
        'client_id','client_name','company_id','priority','remark','photo','dateTime','status','actionDateTime','actionRemark','actionById','actionByName'
    ];

    public $timestamps = false;


     
}