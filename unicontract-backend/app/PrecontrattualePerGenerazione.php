<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Insegnamenti;
use App\Models\Anagrafica;
use App\Models\B1ConflittoInteressi;
use App\Models\A2ModalitaPagamento;
use App\User;
use App\Ruolo;
use App\Models\B4RapportoPA;
use App\Models\P2rapporto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Handler;
use Illuminate\Container\Container;
use Exception;


class PrecontrattualePerGenerazione extends Precontrattuale {

    //PROPRIETA CREATE PER GENERAZIONE CONTRATTO
    // TIPOLOGIA CONTRATTUALE
    public function getTipoContrAttribute()
    {
        return $this->insegnamento->tipo_contratto;
    }

    public function getMotivoAttoAttribute()
    {
        return $this->insegnamento->motivo_atto;
    }

    public function getNaturaRapportoAttribute()
    {
        return $this->p2naturarapporto->natura_rapporto;
    }

    public function getGenereAttribute(){
        return $this->anagrafica->genereSelection();
    }


    // SPECIFICHE DELL'INSEGNAMENTO
    public function getRuoloAttribute()
    {
        $pers =$this->personaleRelation() ? $this->personaleRelation()->first() : null;
        $cd_ruolo = $pers ? $pers->cd_ruolo : null;

        return $this->insegnamento->ruoloToString($cd_ruolo);
    }

    public function getDipartimentoAttribute()
    {
        return $this->insegnamento->dipartimento;
    }

    public function getInsegnamentoDescrAttribute()
    {
        return $this->insegnamento->insegnamentoDescr;
    }

    public function getSettoreAttribute()
    {
        return $this->insegnamento->settoreToString();
    }

    public function getCfuAttribute()
    {
        return $this->insegnamento->cfuToString();
    }
    public function getOreAttribute()
    {
        return $this->insegnamento->ore;
    }
    public function getCdlAttribute()
    {
        return  $this->insegnamento->cdlToString();
    }

    public function getPeriodoAttribute()
    {
        return $this->insegnamento->periodoToString();
    }

    public function getCicloAttribute()
    {
        return $this->insegnamento->cicloToString();
    }

    public function getDataDaAttribute()
    {
        return $this->insegnamento->data_ini_contr;
    }

    public function getDataAAttribute()
    {
        return $this->insegnamento->data_fine_contr;
    }

    public function getAaAttribute()
    {
        return $this->insegnamento->aa.'/'.((int)$this->insegnamento->aa+1);
    }

    public function getAaNumAttribute()
    {
        return (int)$this->insegnamento->aa;
    }


    public function getRinnovoAttribute()
    {
        return $this->ContatoreSelection();
    }


    // SPECIFICHE AMMINISTRATIVE E PREVIDENZIALI
    public function getCompensoAttribute()
    {
        return number_format($this->insegnamento->compenso, 2, ',', '.');
    }

    //public function getTipoEmittAttribute()
    //{
        //return $this->insegnamento->emittenteToString();
    //}
    //public function getTipoAttoAttribute()
    //{
        //return $this->insegnamento->tipoAttoToString();
    //}

    public function getEsercizioFinanziarioAttribute()
    {
        return date('Y') + 1;
    }

    public function getModPagamentoAttribute(){

        return $this->insegnamento->modalitadiPagamento();
    }

    public function getAttestazioneAttribute(){
        if ($this->isAltaQualificazione() || $this->isDidatticaUfficiale()){
            return ", previa attestazione, da parte del Responsabile della struttura didattica, del regolare svolgimento dell'attività";
        }else {
            return "";
        }
    }

    public function getCassaAttribute(){
        if ($this->cPrestazioneProfessionale->flag_cassa === 1) {
            return "più contributo cassa previdenziale";
        } else {
            return "";
        }
    }

    public function getRivalsaAttribute(){
        if ($this->cPrestazioneProfessionale->flag_rivalsa === 1) {
            return "e rivalsa prevista dalla legge 8 agosto 1995 n. 335";
        } else {
            return "";
        }
    }

    public function datiAnagraficaString(){
        if($this->naturaRapporto == "ALD"){
            // ASSIMILATO A LAVORO DIPENDENTE
            return $this->anagrafica->datiAnagraficaString()." attualmente ".$this->ruolo." di questo Ateneo";
        }
        return $this->anagrafica->datiAnagraficaString();
    }


    public function isAltaQualificazione(){
        return $this->tipoContr == 'ALTQG' ||
               $this->tipoContr == 'ALTQC' ||
               $this->tipoContr == 'ALTQU' ||
               $this->tipoContr == 'TC004' ||
               $this->tipoContr == 'TC005' ||
               $this->tipoContr == 'TC006';
    }

    public function isDidatticaUfficiale(){
        return $this->tipoContr == 'CONTC' ||
               $this->tipoContr == 'CONTU' ||
               $this->tipoContr == 'TC007';
    }

    public function isDidatticaIntegrativa(){
        return $this->tipoContr == 'INTC' || $this->tipoContr == 'INTU' || $this->tipoContr == 'INTXU' || $this->tipoContr == 'INTXC';
    }

    public function isSupportoDidattica(){
        return $this->tipoContr == 'SUPPU' || $this->tipoContr == 'SUPPC';
    }

    public function isNuovo(){
        return $this->motivoAtto == 'BAN_INC' || $this->motivoAtto == 'APPR_INC' || $this->motivoAtto == 'PROP_INC';
    }

    public function ContatoreSelection(){
        // CONTATORE INSEGNAMENTI

        $counter = $this->insegnamento->contatore(); //contatore_insegnamenti($cod_insegnam, $cf);

        //nel caso il contatore sia 0 ma è stato importato come RINNOVO
        //significa che il sistema su Ugov non è coerente per eventuali delibere di rinnovo
        //UniContr lo considera rinnovo
        if($counter == 1){
            return [
                'storico' => "attribuito",
                'text_rinnovo_1' => "rinnovato",
                'text_rinnovo_2' => "attribuito",
                'text_rinnovo_3' => "rinnovo",
                'text_rinnovo_4' => "conferito"
            ];

        } else if($counter > 1) {
            return [
                'storico' => "rinnovato",
                'text_rinnovo_1' => "ulteriormente rinnovato",
                'text_rinnovo_2' => "già rinnovato",
                'text_rinnovo_3' => "ulteriore rinnovo",
                'text_rinnovo_4' => "rinnovato"
            ];
        }

        if($counter == 0){

            Log::info('Generato contratto con contatore a 0  [ id =' . $this->id . '] [ coper_id =' . $this->insegnamento->coper_id . ']');

            return [
                'storico' => "attribuito",
                'text_rinnovo_1' => "rinnovato",
                'text_rinnovo_2' => "attribuito",
                'text_rinnovo_3' => "rinnovo",
                'text_rinnovo_4' => "conferito"
            ];

        }

    }

    public function currentStateReport(){
        if ($this->validazioni){
            if ($this->stato == 2 || $this->stato == 3){
                return "ANNULLATA";
            }
            if (!$this->validazioni->flag_submit){
                return "MODULISTICA \n DA COMPILARE";
            }
            if ($this->validazioni->flag_submit && (!$this->validazioni->flag_upd || !$this->validazioni->flag_amm)){
                //this.items.flag_upd isValidatoAmm uff. amministrativo
                //this.items.flag_amm  isValidatoEconomica uff. finanze
                if (!$this->validazioni->flag_upd){
                    return "DA VALIDARE \n UFF. PERS. DOC.";
                }else if (!$this->validazioni->flag_amm){
                    return "DA VALIDARE \n UFF. TRATT. ECON.";
                }else{
                    return "DA VALIDARE";
                }
            }
            if ($this->validazioni->flag_submit && $this->validazioni->flag_upd && $this->validazioni->flag_amm && !$this->validazioni->flag_accept){
                return "CONTRATTO \n DA ACCETTARE";
            }
            if ($this->validazioni->flag_submit && $this->validazioni->flag_upd && $this->validazioni->flag_amm && $this->validazioni->flag_accept && $this->stato == 0 ){
                return "ALLA FIRMA";
            }
            return '';
        }else{
            return '';
        }
    }


    public function getPrecontrattualeType(){
        $entity = new Precontrattuale();
        $entity->fill($this->toArray());
        $entity->id = $this->id;
        return $entity;
    }



    ///precondizione che sia stata caricata la relazione
    public function existEmail(){
        if ($this->sendemails && $this->sendemails->count()>0){
            return true;
        }
        return false;
    }

    ///precondizione che sia stata caricata la relazione
    public function giorniUltimaEmail(){
        if ($this->existEmail()){
            $email = $this->sendemails->sortByDesc('created_at')->first();
            $datetime1 =  $email->created_at;
            $datetime2 = Carbon::now();
            $diff_in_days  = $datetime1->diffInDays($datetime2);
            return $diff_in_days;
        }else{
            return -1;
        }
    }

    // Unical functions
    public function isComma1Gratuito(){
        return $this->tipoContr == 'TC004';
    }
    public function isComma1Retribuito(){
        return $this->tipoContr == 'TC006';
    }
    public function isComma2Retribuito(){
        return $this->tipoContr == 'TC007';
    }
    public function getDataDeliberaAttribute()
    {
        return $this->insegnamento->data_delibera;
    }
    public function getEmittenteAttribute()
    {
        return $this->insegnamento->emittente;
    }
    public function getOreDescAttribute()
    {
        return $this->insegnamento->ore_desc;
    }
    public function getTipoCorsoDesAttribute()
    {
        return $this->insegnamento->tipo_corso_des;
    }
    public function getAnnoCorsoAttribute()
    {
        return $this->insegnamento->anno_corso;
    }
    public function getDipDocCodAttribute()
    {
        return $this->insegnamento->dip_doc_cod;
    }
    public function getDipDocDesAttribute()
    {
        return $this->insegnamento->dip_doc_des;
    }

}


