<?php

namespace App\Models\Pessoa;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    //Define a tabela associada a esse model
    protected $table = 'pessoa';
    
    //Define se a chave eh auto-incremento
    public $incrementing = false;
    
    /* Desabilita as colunas default do Eloquent referentes a data de criacao e
    de alteracao */
    public $timestamps = false;
}
