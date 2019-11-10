<?php

function generateMail($mail_variables, $product_list, $courier) {

    $mail = 'COMANDA - ' . $mail_variables['order_nr'] . '<br /><br />'
            . $mail_variables['company'] . ' '
            . (($mail_variables['company']!='')? '(':'') . $mail_variables['firstname'] . ' ' . $mail_variables['lastname'] . (($mail_variables['company']!='')? ')':'') . '<br /><br />'
            . $mail_variables['address1']
            . $mail_variables['address2']
            . ', ' . $mail_variables['postcode'] . ', ' . $mail_variables['city'] . ', '
            . $mail_variables['state_name']
            . $mail_variables['phone'] . '<br />'
            . $mail_variables['phone_mobile'] . '<br />'
            . $product_list . '<br />'
            . '<br />'
            . 'RIDICARE MARFA: ' . $mail_variables['date_+1'] . '<br />'
            . 'URGENT-CARGUS' . '<br />'
            . '……………………………………………………………………' . '<br />'
            . 'LIVRARE LA DESTINATAR: ' . $mail_variables['date_+2'] . '<br />'
            . 'GREUTATE Marfa ' . $mail_variables['total_weight'] . ' kg, ' . $mail_variables['amount_of_boxes'] . ' COLETE<br />'
            . 'PLATA TRANSPORTULUI AMERIPOL CONFORM CONTRACT' . $mail_variables['cash_on_delivery'] . '<br />'
            . 'Transport cu ' . $courier . '<br /><br />';
    return $mail;
}

function generateMailBG($mail_variables, $product_list) {
    $mail = '
<table>
  <tr>
    <th style="border: 1px solid black;">ПОДАТЕЛ/<br>SENDER</th>
    <th style="border: 1px solid black;">АМЕРИ – ПОЛ ТРЕЙДИНГ  ПОЛША ООД</th>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Адрес/Adress</td>
    <td style="border: 1px solid black;">2700 БЛАГОЕВГРАД  ул.“Сан Стефано“ №2</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Име/Name</td>
    <td style="border: 1px solid black;">Николай Груев</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Тел/Tel</td>
    <td style="border: 1px solid black;">+359 876 891 146</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">E-mail</td>
    <td style="border: 1px solid black;">nikolay.g@bg.spraykon.eu</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">ЗА ПРАТКАТА/ FOR PARCEL</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Документи/Documents</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Колет/Parcel</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Карго експрес/Cargo Express</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Карго палет/ Cargo pallet</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Килограми/Kg.</td>
    <td style="border: 1px solid black;"> ' . $mail_variables['total_weight'] . ' </td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Бройки/Pieces</td>
    <td style="border: 1px solid black;">' . $product_list . '</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">ПОЛУЧАТЕЛ/<br>CONSIGNER</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">До Адрес/Adress</td>
    <td style="border: 1px solid black;">'
            . $mail_variables['company'] . '<br />'
            . $mail_variables['address1'] . '<br />'
            . $mail_variables['address2']
            . $mail_variables['postcode'] . ', ' . $mail_variables['city'] . '<br />'
            . $mail_variables['state_name'] . '</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">До офис / To office Econt Express</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Име/Name</td>
    <td style="border: 1px solid black;">' . $mail_variables['firstname'] . ' ' . $mail_variables['lastname'] . '</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Тел/Tel</td>
    <td style="border: 1px solid black;">' . $mail_variables['phone'] . '<br />'
            . $mail_variables['phone_mobile']
            . '</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">E-mail</td>
    <td style="border: 1px solid black;"></td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">За чия сметка пътува/Payer</td>
    <td style="border: 1px solid black;">'. 'Ameripol' . /*. $mail_variables['payer'] .*/ '</td>
  </tr>
  <tr>
    <td style="border: 1px solid black;">Допълнителни услуги/<br>Additional services</td>
    <td style="border: 1px solid black;">' . (!empty($mail_variables['cash_on_delivery']) ? 'Плащане с наложен платеж (COD) ' : '') . '</td>
  </tr>' . (!empty($mail_variables['cash_on_delivery']) ? '<tr>
    <td style="border: 1px solid black;">Наложен платеж :/<br>Cash on delivery:</td>
    <td style="border: 1px solid black;">' . $mail_variables['cash_on_delivery'] . ' лева</td>
  </tr>' : '')
            . '<tr>
    <td style="border: 1px solid black;">Дата и час на товарене/<br>Date and time for Pick up</td>
    <td style="border: 1px solid black;">' . $mail_variables['date_+1'] . '</td>
  </tr>
  </table>
';
    return $mail;
}

?>