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
        $decoder->writeToFile("6047AGNBLP");
        ?>
    </body>
</html>