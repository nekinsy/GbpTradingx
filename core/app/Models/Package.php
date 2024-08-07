<?php

namespace App\Models;
use App\Constants\Status;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Package extends Model
{
    use HasFactory;
    use GlobalStatus;
    public function typeBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if($this->type== Status::PACKAGE_TYPE_FIXED){
                $html = '<span class="badge badge--primary">' . trans('fixed') .'</span><br>';
            }
            elseif ($this->type == Status::PACKAGE_TYPE_VARIABLE) {
                $html = '<span class="badge badge--danger">' . trans('variable') .'</span><br>';
            }
            return $html;
        });
    }
}
