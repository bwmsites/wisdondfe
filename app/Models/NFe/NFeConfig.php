<?php

namespace App\Models\NFe;

use Illuminate\Database\Eloquent\Model;

class NFeConfig extends Model
{
    //Define a tabela associada a esse model
    protected $table = 'nfe_config';
    
    //Define se a chave eh auto-incremento
    public $incrementing = false;
    
    /* Desabilita as colunas default do Eloquent referentes a data de criacao e
    de alteracao */
    public $timestamps = false;

    //Faz o relacionamento 1:1 com a tabela pessoa (ref. contratante vinculado a configuracao)
    public function emissor() {
        return $this->belongsTo('App\Models\Pessoa\Pessoa', 'contratante', 'id');
    }
}
