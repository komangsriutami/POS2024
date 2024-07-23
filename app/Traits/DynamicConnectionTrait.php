<?php

namespace App\Traits;

trait DynamicConnectionTrait
{
    public static function bootDynamicConnectionTrait()
    {
        static::retrieved(function ($model) {
            $model->setDynamicConnection();
        });

        static::creating(function ($model) {
            $model->setDynamicConnection();
        });

        static::updating(function ($model) {
            $model->setDynamicConnection();
        });
    }

    public function setDynamicConnection()
    {
        if(session('id_tahun_active') != "" OR !is_null(session('id_tahun_active'))) {
            $tahun = session('id_tahun_active');

            if (date('Y') != $tahun) {
                $this->setConnection('db_' . $tahun);
            } else {
                $this->setConnection('mysql');
            }
        } else {
            $this->setConnection('mysql');
        }
    }

    public function getConnectionName() {
        $conn = 'mysql';
        if(session('id_tahun_active') != "" OR !is_null(session('id_tahun_active'))) {
            $tahun = session('id_tahun_active');
            if (date('Y') != $tahun) {
                $conn = 'db_'. $tahun;
            } 
        }
        return $conn;
    }

    public function getAccess() {
        $access = 1;
        if(session('id_tahun_active') != "" OR !is_null(session('id_tahun_active'))) {
            $tahun = session('id_tahun_active');
            if (date('Y') != $tahun) {
                $access = 0;
            } 
        }
        return $access;
    }
}
