<?php

interface SecurityObject {
    /**
     *
     * @param string $label - метка действия
     * @return boolean
     */
    public function checkPermissions($label);
}
?>
