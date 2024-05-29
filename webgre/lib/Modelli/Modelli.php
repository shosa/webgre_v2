<?php
class Modelli
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
            'id' => 'ID',
            'linea' => 'Linea',
            'path_to_image' => 'Percorso',
            'codice' => 'Codice',
            'descrizione' => 'Descrizione',
            'qta_varianti' => 'Varianti attive'
        ];

        return $ordering;
    }
}
?>