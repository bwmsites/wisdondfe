<?php

namespace App\Services;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
//use NFePHP\DA\NFe\Danfe;
//use NFePHP\DA\Legacy\FilesFolders;
use NFePHP\NFe\Make;
//use NFePHP\NFe\Complements;
use App\Classes\BaseNFe;

use App\Services\UtilService;

/**
 * Description of NFeService
 *
 * @author Bruno Silva
 */
class NFeService {
    private $tools;
    private $config;
    private $ok;
    
    public function __construct($cnpj_emissor = '') {
        $contdata = UtilService::getContratante($cnpj_emissor);
        
        if ($contdata['retorno'] === 'ok') {
            $cont = $contdata['body']['id'];
            $confdata = UtilService::getNFeConfig($cont);
            if ($confdata['retorno'] === 'ok') {
                $this->config = $confdata['body'];
                $this->ok = true;
                $configJson = $this->config->api_config;
                $certfile = UtilService::getDirCert($cont) . $this->config->certificado_nome;
                
                $certpass = $this->config->certificado_senha;
                $content = file_get_contents($certfile);
                
                $this->tools = new Tools($configJson, Certificate::readPfx($content, $certpass));
            } else {
                $this->ok = false;
            }
        } else {
            $this->ok = false;
        }
    }
    
    public function checkCadastro($modelo = '55') {
        if ($this->ok) {
            $this->tools->model($modelo);
            $std = new Standardize();        

            $uf = $this->config->emissor->uf;
            $cnpj = $this->config->emissor->cpf_cnpj;
            $iest = '';
            $cpf = '';

            try {
                 $retorno = $std->toArray($this->tools->sefazCadastro($uf, $cnpj, $iest, $cpf));
                 return ["retorno" => "ok", "status" => $retorno['infCons']['cStat'], "msg" => $retorno['infCons']['xMotivo']];
            } catch (\Exception $e) {
                return ["retorno" => "erro", "status" => -1, "Msg" => $e->getMessage()];
            }
        } else {
            return ["retorno" => "erro", "status" => -1, "Msg" => "Recurso indisponível no momento"];
        }
    }
    
    public function checkSefazStatus($modelo = '55') {
        if ($this->ok) {
            $this->tools->model($modelo);

            //$contingencia = $this->tools->contingency->activate('SP', $motive);
            $std = new Standardize();
            $retorno = $std->toArray($this->tools->sefazStatus());

            return [
                "retorno" => "ok",
                "status" => $retorno['cStat'],
                "Msg" => $retorno['xMotivo']
            ];
        } else {
            return ["retorno" => "erro", "status" => -1, "Msg" => "Recurso indisponível no momento"];
        }
    }
    
    public function geraXML(BaseNFe $nfeData) {
        try {
            $nfe = new Make();        
            $nfe->taginfNFe($nfeData->infNFe);
            $nfe->tagide($nfeData->ide);
            $nfe->tagemit($nfeData->emit);
            $nfe->tagenderEmit($nfeData->emit->enderEmit);
            $nfe->tagdest($nfeData->dest);
            $nfe->tagenderDest($nfeData->dest->enderDest);
            foreach ($nfeData->det as $det) {
                $nfe->tagprod($det->prod);
                $nfe->taginfAdProd($det->infAdProd);
                $nfe->tagimposto($det->imposto);
                $nfe->tagICMS($det->imposto->icms);
                $nfe->tagPIS($det->imposto->pis);
                $nfe->tagCOFINS($det->imposto->cofins);
            }
            $nfe->tagICMSTot($nfeData->IcmsTot);
            $nfe->tagtransp($nfeData->transp);
            $nfe->tagpag($nfeData->pag);
            $pgtos = $nfeData->pag->pgtos;
            foreach ($pgtos as $pg) {
               $nfe->tagdetPag($pg);
            }
            $nfe->taginfAdic($nfeData->infAdic);
            $obsCont = (isset($nfeData->infAdic->obsCont) && count($nfeData->infAdic->obsCont) > 0) ? $nfeData->infAdic->obsCont : [];
            foreach ($obsCont as $obs) {
                $nfe->tagobsCont($obs);
            }
            $nfe->monta();        
            return [
                "retorno" => "ok",
                "msg" => "XML gerado com sucesso",
                "xml" => $nfe->getXML()
            ];
        } catch (Exception $e) {
            return [
                "retorno" => "erro",
                "msg" => "Erro ao gerar arquivo XML. Detalhe: " . $e->getMessage()
            ];
        }
    }
    
    public function assinaXML($xml) {
        try {
            $resp = $this->tools->signNFe($xml);
            return [
                "retorno" => "ok",
                "msg" => "XML assinado com sucesso",
                "xml" => $resp
            ];
        } catch (\Exception $e) {
            return [
                "retorno" => "erro",
                "msg" => "Erro ao tentar assinar XML. Detalhe: " . $e->getMessage(),
                "xml" => null
            ];
        }
    }
    
    public function enviaNFe($lote, $aXML) {
        try {
            $resp = $this->tools->sefazEnviaLote($aXML, $lote); //Envia o xml para pedir autorização a SEFAZ
            $st = new Standardize();
            $std = $st->toStd($resp); //Transforma o xml de retorno em uma stdClass
            if ($std->cStat != 103) { //Erro/Problema no recebimento do lote
                return [
                    "retorno" => "erro",
                    "status" => $std->cStat,
                    "msg" => $std->xMotivo
                ];
            }
            $dhrec = new \DateTime($std->dhRecbto);
            return [
                "retorno" => "ok",
                "status" => $std->cStat,
                "msg" => $std->xMotivo,
                "nrec" => $std->infRec->nRec, //Esse recibo deve ser guardado para a proxima operação que é a consulta do recibo
                "dhrec" => $dhrec->format('Y-m-d H:i:s'),
                "versao" => $std->verAplic
            ];
        } catch (\Exception $e) {
            return [
                "retorno" => "erro",
                "status" => 0,
                "msg" => $e->getMessage()
                
            ];
        }
    }
    
    public function consultaLote($nrec) {
        $xmlResp = $this->tools->sefazConsultaRecibo($nrec); //consulta pelo número do recibo
        $st = new Standardize();
        $std = $st->toStd($xmlResp); //transforma o xml de retorno em um stdClass

        if($std->cStat=='103') { //lote enviado
            $return = [
                "retorno" => "pendente",
                "status" => "103",
                "msg" => "Lote ainda NÃO processado",
            ];
        }
        if($std->cStat=='105') { //lote em processamento
            $return = [
                "retorno" => "pendente",
                "status" => "105",
                "msg" => "Lote em Processamento",
            ];
        }

        if($std->cStat=='104'){ //lote processado (tudo ok)
            $return = [
                "retorno" => "ok",
                "status" => "104",
                "msg" => "Lote processado",
                "xml" => $xmlResp
            ];            
        } else { //outros erros possíveis
            $return = [
                "retorno" => "rejeitado",
                "status" => $std->cStat,
                "msg" => $std->xMotivo
            ];
        }

            return $return;
    }
}