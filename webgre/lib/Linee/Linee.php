<?php
class Linee
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
            'sigla' => 'Sigla',
            'descrizione' => 'Marchio',
        ];

        return $ordering;
    }
}
?>
