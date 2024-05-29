<?php
class Lanci
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
            'lancio' => 'N° Lancio',
            'data' => 'Data di Lancio',
            'id_lab' => 'Laboratorio',
            'id_modello' => 'Modello',
            'stato' => 'Stato',
            'paia' => 'Paia',


        ];

        return $ordering;
    }
}
?>