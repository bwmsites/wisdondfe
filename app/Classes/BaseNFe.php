<?php

namespace App\Classes;

/**
 * Description of BaseNFe
 *
 * @author Bruno Silva
 */
class BaseNFe {
    public $infNFe;
    public $ide;
    public $emit;
    public $dest;
    public $autXML = [];
    public $det = [];
    public $IcmsTot;
    public $transp;
    public $pag;
    public $infAdic;
        
    public function __construct($nfeData) {
        $this->taginfNFe($nfeData['infNFe']);
        $this->tagide($nfeData['ide']);
        $this->tagemit($nfeData['emit']); //Dados do Emitente
        $this->tagdest($nfeData['dest']); //Dados do Destinatario
        //$this->tagautXML($nfeData['']); // Pessoas autorizadas a receber a NFe
        $this->tagdet($nfeData['det']);
        $this->tagIcmsTot($nfeData['icmsTot']);
        $this->tagtransp($nfeData['transp']);
        $this->tagpag($nfeData['pag']);
        $this->taginfAdic($nfeData['infAdic']);
    }
    
    private function taginfNFe($data = []) {
        $this->infNFe = new \stdClass();
        
        foreach ($data as $tag => $valor) {
            $this->infNFe->$tag = $valor;
        }
    }
    
    private function tagide($data = []) {
        $this->ide = new \stdClass();

        foreach ($data as $tag => $valor) {
            $this->ide->$tag = $valor;
        }
    }
    
    private function tagemit($data = []) {
        $this->emit = new \stdClass();
        $this->emit->enderEmit = new \stdClass();
        
        foreach ($data as $tag => $valor) {
            if ($tag !== 'enderEmit') {
                $this->emit->$tag = $valor;
            } else {
                foreach ($data[$tag] as $sbtag => $val) {
                    $this->emit->enderEmit->$sbtag = $val;
                }
            }
        }
    }
    
    private function tagdest($data = []) {
        $this->dest = new \stdClass();
        $this->dest->enderDest = new \stdClass();
        foreach ($data as $tag => $valor) {
            if ($tag !== 'enderDest') {
                $this->dest->$tag = $valor;
            } else {
                foreach ($data[$tag] as $sbtag => $val) {
                    $this->dest->enderDest->$sbtag = $val;
                }
            }
        }
    }
    
    private function tagdet($data = []) {
        $arr = [];
        foreach ($data as $detItem) {
            $nItem = $detItem['item'];
            $det = new \stdClass();
            $det->prod = new \stdClass();
            $det->prod->item = $nItem;
            foreach ($detItem['prod'] as $tag => $valor) {
                $det->prod->$tag = $valor;
            }
            $det->infAdProd = new \stdClass();
            $det->infAdProd->item = $nItem;
            $det->infAdProd->infAdProd = $detItem['infAdProd'];
            $det->imposto = new \stdClass();
            $det->imposto->item = $nItem;
            $det->imposto->vTotTrib = $detItem['imposto']['vTotTrib'];
            $det->imposto->icms = new \stdClass();
            $det->imposto->icms->item = $nItem;
            $icms = $detItem['imposto']['icms'];
            foreach ($icms as $tag => $valor) {
                $det->imposto->icms->$tag = $valor;
            }
            $det->imposto->pis = new \stdClass();
            $det->imposto->pis->item = $nItem;
            $pis = $detItem['imposto']['pis'];
            foreach ($pis as $tag => $valor) {
                $det->imposto->pis->$tag = $valor;
            }
            $det->imposto->cofins = new \stdClass();
            $det->imposto->cofins->item = $nItem;
            $cofins = $detItem['imposto']['cofins'];
            foreach ($cofins as $tag => $valor) {
                $det->imposto->cofins->$tag = $valor;
            }
            $arr[] = $det;
        }
        $this->det = $arr;
    }
    
    private function tagIcmsTot($data = []) {
        $this->IcmsTot = new \stdClass();
        
        foreach ($data as $tag => $valor) {
            $this->IcmsTot->$tag = $valor;
        }
    }
    
    private function tagtransp($data = []) {
        $this->transp = new \stdClass();
        $this->transp->modFrete = $data['modFrete'];
        if (isset($data['transporta']) && count($data['transporta'])) {
            $transporta = $data['transporta'];
            foreach ($transporta as $tag => $valor) {
                $this->transp->transporta->$tag = $valor;
            }
        }
    }
    
    private function tagpag($data = []) {
        $this->pag = new \stdClass();
        $this->pag->vTroco = $data['vTroco'];
        $this->pag->pgtos = [];
        $detpag = $data['detPag'];
        foreach ($detpag as $det) {
            $this->pag->detPag = new \stdClass();
            foreach ($det as $tag => $valor) {
                $this->pag->detPag->$tag = $valor;
            }
            $this->pag->pgtos[] = $this->pag->detPag;
        }
        
        //print_r($this->pag->pgtos);
    }
    
    private function taginfAdic($data = []) {
        $this->infAdic = new \stdClass();
        $this->infAdic->infAdFisco = isset($data['infAdFisco']) ? $data['infAdFisco'] : null;
        $this->infAdic->infCpl = isset($data['infCpl']) ? $data['infCpl'] : null;
        $this->infAdic->obsCont = [];
        $obsCont = $data['obsCont'];        
        foreach ($obsCont as $observ) {
            $obs = new \stdClass();
            foreach ($observ as $tag => $valor) {
                $obs->$tag = $valor;
            }            
            $this->infAdic->obsCont[] = $obs;
        }
    }
}
