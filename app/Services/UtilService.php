<?php

namespace App\Services;


use App\Models\Pessoa\Pessoa as Contratante;
use App\Models\NFe\NFeConfig;

/**
 * Description of UtilService
 *
 * @author Bruno
 */
class UtilService {
    public static function getContratante($cnpj) {
        $contratante = Contratante::where('cpf_cnpj', $cnpj)->get();
        
        if (count($contratante) > 0) {
            return [
                "retorno" => "ok",
                "msg" => "Registro localizado",
                "body" => $contratante[0]
            ];
        }
        
        return [
                "retorno" => "erro",
                "msg" => "Registro não localizado",
                "body" => null
            ];
    }
    
    public static function getNFeConfig($contratante) {
        $config = NFeConfig::where('contratante', $contratante)->get();
        
        if (count($config) > 0) {
            return [
                "retorno" => "ok",
                "msg" => "Registro localizado",
                "body" => $config[0]
            ];
        }
        
        return [
                "retorno" => "erro",
                "msg" => "Registro não localizado",
                "body" => null
            ];
    }
    
    public static function getDirCert($contratante) {
        $cnpj = Contratante::find($contratante)->cpf_cnpj;
        
        return '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR .
               'nfe' . DIRECTORY_SEPARATOR . 'certificado' . DIRECTORY_SEPARATOR .
               $cnpj . DIRECTORY_SEPARATOR . '/' ;
    }
    
    public static function getNovoID($contratante) {
        
    }
}
