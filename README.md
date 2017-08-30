# Eurocode Decoder
Дешифратор еврокода автомобильного стекла

## Описание
Eurocode Decoder это `PHP` класс, который расшифровывает еврокод стекла и записывает информацию в `CSV` файл.

Расшифровывает марку, модель и год выпуска автомобиля, тип стекла (лобовое, заднее, боковое), цвет, другие характеристики и модификации.
Возвращает строку в `CSV` формате.

Данные для декодирования берет из `json-файлов` в папке `eurocode`:
 - `id.json` - список марок и моделей автомобилей, год их выпуска
 - `type.json` - типы стекол (лобовое, заднее, боковые)
 - `color.json` - цвета стекол
 - `args.json` - другие данные о стекле. Для лобовых - цвет полосы, для других - типы дверей
 - `other.json` - список характеристик и модификаций для стекол

## Использование
Подключите класс и создайте объект:
```php
include 'EuroCodeDecoder.php';
$decoder = new EuroCodeDecoder();
```
Для расшифровки еврокода есть 2 метода:
 - `decode($eurocode)` - декодирует еврокод $eurocode и возвращает `CSV` строку с расшифрованными данными
 - `writeToFile($eurocode)` - делает тоже самое, что и decode(), но результат записывает в файл `decode.csv` в папку `eurocode`
```php
// Вывод результата на экран
echo $decoder->decode("6047AGNBLP");
// Запись результата в файл
$decoder->writeToFile("6047AGNBLP");
```

### Пример использования
```html
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include 'EuroCodeDecoder.php';
        
        $decoder = new EuroCodeDecoder();
        echo $decoder->decode("6047AGNBLP");
        ?>
    </body>
</html>
```
