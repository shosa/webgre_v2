<?php
class Inventario
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
            'ID' => 'ID',
            'dep' => 'Deposito',
            'cm' => 'Categoria Merceologica',
            'art' => 'Articolo',
            'des' => 'Descrizione',
            'qta' => 'Quantità',
            'num' => 'Numerata',
            'is_num' => 'is_num',
        ];

        return $ordering;
    }
}
?>