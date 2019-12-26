<?php

class FilesUtils
{
    const DIRECTORY_TREE_DEEP = 5; // уровень вложенности для раскидывания файлов по директориям

    public static function checkDir($dir)
    {
        $dir = dirname($dir);

        if (file_exists($dir) && is_dir($dir))
            return true;

        return mkdir($dir, 0770, true);
    }

    /**
     * @param int $id
     * @param int $deep
     * @return string
     */
    public static function getNestingPathById($id, $deep = null)
    {
        $deep = $deep ? $deep : self::DIRECTORY_TREE_DEEP;

        if (!$id)
            return null;

        $length = mb_strlen($id);
        $path = array();

        for ($i = 0;  $i < $deep; $i++)
            $path[] = ($length > $i ? mb_substr($id, $length - 1 - $i, 1) : '0');

        krsort($path);

        return join(DIRECTORY_SEPARATOR, $path) . '/';
    }	
}

?>