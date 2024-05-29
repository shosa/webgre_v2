<?php
class LabUser
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
            'lab' => 'Laboratorio',
            'user' => 'Utente',
        ];

        return $ordering;
    }
}
?>