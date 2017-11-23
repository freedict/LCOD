<?php

namespace App\Traits;

// See https://stackoverflow.com/a/45914381 for explanation.
trait BindsDynamically
{
    // protected $connection = null;
    // protected $table = null;

    // public function bind(string $connection, string $table)
    // {
    //     $this->setConnection($connection);
    //     $this->setTable($table);
    // }

    public function printfoo(string $foostring) {
        print($foostring);
    }
    // public function newInstance($attributes = [], $exists = false)
    // {
    //     $model = parent::newInstance($attributes, $exists);
    //     $model->setTable($this->table);

    //     return $model;
    // }

}