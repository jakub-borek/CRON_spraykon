<?php

include('model.php');
include('controller.php');
include('mail_template.php');
?>

<?php

$orders_unsorted = selectOrders();
$orders = sortOrders($orders_unsorted);
$unsent_orders = checkWhichOrdersWereAlreadySent($orders);
$mail_messages = generateMailMessages($unsent_orders);

sendMails($mail_messages);
setMailsToSent($unsent_orders);

deleteOldInfoAboutMails();

