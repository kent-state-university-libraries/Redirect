<?php

class Table_Redirect extends Omeka_Db_Table
{
    public function findByElementId($id)
    {
        $select = $this->getSelect()->where('id = ?', $id);
        return $this->fetchObject($select);
    }
}
