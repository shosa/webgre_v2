<?php
class Riparazioni
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * Set friendly columns\' names to order tables\' entries
     */
    public function setOrderingValues()
    {
        $ordering = [
            'IDRIP' => 'ID',
            'ARTICOLO' => 'Articolo',
            'CODICE' => 'Codice',
            'P01' => 'P01',
            'P02' => 'P02',
            'P03' => 'P03',
            'P04' => 'P04',
            'P05' => 'P05',
            'P06' => 'P06',
            'P07' => 'P07',
            'P08' => 'P08',
            'P09' => 'P09',
            'P10' => 'P10',
            'P11' => 'P11',
            'P12' => 'P12',
            'P13' => 'P13',
            'P14' => 'P14',
            'P15' => 'P15',
            'P16' => 'P16',
            'P17' => 'P17',
            'P18' => 'P18',
            'P19' => 'P19',
            'P20' => 'P20',
            'QTA' => 'Qta',
            'CARTELLINO' => 'Cartellino',
            'REPARTO' => 'Reparto',
            'CAUSALE' => 'Note',
            'LABORATORIO' => 'Laboratorio',
            'DATA' => 'Data',
            'NU' => 'Numerata',
            'UTENTE' => 'Utente',
            'CLIENTE' => 'Cliente',
            'URGENZA' => 'Urgenza',
            'COMMESSA' => 'Commessa',
            'LINEA' => 'Linea',
        ];

        return $ordering;
    }
}
?>