<?php

// sprawdzanie, czy zamówienie posiada kanister
function containsCanister($order_to_check, $orders_unsorted) {
    $contains_canister = 0;
    foreach ($orders_unsorted as $order) {
        if ($order['id_order'] == $order_to_check['id_order'] && $order['canistre'] == 1) {
            $contains_canister = 1;
        }
    }
    return $contains_canister;
}

//sortowanie produktów
function sortOrders($orders_unsorted) {
    $n = $m = 0;
    $orders = [];
    foreach ($orders_unsorted as $order) {
        //dodawanie do bułgarskich produktów
        if ($order['id_shop'] == 2) {
            if (empty($orders[$order['id_order']])) {
                $n = $m = 0;
            }
            $orders[$order['id_order']]['products'][$n] = $order;
            $n++;
        } else if ($order['id_shop'] == 1) {
            if (empty($orders[$order['id_order']])) {
                $n = $m = 0;
            }
            $contains_canister = containsCanister($order, $orders_unsorted);

            //dodawanie do kanistrów rumuńskich
            if ($contains_canister == 1) {
                $orders[$order['id_order']]['canistre'][$m] = $order;
                $m++;
            }
            //dodawanie do boxów rumuńskich
            else {
                $orders[$order['id_order']]['box'][$n] = $order;
                $n++;
            }
        }
        //data, ułatwia przy wysyłaniu maila
        $orders[$order['id_order']]['date_add'] = $order['date_add'];
    }


    return $orders;
}

//sprawdza, które maile były już wysłane, według tabeli z bazy danych spraykon.ro
function checkWhichOrdersWereAlreadySent($orders) {
    $sent_mails = selectInfoSentMails();
    foreach ($orders as $id_order => $order) {
        $was_sent = false;
        foreach ($sent_mails as $sent_mail) {
            if ($sent_mail ['id_order'] == $id_order && $sent_mail ['is_sent'] == 1) {
                $was_sent = true;
            }
        }
        if ($was_sent == true) {
            unset($orders[$id_order]);
        }
    }


    return $orders;
}

//sprawdzanie kodu magazynowego produktu według listy przesłanej przez Van Moer
function checkProductCode($id) {
    $code = '';
    switch ($id) {
        case 12: $code = 'SK-03';
            break;
        case 13: $code = 'SK-09';
            break;
        case 14: $code = 'SK-06';
            break;
        case 15: $code = 'SK-04';
            break;
        case 16: $code = 'SK-08';
            break;
        case 17: $code = 'SK-11';
            break;
        case 18: $code = 'LK-01';
            break;
        case 19: $code = 'SK-01';
            break;
        case 20: $code = 'SK-02';
            break;
        case 21: $code = 'SK-05';
            break;
        case 23: $code = 'DZ-04';
            break;
        case 24: $code = 'DZ-03';
            break;
        case 25: $code = 'AT-01';
            break;
        case 26: $code = 'RL-01';
            break;
        case 27: $code = 'RL-02';
            break;
        case 28: $code = 'RL-03';
            break;
        case 29: $code = 'SK-10';
            break;
        case 30: $code = 'SK-11';
            break;
        case 31: $code = 'SK-09';
            break;
        case 32: $code = 'LK-01';
            break;
        case 33: $code = 'SK-08';
            break;
        case 34: $code = 'SK-06';
            break;
        case 35: $code = 'SK-04';
            break;
        case 36: $code = 'SK-03';
            break;
        case 37: $code = 'SK-12';
            break;
    }
    return $code;
}

//konwersja arraya produktów na listę w jednym stringu, podział na kanistry, boxy i akcesoria
function productList($products, $shop) {
    $product_list = '';

    if ($shop == 'romania') {
        $box = ' BAX: ';
        $canister = ' CANISTRA: ';
        $canisters = ' CANISTRE: ';
        $bottle = ' BUC. ';
    } else if ($shop == 'bulgary') {
        $box = ' Кашон : ';
        $canister = ' Бутилка: ';
        $canisters = ' Бутилка: ';
        $bottle = ' БР. ';
    }


    foreach ($products as $product) {
        if ($product['box'] == 1) {
            $product['product_quantity'] = ($product['product_quantity'] * 12) . $bottle;
        } else if ($product ['canistre'] == 1) {
            if ($product['product_quantity'] == 1) {
                $product['product_quantity'] = $product['product_quantity'] . $canister;
            } else {
                $product['product_quantity'] = $product['product_quantity'] . $canisters;
            }
        } else if ($product ['accesorii'] == 1) {
            $product['product_quantity'] = $product['product_quantity'] . '';
        } else {
            $product['product_quantity'] = $product['product_quantity'] . $bottle;
        }

//obcinanie pierwszych dwóch słów w nazwie produktu
        $name = explode(' ', ltrim($product['product_name']), 3);
        foreach ($name as $key => $word) {
            if ($word == 'SPRAY-KON') {
                $name[$key] = '';
            }
        }
        if (!isset($name[1])) {
            $name[1] = '';
        }
        //specjalne przypadki sprawdzane po id produktu
        if ($product ['product_id'] == 15 || $product ['product_id'] == 35) {
            //$name[1] .= ' + ATOMIZARE'; - wyłączyłem + ATOMIZARE na prośbę klienta: Oskar
            $name[1] .= ' ';
        }
        if ($product['product_id'] == 19) {
            $name[1] .= ' 22l';
        }
        if ($product['product_id'] == 20) {
            $name[1] .= ' 13,6l';
        }

        $code = checkProductCode($product['product_id']);
        $product_list .= '- ' . $code . ' ' . $name[0] . ' ' . $name[1] . ', ' . $product['product_quantity'] . '<br />';
    }
    return

            $product_list;
}

// w ilu kartonach zmieszczą się aerozole
function howManyBoxes($products) {
    $amount_of_boxes = 0;
    $quantity = 0;
    $temp = 0;
    foreach ($products as $product) {
        if ($product['box'] == 1 || $product['canistre'] == 1)
            $amount_of_boxes+= $product['product_quantity'];
        else if ($product['box'] == 0 && $product['canistre'] == 0 && $product['accesorii'] == 0) {
            while (($product['product_quantity'] - $temp) >= 12) {
                $amount_of_boxes++;
                $temp+=12;
            }

            $quantity += $product['product_quantity'];
            $quantity -= $temp;
            $temp = 0;
        }
    }
    while ($quantity > 0) {
        switch ($quantity) {
            case 1: $amount_of_boxes++;
                $quantity--;
                break;
            case 2: $amount_of_boxes++;
                $quantity-= 2;
                break;
            case 3: $amount_of_boxes++;
                $quantity -= 3;
                break;
            default: $amount_of_boxes++;
                $quantity -= 4;
                break;
        }
    }

    return $amount_of_boxes;
}

//przeliczenie ceny płatności przy odbiorze
function cashOnDeliveryPrice($price, $reduction_percent, $reduction_amount, $quantity) {

    $taxe = 1.19;
    $price_taxe = $price * $taxe;

    if ($reduction_amount != 0) {
        $final_price = ($price - $reduction_amount) * 1.19;
    } else if ($reduction_percent != 0) {
        $final_price = $price_taxe - 0.01 * $reduction_percent * $price_taxe;
    } else {
        $final_price = $price_taxe;
    }
    $final_price *= $quantity;
    $final_price_formatted = round($final_price, 2);
    return

            $final_price_formatted;
}

function isWeekend($date, $shop) {
    if ($shop == 2) { //strefa czasowa dla bułgarii, czas powyżej którego wysyłka ma być na następny dzień
        $nextDayDelivery = new DateTime('17:30:00');
    } else {// dla rumunii
        $nextDayDelivery = new DateTime('13:30:00');
    }
    $day_date = strtolower(date("l", strtotime($date->format('Y-m-d'))));
    if ($day_date == "saturday" || $day_date == "sunday" || ($day_date == "friday" && $date > $nextDayDelivery)) {
        return 'weekend';
    } else if ($day_date == "thursday" && $date > $nextDayDelivery) {
        return 'thursday';
    } else if ($day_date == "friday" && $date < $nextDayDelivery) {
        return 'friday';
    } else {
        return

                'week';
    }
}

function discountPercentage($originalPrice, $discount) {

    $discountPercentage = $discount / $originalPrice;
    return

            $discountPercentage;
}

function getFinalPrice($order) { //przeliczenie ceny po uwzględnieniu discount i podatków
    if ($order [0]['payment'] == 'Cash on delivery (COD)') {
        $final_price = 0;
        foreach ($order as $product) {
            $final_price += cashOnDeliveryPrice($product['product_price'], $product['reduction_percent'], $product['reduction_amount'], $product['product_quantity']);
        }
        $discountPercentage = discountPercentage($order[0]['total_products_wt'], $order[0]['total_discounts']);
        if ($discountPercentage != 0) {
            $final_price *= (1 - $discountPercentage);
        }
        $mail_variables['cash_on_delivery'] = ', RAMBURS IN CONT COLECTOR AMERIPOL ' . number_format($final_price, 2, ',', ' ') . ' lei (CONT AMERIPOL RO48CITI0000000120335006) <br />';
    } else {
        $mail_variables['cash_on_delivery'] = '';
    }
    return $mail_variables['cash_on_delivery'];
}

function getDateDelivery($shop) { // ustalenie daty odbioru z magazynu i daty dostawy zależnie od dnia tygodnia i godziny złożenia zamówienia
    $createDate = new DateTime('+1 hours'); //rumuńska oraz bułgarska strefa czasowa względem serwera ameripolu
    $mail_variables = [];
    $monday = date('Y-m-d', strtotime('next Monday', strtotime($createDate->format('Y-m-d'))));
    $isweekend = isWeekend($createDate, $shop);
    switch ($isweekend) {
        case 'week':
            if ($shop == 2) {
                $nextDayDelivery = new DateTime('17:30:00');
            } else {
                $nextDayDelivery = new DateTime('13:30:00');
            }
            $date = $createDate->format('Y-m-d');
            if ($createDate > $nextDayDelivery) {
                $mail_variables['date_+1'] = date('Y-m-d', strtotime($date . ' +1 day'));
                $mail_variables['date_+2'] = date('Y-m-d', strtotime($date . ' +2 day'));
            } else {
                $mail_variables['date_+1'] = date('Y-m-d', strtotime($date));
                $mail_variables['date_+2'] = date('Y-m-d', strtotime($date . ' +1 day'));
            }
            break;
        case 'thursday':
            $mail_variables['date_+1'] = date('Y-m-d', strtotime($createDate->format('Y-m-d') . ' +1 day'));
            $mail_variables['date_+2'] = date('Y-m-d', strtotime($monday));
            break;
        case 'friday':
            $mail_variables['date_+1'] = date('Y-m-d', strtotime($createDate->format('Y-m-d')));
            $mail_variables['date_+2'] = date('Y-m-d', strtotime($monday));
            break;
        case 'weekend':
            $mail_variables['date_+1'] = date('Y-m-d', strtotime($monday));
            $mail_variables['date_+2'] = date('Y-m-d', strtotime($monday . ' +1 day'));
            break;
    }

    return

            $mail_variables;
}

//zapisanie numeru zamówienia do pliku zewnętrznego
function setNewOrderNumber($order_nr) {
    $date = date('Y-m-d');
    $file_content = $date . "\n" . $order_nr;
    file_put_contents('order_nr.txt', $file_content);
    return;
}

//pobieranie numeru zamówienia dzisiaj, numerując od 1, dane przechowywane w pliku order_number.txt
function getTodayOrderNumber() {

    if (!(file('order_nr.txt'))) {
        $date = date('Y-m-d');
        $order_nr = 1;
    } else {
        $file_lines = file('order_nr.txt');
        $date = $file_lines[0];
        $order_nr = $file_lines[1];

        $today_date = date('Y-m-d');
        $date = trim(preg_replace('/\s+/', ' ', $date));
        if ($today_date == $date) {
            $order_nr ++;
        } else
            $order_nr = 1;
    }
    setNewOrderNumber($order_nr);
    return $order_nr;
}

function getMailVariables($order, $is_romania) {
    $mail_variables = [];
    $mail_variables['company_title'] = $mail_variables['company'] = $order[0]['company'];
    if (empty($order[0]['company'])) {
        $mail_variables['company_title'] = $order [0]['firstname'] . ' ' . $order[0]['lastname'];
    }
    $mail_variables['firstname'] = $order[0]['firstname'];
    $mail_variables['lastname'] = $order[0]['lastname'];
    $mail_variables['address1'] = $order[0]['address1'];
    if ($order[0]['address2']) {
        $mail_variables['address2'] = $order [0]['address2'] . "<br />";
    } else {
        $mail_variables['address2'] = '';
    }
    if ($order[0]['state_name']) {
        $mail_variables['state_name'] = 'Jud. ' . $order[0] ['state_name'] . "<br />";
    } else {
        $mail_variables['state_name'] = '';
    }

    $mail_variables['postcode'] = $order[0]['postcode'];
    $mail_variables['city'] = $order[0]['city'];
    if ($order[0]['phone']) {
        $mail_variables['phone'] = 'Tel.: ' . $order[0]['phone'];
    } else {
        $mail_variables['phone'] = '';
    }
    if ($order[0]['phone_mobile']) {
        $mail_variables['phone_mobile'] = 'Tel.: ' . $order[0] ['phone_mobile'] . '<br />';
    } else {
        $mail_variables['phone_mobile'] = '';
    }


//zmienne związane z płatnością pobraniową
    $mail_variables['cash_on_delivery'] = getFinalPrice($order);
// zmienne związane z czasem dostarczenia przeszyłki
    $date_delivery = getDateDelivery($order[0]['id_shop']);
    $mail_variables['date_+1'] = $date_delivery['date_+1'];
    $mail_variables['date_+2'] = $date_delivery['date_+2'];

    var_dump($mail_variables['company']);
    //waga
    $mail_variables['total_weight'] = 0;
    foreach ($order as $product) {
        var_dump($product);
        $mail_variables['total_weight'] += str_replace(',', '.', $product ['weight']) * $product['product_quantity'];
    }
    var_dump($mail_variables['total_weight']);

    //numer zamówienia
    if ($is_romania) {
        $mail_variables['order_nr'] = getTodayOrderNumber();
        $mail_variables['amount_of_boxes'] = howManyBoxes($order);
    }

    return $mail_variables;
}

function checkPayer($products) {
//    $price = $products[0]['total_products_wt'];
//    $value = 70.00; // wartość od której dostawa powinna być darmowa
//    if (floatval($price) < $value) {
//        return 'Client';
//    } else {
//        return 'Ameripol';
//    }
//    


    $amount_of_bottles = 0;
    foreach ($products as $product) {
        if ($product['box'] == 0 && $product['canistre'] == 0 && $product['accesorii'] == 0) {
            $amount_of_bottles += $product['product_quantity'];
        } else {
            return 'Ameripol';
        }
    }
    if ($amount_of_bottles < 4) {
        return 'Client';
    } else {
        return 'Ameripol';
    }
}

function generateMailMessages($orders) {
    $mail_messages = [];
    foreach ($orders as $order) {
        if (!empty($order['products'])) {
            $product_list = productList($order['products'], 'bulgary');
            $mail_variables = getMailVariables($order['products'], 0);
            $mail_variables['payer'] = checkPayer($order['products']);
            if ($order['products'][0]['payment'] == 'Наложен платеж (COD)') {
                $mail_variables['cash_on_delivery'] = number_format($order['products'][0]['total_paid'], 2, '.', '');
            } else {
                $mail_variables['cash_on_delivery'] = '';
            }
            $mail_messages[$order['products'][0]['id_order']]['products'] = generateMailBG($mail_variables, $product_list);
            $mail_messages[$order['products'][0]['id_order']]['firm'] = $mail_variables['company_title'];
            $mail_messages[$order['products'][0]['id_order']]['shop'] = 'bulgary';
        }
        if (!empty($order['canistre'])) {
            $product_list = productList($order['canistre'], 'romania');
            $mail_variables = getMailVariables($order['canistre'], 1);
            $courier = 'Englmayer';
            $mail_messages[$order['canistre'][0]['id_order']]['canistre'] = generateMail($mail_variables, $product_list, $courier);
            $mail_messages[$order['canistre'][0]['id_order']]['firm'] = $mail_variables['company_title'];
            $mail_messages[$order['canistre'][0]['id_order']]['shop'] = 'romania';
        }
        if (!empty($order['box'])) {
            $product_list = productList($order['box'], 'romania');
            $mail_variables = getMailVariables($order['box'], 1);
            $courier = 'NEMO-EXPRESS';
            $mail_messages[$order['box'][0]['id_order']]['box'] = generateMail($mail_variables, $product_list, $courier);
            $mail_messages[$order['box'][0]['id_order']]['firm'] = $mail_variables['company_title'];
            $mail_messages[$order['box'][0]['id_order']]['shop'] = 'romania';
        }
    }
    return

            $mail_messages;
}

function sendMail($message, $courier_email, $courier_name, $order_id, $firm, $shop) {
    $headers = "Content-Type: text/html; charset=utf-8\r\n";
    $date = (new DateTime())->format('d.m.Y');
    if ($shop == 'romania') {
        $title = 'ELIBERARE LA ' . $firm;
    } else if ($shop == 'bulgary') {
        $title = 'поръчка ' . $order_id . '/' . $date . ' ' . $firm . ' [' . $courier_name . ']';
    }

    if (mail($courier_email, $title, $message, $headers)) {
        $mail_sent = 'Mail succesfully sent';
    } else {
        $mail_sent = 'Error with sending';
    }
    file_put_contents("logs.txt", $mail_sent . ', Time when sent: ' . date('Y-m-d H:i:s') . ', ' . $courier_email . ',: ' . $title . ' ' . $message . ' 
	\n
	', FILE_APPEND);
}

function getCourierEmails($couriers) {
    foreach ($couriers as $courier) {
        if ($courier ['id_employee'] == '5') {
            $emails['canistre'] = $courier['email'];
        } else if ($courier ['id_employee'] == '4') {
            $emails['box'] = $courier['email'];
        } else if ($courier ['id_employee'] == '7') {
            $emails['warehouse'] = $courier['email'];
        } else if ($courier ['id_employee'] == '17') {
            $emails['warehouse_2'] = $courier['email'];
        }
    }

    return $emails;
}

function sendMails($mail_messages) {
    $couriers = selectCouriers();
    $emails = getCourierEmails($couriers);
    foreach ($mail_messages as $order_id => $mail_message) {
        if ($mail_message['shop'] == 'romania') {
            if (!empty($mail_message['canistre'])) {
                sendMail($mail_message['canistre'], $emails['canistre'], 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], $emails['warehouse'], 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], $emails['warehouse_2'], 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], 'spraykon.englmayer@gmail.com', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], 'spraykon.magazyn@gmail.com', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], 'spraykon.ro@gmail.com', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], 'n.olarescu@ro.englmayer.net', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['canistre'], 'a.peloiu@ro.englmayer.net', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
                //sendMail($mail_message['canistre'], 'd.tulea@ro.englmayer.net', 'Englmayer', $order_id, $mail_message['firm'], $mail_message['shop']);
            }
            if (!empty($mail_message['box'])) {
                sendMail($mail_message['box'], $emails['box'], 'Nemo-express', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['box'], $emails['warehouse'], 'Nemo-express', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['box'], $emails['warehouse_2'], 'Nemo-express', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['box'], 'spraykon.magazyn@gmail.com', 'Nemo-express', $order_id, $mail_message['firm'], $mail_message['shop']);
                sendMail($mail_message['box'], 'spraykon.ro@gmail.com', 'Nemo-express', $order_id, $mail_message['firm'], $mail_message['shop']);
            }
        } else if ($mail_message['shop'] == 'bulgary') {
            //sendMail($mail_message['products'], 'qbaboreq@gmail.com', 'Econt', $order_id, $mail_message['firm'], $mail_message['shop']);
            sendMail($mail_message['products'], 'filip.n@ameripol.pl', 'Econt', $order_id, $mail_message['firm'], $mail_message['shop']);
            sendMail($mail_message['products'], 'renata.a@ameripol.pl', 'Econt', $order_id, $mail_message['firm'], $mail_message['shop']);
            sendMail($mail_message['products'], 'blagoevgrad@econt.com', 'Econt', $order_id, $mail_message['firm'], $mail_message['shop']);
            sendMail($mail_message['products'], 'nikolay.g@bg.spraykon.eu', 'Econt', $order_id, $mail_message['firm'], $mail_message['shop']);
        }
    }
}

?>