
<?php

class ViewTextUtils extends StaticFactory
{
    const STRIP_TEXT_PATTERN = '\t|\r|\n|\s|!|\.|,|?|!|:';
    const FULL_TEXT_MIN_STRLEN = 3;
    const HIGHLIGHT_START = '<span class="hilight-el" style="background-color:#bbEEFF;">';
    const HIGHLIGHT_END = '</span>';

    protected static $highlightStart = null;
    protected static $highlightEnd = null;

    public static function setHighlightTags($highlightStart, $highlightEnd)
    {
            self::$highlightStart = $highlightStart;
            self::$highlightEnd = $highlightEnd;
    }

    public static function getHighlightStart()
    {
            return self::$highlightStart ? self::$highlightStart : self::HIGHLIGHT_START;
    }

    public static function getHighlightEnd()
    {
            return self::$highlightEnd ? self::$highlightEnd : self::HIGHLIGHT_END;
    }

    public static function ucFirst($text)
    {
        return mb_strlen($text) > 0 ? mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1) : $text;
    }

    /*
     * Разрешенные атрибуты HTML тэгов в текстах от пользователей
     */
    protected static $safeAttributes = array('style', 'border', 'cellspacing', 'cellpadding', 'align', 'id', 'valign', 'colspan', 'rowspan', 'id', 'class', 'dir', 'lang', 'scope', 'alt', 'src', 'width', 'height');

    /**
     * Вернет случайный пароль
     *
     * @param integer длина пароля
     * @return string
     */
    public static function createRandomPassword($len = 6)
    {
        static $symbolSet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        static $length = null;

        if (is_null($length)) {
            $length = strlen($symbolSet);
        }
        
        $pass = "";

        for($i = 1; $i <= $len; $i++)
            $pass .= substr($symbolSet, mt_rand(0, $length - 1), 1);

        return $pass;
    }

    /**
     * Из полученного текста с HTML форматирование удаляются комментарии и неразрешенные аттрибуты.
     * Очищать текст от запрещенных тегов надо до передачи сюда.
     * @param string $text
     * @return string
     */
    public static function safeHTML($text)
    {
        $text = preg_replace("/(&lt;|<)\!--.*?--(>|&gt;)/isu", "", $text);
        if (preg_match_all("/<(.*?)>/isu", $text, $similarTagElements) !== false) {
            $position = 0;
            // Далее у каждой внутренности берем тэг, закрывающий он или пустой и строку параметров
            foreach($similarTagElements[1] as $similarTagIndex => $tagEntry) {
                $position = mb_strpos($text, $similarTagElements[0][$similarTagIndex], $position);
                if (preg_match("/^\s*(\/?)\s*([a-z0-9]+)( .*?)?\s*(\/?)\s*$/isu", $tagEntry, $tagEntryMatches)) {
                    // Если есть аттрибуты - отсеиваем неправильные и запрещенные
                    if (trim($tagEntryMatches[3])) {
                        $attrs = array();
                        // Получаем все валидные аттрибуты тэга
                        if (preg_match_all("/([a-z]+)=((['\"])[^\\3]*\\3)/isuU", $tagEntryMatches[3], $tagAttributesMatches) !== false) {
                            // Если аттрибут разрешен - добавляем его к списку
                            foreach($tagAttributesMatches[1] as $tagAttributeKey => $tagAttribute) {
                                if (in_array($tagAttribute, self::$safeAttributes)) {
                                    $attrs[] = $tagAttributesMatches[0][$tagAttributeKey];
                                }
                            }
                        }
                        $safeTag = "<{$tagEntryMatches[1]}{$tagEntryMatches[2]} ".implode(" ", $attrs)." {$tagEntryMatches[4]}>";
                        $text = mb_substr($text, 0, $position) . $safeTag . mb_substr($text, $position + mb_strlen($similarTagElements[0][$similarTagIndex]));
                        $position += mb_strlen($safeTag);
                    } else {
                        $position += mb_strlen($similarTagElements[0][$similarTagIndex]);
                    }
                } else {
                    $text = mb_substr($text, 0, $position) . mb_substr($text, $position + mb_strlen($similarTagElements[0][$similarTagIndex]));
                }
            }
        }

        return $text;
    }

    public static function checkUppercase($text, $maxPercents = 78)
    {
        $woUpper = preg_replace("/[А-ЯЁ]/su", "", $text);

        return mb_strlen($text) == 0 || mb_strlen($woUpper) / mb_strlen($text) > $maxPercents / 100;
    }

    /**
     * Форматирование даты dd month YYYY
     *
     * @param integer|string $timestamp unix timestamp
     */
    public static function humanizeDate(Timestamp $timestamp, $todayWordNeed = true, $needTime = true)
    {
        $timestampCheck = $timestamp->spawn(sprintf("%+d hour", SecurityManager::isAuth() ? SecurityManager::getUser()->getTimezone() : SERVER_TIMEZONE));
        $dayStart = Timestamp::makeToday();
        $tomorrowDayStart = $dayStart->spawn('+1 day');
        $yesterdayStart = $dayStart->spawn('-1 day');
        if (
                (Timestamp::compare($timestampCheck, $dayStart) == 1)
                && (Timestamp::compare($timestampCheck, $tomorrowDayStart) == -1)
        ) {
                $date =	$todayWordNeed === true	? 'сегодня ' : '';
                if ($needTime)
                    $date .= date("H:i", $timestampCheck->toStamp());

        } elseif (Timestamp::compare($timestampCheck, $yesterdayStart) == 1
                && Timestamp::compare($timestampCheck, $dayStart) == -1
        ) {
                $date = 'вчера ';
                if ($needTime)
                    $date .= date("H:i", $timestampCheck->toStamp());

        } else {
                $date = preg_replace('|^0|', '', $timestampCheck->getDay()) . ' ' . RussianTextUtils::getMonthInGenitiveCase($timestampCheck->getMonth()) . ' ' . $timestampCheck->getYear();
        }

        return $date;
    }

    /**
     * Функция возвращает первые $length символов до символов $pattern
     *
     * @param string $text
     * @param integer $length
     * @param string $pattern
     * @param string добавляемые в возвращаюемую строку символы
     * @param bool удалять ли символы $pattern из конца строки
     * @return string
     */
    public static function getStrippedText($text, $length, $pattern = null, $postfix = '', $removePatternFromEndOfString = true)
    {
        if (mb_strlen(trim($text)) <= $length)
                return trim($text);

        $pattern = $pattern ? $pattern : self::STRIP_TEXT_PATTERN;
        $substr = mb_substr(trim($text), 0, $length+1);

        // если $length+1 символ из $pattern - отсекаем до него
        if (preg_match('/['.$pattern.']$/isU', $substr)) {
                $res = mb_substr($substr, 0, $length);
        } elseif (!($res = preg_replace('/([^>'.$pattern.']+)$/isU', '', $substr, -1))) { // удаляем до первого с конца символа из $pattern
                $res = mb_substr(trim($text), 0, $length);
        }

        // убить br и не закрытые теги
        return preg_replace('/('.($removePatternFromEndOfString ? '['.$pattern.']|' : '').'(<br[^>]*>)|(<[^>]*))+$/isU', '', $res) . $postfix;
    }

    /**
     * @fixme избавиться от 2ух str_replace($text)
     * имеется баг: при $words больше 99 - символ после найденого слова заменяется пробелом
    **/
    public static function highlightTextByWords($text, array $words, $clearWords = true)
    {
            $words = array_unique($words);
            if ($clearWords)
                    $words = self::clearKeywords($words, true);

            $join = array();
            foreach($words as $word) {
                if (mb_strlen($word) >= self::FULL_TEXT_MIN_STRLEN) {
                    $join[] = "([a-zа-яёЁ0-9]{0,4}".$word."[a-zа-яёЁ0-9]{0,4})";
                } else {
                    $join[] = "(".$word.")";
                }
            }

            if (count($words) && $text) {
                    $regExp = "/([\s\.,:\-\!\?\(\)«»\"'\n>]|^)(".join("|", $join).")([\s\.,:\-\!\?\(\)«»\"'\n<]|$)/isu";
                    //$regExp = "/(\W|^)\n((".join(")\n|(", $words)."))\n(\W|$)/isux";
                    //echo $regExp;

                    $text = str_replace('  ', ' ', trim(
                            Filter::pcre()->setExpression(
                                    $regExp,
                                    '$1'.self::getHighlightStart().'$2'.self::getHighlightEnd().'$'.(3+count($words) > 99 ? 1 : 3+count($words))
                            )->
                            apply(' '.str_replace(' ', '  ', $text).' ')
                    ));
            }

            return $text;
    }

    public static function clearKeywords(array $words, $enableRegexp = false)
    {
            $result = array();

            foreach ($words as $word) {
                    if (mb_strlen($word) < self::FULL_TEXT_MIN_STRLEN)
                            continue;
                    $word = preg_quote($word, '/');

                    if ($enableRegexp)
                            $word = str_replace('\*', '[a-zа-яА-ЯёЁ-]*', $word);

                    $result[] = $word;
            }

            return $result;
    }
}