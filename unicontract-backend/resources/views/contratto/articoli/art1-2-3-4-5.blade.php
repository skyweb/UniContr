<h4>Art. 1</h4>
<p class="normal">
{{$pre->genere['str0']}} {{$pre->genere['str5']}}
s’impegna a prestare in favore dell’Università la propria opera intellettuale quale professore a
contratto a tempo determinato dell’insegnamento di {{$pre->insegnamentoDescr}}
{{$pre->settore}},
{{$pre->cfu}}
{{$pre->ore}} ore complessive
{{$pre->oreDesc}} -
{{$pre->ciclo}} -
del Corso di {{$pre->tipoCorsoDes}} in {{$pre->cdl}} - {{$pre->annoCorso}} anno,
con inizio contratto {{$pre->dataDa}} e termine contratto {{$pre->dataA}}.======================
</p>

<h4>Art. 2</h4>
<p class="normal">
La prestazione d’opera intellettuale, stabilita

@if(count(explode('#', $pre->dataDelibera)) == 1)
nella seduta
@else
nelle sedute
@endif

@foreach(explode('#', $pre->dataDelibera) as $data)
    @if(!$loop->first && !$loop->last)
        e
    @endif
    del {{$data}} dal {{ $pre->emittente[$loop->index] }}
@endforeach

del {{$pre->dipDocDes}},
in seguito indicato Dipartimento,
consiste in un impegno didattico complessivo di {{$pre->ore}} ore,
oltre ad esami e ricevimento studenti.
==========================================================
</p>

<h4>Art. 3</h4>
<p class="normal">
{{$pre->genere['str0']}} {{$pre->genere['str5']}} s’impegna a svolgere le attività connesse al suddetto corso d’insegnamento nell’ambito e secondo la programmazione didattica predisposta dal Dipartimento. L{{$pre->genere['str2']}} stess{{$pre->genere['str2']}} s’impegna, inoltre, a compilare il registro elettronico sul sistema informatico ESSE3, ove verranno annotate le lezioni impartite con l’indicazione del tema trattato.========================================
</p>

<h4>Art. 4</h4>
<p class="normal">
{{$pre->genere['str0']}} {{$pre->genere['str5']}} ha diritto di partecipare ai Consigli delle strutture didattiche, ove dalle stesse previsto e nei limiti fissati dai rispettivi regolamenti.========================================
</p>

<h4>Art. 5</h4>
<p class="normal">
L’Università, limitatamente al periodo di cui al precedente art. 1, provvede, in favore {{$pre->genere['str6']}} {{$pre->genere['str5']}} e con spese a proprio carico, alla copertura assicurativa privata contro i danni derivanti da responsabilità civile.========================================
</p>
