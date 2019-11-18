<?php

namespace App\Http\Controllers\NFe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\NFeService;
use App\Classes\BaseNFe;

class NFeController extends Controller
{
    public function checkCadastro ($cnpj, $modelo = '55') {
        $svc = new NFeService($cnpj);
        return $svc->checkCadastro($modelo);
    }
    
    public function checkSefaz ($cnpj, $modelo = '55') {
        $svc = new NFeService($cnpj);
        return $svc->checkSefazStatus($modelo);
    }
    
    public function geraXML ($cnpj, Request $request) {
        $svc = new NFeService($cnpj);
        $nfe = new BaseNFe($request->input('nfe'));
        $retGera =  $svc->geraXML($nfe);
        
        if ($retGera['retorno'] === 'ok') {
            return $svc->assinaXML($retGera['xml']);
        }
        
        return $retGera;
    }
    
    public function assinaXML($cnpj, Request $request) {
        $svc = new NFeService($cnpj);
        $xml = $request->input('xml');
        return $svc->assinaXML($xml);
        
    }
}
