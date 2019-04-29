<?php

error_reporting (E_ALL);

class TypeHobnailsDB {
    public $host;
    public $dbName;
    public $user;
    public $password;
    public $connection;
    public $dbTableName = "dbHobnails";
    public $records;

    //db connect
    public function __construct($config) {
        $this->host = $config['host'];
        $this->dbName = $config['dbName'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        try {
            $this->connection = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbName, $this->user, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->exec('SET NAMES utf8');
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //request get records
    public function getRecords() {
        try {
            // Get all records
            $sth = $this->connection->prepare('SELECT * FROM '.$this->dbTableName);
            $sth->execute();
            return $this->records = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //request insert record
    public function insertRecord($typeHb, $wt) {
        try {
            $stmt = $this->connection->prepare('INSERT INTO '.$this->dbTableName. ' (typeHb, wt) VALUES (:typeHb, :wt)');
            $stmt->bindParam(':typeHb', $typeHb);
            $stmt->bindParam(':wt', $wt);
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //select wt Hobnails
    public function selectById($posted) {
        try {
            $bid = $this->connection->prepare('SELECT wt FROM '. $this->dbTableName . ' WHERE id = '.$posted);
            $bid->execute();
            $test = $bid->fetch(PDO::FETCH_NUM);
            echo $test[0];
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //delete records
    public function deleteRow($posted) {
        try {
            $posted = implode(",", $posted);
            $stmt = $this->connection->prepare('DELETE FROM '. $this->dbTableName .' WHERE id IN (' . $posted . ')');
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

$config = parse_ini_file('config/config.ini');
$entry = new TypeHobnailsDB($config);

//all row
$results = $entry->getRecords();

// insert
if (isset($_POST["inputWt"]) && isset($_POST["inputType"])) {
    $entry->insertRecord($_POST['inputType'], $_POST['inputWt']);
    header ('Location:index.php');
}

if (isset($_POST["deleteId"])) {
    $entry->deleteRow($_POST['deleteId']);
    header ('Location:index.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Счетчик</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <script
            src="http://code.jquery.com/jquery-3.4.0.js"
            integrity="sha256-DYZMCC8HTC+QDr5QNaIcfR7VSPtcISykd+6eSmBW5qo="
            crossorigin="anonymous"></script>

</head>
<body>
<div class="container">

    <label>Гвозди</label>
    <div class="col-lg-12 count">
        <form class="form_count" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="typeHobnail">ВИД:</label>
            <select name="typeHobnail" id="typeHobnail" onchange="this.form.submit()">
                <option>Выбрать вид</option>
                <?php
                //select typeHb
                foreach ($results as $result) {
                    //last selected option
                    if (/*isset($_POST['typeHobnail']) &&*/ $_POST['typeHobnail'] == $result['id']) {
                        ?>
                        <option value="<?=$result['id']?>" selected><?=$result['typeHb']?></option>
                        <?php
                    } else {
                        ?>
                        <option value="<?=$result['id']?>"><?=$result['typeHb']?></option>
                        <?php
                    }
                }
                ?>
            </select><br>
            <label for="wtHobnail">ВЕС(грамм):</label>
            <span id="wtHobnail">
                <?php
                //select wtHb one of type
                if (isset($_POST['typeHobnail'])) {
                    //get id in db
                    echo $entry->selectById($_POST['typeHobnail']);
                } else {
                    echo '0';
                }
                ?>
            </span>
        </form>
        <label for="amountHobnail">Количество: </label>
        <input type="number" step="0.1" name="amountHobnail" id="amountHobnail" onchange="amountValue(this)"><br>
        <label for="totalWt">Вес итого(грамм): </label>
        <input type="number" step="0.01" name="totalWt" id="totalWt" onchange="totalWtValue(this)" >
    </div> <!--div count-->

    <button class="open_table">Сводная таблица</button> <!--btn for open total table-->

    <div class="operation">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="totalTable" id="totalTable">
            <table class="table-sm col-lg-12">
                <tr>
                    <th>Вид</th>
                    <th>Вес</th>
                </tr>
                <?php
                foreach ($results as $result) {
                    ?>
                    <tr>
                        <td><?=$result['typeHb']?></td>
                        <td><?=$result['wt']?></td>
                        <td><input type="checkbox" form="totalTable" name="deleteId[]" value="<?=$result['id']?>"></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <button class="add_btn" type="button">Добавить</button>
            <button class="del_btn" type="submit" form="totalTable">Удалить</button>
        </form>

        <div class="form_add">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="add" id="add">
                <input type="text" name="inputType" form="add" placeholder="Вид" id="inputType">
                <input type="number" step="0.01" name="inputWt" form="add" placeholder="Вес" id="inputWt">
                <button type="submit" form="add">Отправить</button>
            </form>
        </div>
    </div> <!--div operation ~ total table, add records and delete this-->

    <hr>

    <label>Плитка</label>
    <div class="col-lg-12 count_tile">

        <label for="">Плитка/плиты:</label>
        <div class="tile_size">
            <input type="text" step="any" id="tile_len" placeholder="Длина(см)" onchange="inputTileLen(this)">
            <input type="text" step="any" id="tile_w" placeholder="Ширина(см)" onchange="inputTileW(this)">
            <input type="text" step="any" id="tile_s_cm" placeholder="Площадь(кв.см)" onchange="inputScm(this)">
            <input type="text" step="any" id="tile_s_m" placeholder="Площадь(кв.м)" onchange="inputSm(this)" required>
        </div>

        <label for="">Площадь поверхности:</label>
        <div class="wall_size">
            <input type="text" step="any" id="surface_square" placeholder="Площадь (кв.м)" onchange="inputSurface(this)" required>
        </div>

        <label for="">Окна/Двери:</label>
        <div class="door_size">
            <div class="door" id="door">
                <input type="text" id="doorWinLen" onchange="doorWinLen(this)" placeholder="Длина(м)">
                <input type="text" id="doorWinW" onchange="doorWinW(this)" placeholder="Ширина(м)">
                <input type="text" id="doorWin" onchange="doorWinF(this)" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close" value="1">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <button class="add_door" data-id="add_door">Добавить</button>
        <input type="text" id="doorWindow" placeholder="Общая площадь окон и дверей" onchange="totalSWinDoor(this)"><br>

        <div class="total_div">
            <label>ИТОГО:</label>
            <div class="totalAmount" id="totalAmount"></div>
        </div>
        <button id="count">Рассчитать</button> 
        <button id="clear_all">Очистить форму</button>
        <div class="div_btn"></div>
    </div>

</div>


    <script>
        //open or close .form_add
        $('.add_btn').click(function () {
            if ($('.form_add').css('display') === 'block') {
                $('.form_add').css('display','none');
            } else {
                $('.form_add').css('display','block');
            }
        });

        $('.open_table').click(function () {
            if ($('.operation').css('display') === 'block') {
                $('.operation').css('display','none');
            } else {
                $('.operation').css('display','block');
            }
        });

        var amount = 0; //количество гвоздей
        var total = 0; //итоговый вес гвоздей
        var wtHobnail = document.getElementById('wtHobnail').textContent;
        //считает итоговый вес
        function amountValue(vl) {
            
            amount = vl.value;
            total = amount * wtHobnail;
            document.getElementById('totalWt').value = total;
        }
        //считает количество
        function totalWtValue(vl) {
            total = vl.value;
            document.getElementById('amountHobnail').value = (total / wtHobnail).toFixed(0);
        }

        var tileLen = 0; //длина плитки
        var tileW = 0; //ширина плитки
        var tileSCm; //площадь плитки в см.кв
        var tileSM = 0; //площадь плитки в м.кв
        var surfaceSM = 0; //площадь поверхности м.кв.
        var doorWindowLen = 0; //длина двери/окна
        var doorWindowW = 0; //ширина двери/окна
        var doorWin = 0; //площадь одной двери/окна
        var totalDoorWindow = 0; //площадь всех дверей и окон
        
        function inputTileLen(vl) {
            tileLen = vl.value; //запоминает значение input
            //если все значения есть, считает площадь в кв.см и кв.м
            document.getElementById('tile_len').value = tileLen + ' см';
            if (tileW >= 0) {
                tileSCm = tileLen*tileW;
                tileSM = tileSCm/10000;
                document.getElementById('tile_s_cm').value = tileSCm + ' кв.см';
                document.getElementById('tile_s_m').value = tileSM + 'кв.м';
            }
        }
        function inputTileW(vl) {
            tileW = vl.value;//запоминает значение input
            //если все значения есть, считает площадь в кв.см и кв.м
            document.getElementById('tile_w').value = tileW + ' см';
            if (tileLen >= 0) {
                tileSCm = tileLen*tileW;
                tileSM = tileSCm/10000;
                document.getElementById('tile_s_cm').value = tileSCm+ ' кв.см';
                document.getElementById('tile_s_m').value = tileSM + ' кв.м';
            }
        }

        //расчет м.кв. через см.кв
        function inputScm(vl) {
            tileSCm = vl.value;
            document.getElementById('tile_s_cm').value = tileSCm + ' кв.см';
            if (tileSCm == 0) {
                document.getElementById('tile_len').disabled = false;
                document.getElementById('tile_w').disabled = false;
            } else {
                tileSM = tileSCm/10000;
                document.getElementById('tile_s_m').value = tileSM + ' кв.м'; //расчет
                //обнуление длина-ширина при вводе окончательной площади
                document.getElementById('tile_len').disabled = true;
                document.getElementById('tile_len').value = '';
                document.getElementById('tile_w').disabled = true;
                document.getElementById('tile_w').value = '';
            }
        }
        //расчет см.кв. через м.кв.
        function inputSm(vl) {
            tileSM = vl.value;
            document.getElementById('tile_s_m').value = tileSM + ' кв.м'; 
            if (tileSM == 0) {
                document.getElementById('tile_len').disabled = false;
                document.getElementById('tile_w').disabled = false;
            } else {
                tileSCm = tileSM*10000;
                document.getElementById('tile_s_cm').value = tileSCm + ' кв.см'; //расчет
                //обнуление длина-ширина при вводе окончательной площади
                document.getElementById('tile_len').disabled = true;
                document.getElementById('tile_len').value = '';
                document.getElementById('tile_w').disabled = true;
                document.getElementById('tile_w').value = '';
            }
        }
        
        function inputSurface(vl) {
            surfaceSM = vl.value;
            document.getElementById('surface_square').value = surfaceSM + ' кв.м.';
        }

        function doorWinLen(vl) {
            doorWindowLen = vl.value;
            document.getElementById('doorWinLen').value = doorWindowLen + ' м';
            if (doorWindowW > 0) {
                doorWin = doorWindowW * doorWindowLen;
                document.getElementById('doorWin').value = doorWin + ' кв.м';
            }
        }

        function doorWinW(vl) {
            doorWindowW = vl.value;
            document.getElementById('doorWinW').value = doorWindowW + ' м';
            if (doorWindowLen > 0) {
                doorWin = doorWindowW * doorWindowLen;
                document.getElementById('doorWin').value = doorWin + ' кв.м';
            }
        }

        function doorWinF(vl) {
            doorWin = vl.value;
            document.getElementById('doorWin').value = doorWin + ' кв.м';
            if (doorWin == 0) {
                document.getElementById('doorWinLen').disabled = false;
                document.getElementById('doorWinW').disabled = false;
            } else {
                document.getElementById('doorWinLen').value = '';
                document.getElementById('doorWinLen').disabled = true;
                document.getElementById('doorWinW').value = '';
                document.getElementById('doorWinW').disabled = true;
            }
        }

        //add new door/window
        $('.add_door').click(function () {
            if (document.getElementById('door').style.display == 'block') {
                //считаем результат в одну переменную
                if (doorWin > 0) {
                    totalDoorWindow += parseFloat(doorWin);
                    document.getElementById('doorWindow').value = totalDoorWindow + ' кв.м';
                }

                //обнуляем инпуты
                var child = document.getElementById('door').childNodes;
                doorWin = 0;
                for (i=0; i<child.length;i++) {
                    if (child[i].localName == "input") {
                        child[i].disabled = false;
                        child[i].value = '';
                    }
                }
            } else {
                document.getElementById('door').style.display = 'block';
            }
        });

        //закрывает строку
        $('.close').click(function () {
            var elem = this.parentNode;
            var elemId = elem.getAttribute('id');
            elem.style.display = 'none';

            var child = document.getElementById(elemId).childNodes;
            for (i=0; i<child.length;i++) {
                if (child[i].localName == "input") {
                    child[i].value = '';
                }
            }
        });

        function totalSWinDoor(vl) {
            totalDoorWindow = parseFloat(vl.value);
            document.getElementById('doorWindow').value = totalDoorWindow + ' кв.м';
        }



        $('#count').click(function () {
            console.log(tileSM, surfaceSM, totalDoorWindow);
            if(tileSM > 0 && surfaceSM > 0) {
                calc(tileSM, surfaceSM, totalDoorWindow);
            }
        });

        function calc(tileSM, surfaceSM, doorWin) {
            var result = (surfaceSM - doorWin)/tileSM;
            result += result/10;
            document.getElementById('totalAmount').innerText = result.toFixed(2);
            document.getElementById('totalAmount').innerText += ' eд.';
        }

        $('#clear_all').click(function () {
            location.reload(true);
        });
        </script>
</body>
</html>