<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminSeller extends Model
{
    public function updateRecord(array $data)
 {
  AdminSeller::setConnection('mysql')
  ->where('id', $data['id'])
  ->update($data);
 }
}