<?php
/* 
 * Интерфейс для комманд, перед выполнением которых необходимо проверить права
 */
interface SecurityCommand {

    /**
     * Проверка прав перед выполнением комманды
     * @param Form $form
     * @return Boolean
     */
    public function checkPermissions(Form $form);
}
?>
