<?php

/**
 * Класс EuroCodeDecoder предназначен для расшифровки еврокода
 *
 * @author trsv <trsvdeveloper@gmail.com>
 * @copyright trsv
 * @version 1.0
 */
class EuroCodeDecoder {
    /** Список марок и моделей автомобилей */
    private $listId;
    /** Список видов стекол */
    private $listType;
    /** Список цветов */
    private $listColor;
    /** Список данных для каждого вида. Цвета полосок, типы кузовов */
    private $listArgs;
    /** Список характеристик и модификаций */
    private $listOther;
    
    /** Результат расшифровки - CSV строка */
    private $result;
    
    /**
     * Конструктор, инициализирует все списки из JSON файлов в папке "eurocode"
     *
     */
    public function EuroCodeDecoder() {
        $this->listId = json_decode(file_get_contents("eurocode/id.json"), true);
        $this->listColor = json_decode(file_get_contents("eurocode/color.json"), true);
        $this->listType = json_decode(file_get_contents("eurocode/type.json"), true);
        $this->listArgs = json_decode(file_get_contents("eurocode/args.json"), true);
        $this->listOther = json_decode(file_get_contents("eurocode/other.json"), true);
    }
    
    /** 
     * Расшифровка марки и модели автомобиля
     *
     * @param $id string Ключ марки и модели в файле "id.json"
     * @return Возвращает строку - марку и модель
     */
    private function decodeName($id) {
        return $this->listId[$id]["name"];
    }
    
    /** 
     * Расшифровка года выпуска автомобиля
     * 
     * @param $id string Ключ марки и модели в файле "id.json"
     * @return Возвращает строку - год выпуска
     */
    private function decodeYear($id) {
        return $this->listId[$id]["year"];
    }
    
    /**
     * Расшифровка вида стекла - лобовое, заднее, боковое
     *
     * @param $type string Ключ вида стекла в файле "type.json"
     * @return Возвращает строку - вид стекла
     */
    private function decodeType($type) {
        return $this->listType[$type];
    }
    
    /** 
     * Расшифровка цвета стекла
     *
     * @param $color string Ключ цвета стекла в файле "color.json"
     * @return Возвращает строку - цвет стекла
     */
    private function decodeColor($color) {
        return $this->listColor[$color];
    }
    
    /**
     * Расшифровка других данных о стекле. Цвет полосы для лобового, тип дверей для заднего и бокового
     *
     * @param $keyType string Ключ вида стекла в файле "args.json"
     * @param $code string Еврокод стекла
     * @return Возвращает данные о стекле, если ключ в файле существует
     */
    private function decodeArgs($keyType, $code) {
        switch ($keyType) {
            case "A": return $this->listArgs["windscreen"][mb_substr($code, 5, 4)];
            case "B": return $this->listArgs["backlight"][mb_substr($code, 7, 1)];
            
            case "F":
            case "L":
            case "R": return $this->listArgs["bodyglass"][mb_substr($code, 7, 2)];
            default: return "no data";
        }
    }
    
    /** Расшифровка характеристик и модификаций 
     *
     * @param keyType string Ключ вида стекла в файле "other.json"
     * @param $code string Еврокод стекла
     */
    private function decodeOther($keyType, $code) {
        // Вид стекла
        $type;
        
        // Удаляем из строки еврокода расшифрованные данные,
        // оставляя лишь характеристики и модификации
        switch ($keyType) {
            case "A":
                $code = mb_substr($code, 9);
                $type = "windscreen";
                break;
            case "B":
                $code = mb_substr($code, 8);
                $type = "backlight";
            case "F":
            case "L":
            case "R":
                $code = mb_substr($code, 11);
                $type = "bodyglass";
                break;
            default: return "no data";
        }
        
        // Если в еврокоде ничего не осталось, то ничего не делаем
        if ($code == -1) return;
        
        // Если еврокод состоит только из букв, то это характеристики, иначе - модификации
        // Каждый символ характерстики расшифровуем из файла "other.json" и дописывам в строку
        // Модификацию просто ищем по ключу в файле
        if (preg_match("/^[a-zA-Z]+$/", $code)) {
            for ($i = 0; $i < strlen($code); $i++) $this->result .= ',' . $this->listOther[$type]["character"][$code[$i]];
        } else {
            if (array_key_exists($code, $this->listOther[$type]["modification"])) {
                $this->result .= ',' . $this->listOther[$type]["modification"][$code];
            } else {
                 $this->result .= ",no data";
            } 
        }
    }
    
    /**
     * Расшифровывает весь еврокод и записывает результат в строку @link $result
     *
     * @param $eurocode string Евроокод стекла
     * @return Возвращает расшифрованный еврокод в виде CSV строки
     */
    public function decode($eurocode) {
        // Длина кода
        $size = strlen($eurocode);
        
        // Если длина меньше 4х символов, то мы не сможем ничего расшифровать
        if ($size < 4) {
            echo "Invalid eurocode";
            return;
        }
        
        // Расшифровыаем марку, модель, год
        $name = $this->decodeName(mb_substr($eurocode, 0, 4));
        $year = $this->decodeYear(mb_substr($eurocode, 0, 4));
        $this->result = $name . ',' . $year;
        
        // Если еврокодкод не закончился, то получаем вид стекла
        if ($size > 4) {
            $keyType = mb_substr($eurocode, 4, 1);
            $type = $this->decodeType($keyType);
            $this->result .= ',' . $type;
        }
        
        // Так же получаем цвет стекла 
        if ($size > 6) {
            $color = $this->decodeColor(mb_substr($eurocode, 5, 2));
            $this->result .= ',' . $color; 
        }
        
        // Получаем другие данные
        if ($size > 7) {
            $arg = $this->decodeArgs($keyType, $eurocode);
            $this->result .= ',' . $arg;
        }
        
        // Если это боковое стекло, то получаем еще дополнительные данные
        if (in_array($keyType, array('L', 'R', 'F')) && $size > 10) $this->result .= ',' . $this->listArgs["bodyglass"][mb_substr($eurocode, 9, 2)]; 
        
        // Получаем данные о характеристиках и модификациях
        $this->decodeOther($keyType, $eurocode);
        
        return $this->result;
    }
    
    /** 
     * Расшифровывает и записывает результат в CSV файл "decoded.csv"
     *
     * @param $eurocode string Еврокод стекла
     */
    public function writeToFile($eurocode) {
        $decoded = $this->decode($eurocode);
        $file = fopen("eurocode/decoded.csv", 'w');
        fwrite($file, $decoded);
        fclose($file);
    }
}