<?php
class Laboratori
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
            'Nome' => 'Nome',
        ];

        return $ordering;
    }
}
?>