<?php

class TypeHobnailsDB {
    public $host;
    public $dbName;
    public $user;
    public $password;
    public $connection;
    public $dbTableName;
    public $records;

    //db connect
    public function __construct($host, $dbName, $user, $password, $dbTableName) {
        $this->host = $host;
        $this->dbName = $dbName;
        $this->user = $user;
        $this->password = $password;
        $this->dbTableName = $dbTableName;
        try {
            $this->connection = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbName, $this->user, $this->password);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //request get records
    public function getRecords() {
        try {
            // Get all records
            $sth = $this->connection->prepare('SELECT * FROM '. $this->dbTableName);
            $sth->execute();
            return $this->records = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    //request insert record
    public function insertRecord($typeHb, $wt) {
        try {
            $stmt = $this->connection->prepare('INSERT INTO '.$this->dbName. ' (typeHb, wt) VALUES (:typeHb, :wt)');
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
            $bid = $this->connection->prepare('SELECT wt FROM '. $this->dbName . ' WHERE id = '.$posted);
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
            $stmt = $this->connection->prepare('DELETE FROM '. $this->dbName .' WHERE id IN (' . implode(",", $posted). ')');
            $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

$config = parse_ini_file('config/config.ini');
$entry = new TypeHobnailsDB($config['host'], $config['dbName'], $config['user'], $config['password'], $config['dbName']);

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
    <script src="http://code.jquery.com/jquery-3.4.0.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"></script>

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
                    if (isset($_POST['typeHobnail']) && $_POST['typeHobnail'] == $result['id']) {
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
                <button type="submit" class="" form="add">Отправить</button>
            </form>
        </div>
    </div> <!--div operation ~ total table, add records and delete this-->

    <hr>

    <label>Плитка</label>
    <div class="col-lg-12 count_tile">

        <label for="">Плитка/плиты:</label>
        <div class="tile_size">
            <input type="number" step='0.1' id="tile_len" placeholder="Длина(см)" onchange="inputTileLen(this)">
            <input type="number" step='0.1' id="tile_w" placeholder="Ширина(см)" onchange="inputTileW(this)">
            <input type="number" step='0.01' id="tile_s_cm" placeholder="Площадь(кв.см)" onchange="inputScm(this)">
            <input type="number" step='0.0001' id="tile_s_m" placeholder="Площадь(кв.м)" onchange="inputSm(this)">
        </div>

        <label for="">Площадь поверхности:</label>
        <div class="wall_size">
            <input type="number" id="surface_square" placeholder="Площадь (кв.м)">
        </div>

        <label for="">Двери:</label>
        <div class="door_size">
            <div class="door door_one">
                <label for="">1:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="door door_two">
                <label for="">2:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="door door_three">
                <label for="">3:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="door door_four">
                <label for="">4:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <button class="add_door" data-id="add_door">Добавить</button>
        <label for="">Окна:</label>
        <div class="window_size">
            <div class="window window_one">
                <label for="">1:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="window window_two">
                <label for="">2:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="window window_three">
                <label for="">3:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="window window_four">
                <label for="">4:</label>
                <input type="number" placeholder="Длина(м)">
                <input type="number" placeholder="Ширина(м)">
                <input type="number" name="" id="" placeholder="Площадь(кв.м)">
                <button type="button" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <button class="add_window" data-id="add_window">Добавить</button>
        <label>ИТОГО:</label>
        <div class="totalAmount"></div>


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

        var amount; //количество гвоздей
        var total; //итоговый вес гвоздей
        //считает итоговый вес
        function amountValue(vl) {
            amount = vl.value;
            if (amount !== 0 || amount !== null || amount !== undefined) {
                document.getElementById('totalWt').value = amount * wtHobnail;
            }
        }
        //считает количество
        function totalWtValue(vl) {
            total = vl.value;
            if (total !== 0 || total !== null || total !== undefined) {
                document.getElementById('amountHobnail').value = (total / wtHobnail).toFixed(0);
            }
        }

        //открытие новых строк
        var countDoor = 0;
        $('.add_door').click(function () {
            switch (countDoor) {
                case 0:
                    $('.door_one').css('display','block');
                    countDoor++;
                    break;
                case 1:
                    $('.door_two').css('display','block');
                    countDoor++;
                    break;
                case 2:
                    $('.door_three').css('display','block');
                    countDoor++;
                    break;
                case 3:
                    $('.door_four').css('display','block');
                    countDoor=0;
                    break;
            }
        });

        var countWindow = 0;
        $('.add_window').click(function () {
            switch (countWindow) {
                case 0:
                    $('.window_one').css('display','block');
                    countWindow++;
                    break;
                case 1:
                    $('.window_two').css('display','block');
                    countWindow++;
                    break;
                case 2:
                    $('.window_three').css('display','block');
                    countWindow++;
                    break;
                case 3:
                    $('.window_four').css('display','block');
                    countWindow=0;
                    break;
            }
        });

        //закрывает строку
        $('.close').click(function () {
            this.parentNode.style.display = 'none';
        });

        var tileLen; //длина плитки
        var tileW; //ширина плитки
        var tileSCm; //площадь плитки в см.кв
        var tileSM; //площадь плитки в м.кв

        function inputTileLen(vl) {
            tileLen = vl.value; //запоминает значение input
            //если все значения есть, считает площадь в кв.см и кв.м
            if (tileW >= 0) {
                document.getElementById('tile_s_cm').value = tileLen*tileW;
                document.getElementById('tile_s_m').value = tileLen*tileW/10000;
            }
        }
        function inputTileW(vl) {
            tileW = vl.value;//запоминает значение input
            //если все значения есть, считает площадь в кв.см и кв.м
            if (tileLen >= 0) {
                document.getElementById('tile_s_cm').value = tileLen*tileW;
                document.getElementById('tile_s_m').value = tileLen*tileW/10000;
            }
        }

        //расчет м.кв. через см.кв
        function inputScm(vl) {
            tileSCm = vl.value;
            if (tileSM == 0) {
                document.getElementById('tile_len').disabled = false;
                document.getElementById('tile_w').disabled = false;
            } else {
                document.getElementById('tile_s_m').value = tileSCm/10000; //расчет
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
            if (tileSM == 0) {
                document.getElementById('tile_len').disabled = false;
                document.getElementById('tile_w').disabled = false;
            } else {
                document.getElementById('tile_s_cm').value = tileSM*10000; //расчет
                //обнуление длина-ширина при вводе окончательной площади
                document.getElementById('tile_len').disabled = true;
                document.getElementById('tile_len').value = '';
                document.getElementById('tile_w').disabled = true;
                document.getElementById('tile_w').value = '';
            }
        }

        </script>
</body>
</html>