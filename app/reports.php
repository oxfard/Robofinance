<?php

// Настройки
// ini_set('display_errors', 'Off'); // Вывод ошибок
$db_server = 'host.docker.internal';
$db_user = "robofinance";
$db_password = "111Ghjcnjvjk222_!";
$db_name = "robofinance";


header("Content-Type: text/html; charset=utf-8");

$mysqli = new mysqli($db_server, $db_user, $db_password, $db_name);
if (mysqli_connect_error()) {
   die('Не могу подключиться к БД: '. mysqli_connect_error() );
}
mysqli_set_charset($mysqli , "utf8");

// Валидация $_GET['r']
if (isset($_GET['r'])) {
   $report = (int)$_GET['r'];
} else {
   $report = 0;
}

switch ($report){
   # На испытательном сроке
   case 1:
      $request = "
         SELECT u.id, u.last_name, u.first_name, u.middle_name, 
             p.name position, u.admission_dt admission_dt, 
             ud.dismission_dt dismission_dt, dr.description dismission_reason, p.salary salary, 
             d.description department, 
             IF(up2.user_id = u.id ,'Является начальником', CONCAT(u2.last_name, ' ', u2.first_name, ' ', u2.middle_name)) department_leader
         FROM	
             user u
             LEFT JOIN user_dismission ud ON (u.id = ud.user_id)
             LEFT JOIN dismission_reason dr ON (ud.reason_id = dr.id)
             LEFT JOIN user_position up ON (u.id = up.user_id)
             LEFT JOIN position p ON (up.position_id = p.id)
             LEFT JOIN department d ON (up.department_id = d.id)
             LEFT JOIN user_position up2 ON (d.leader_id = up2.position_id)
             LEFT JOIN user u2 ON (up2.user_id = u2.id)
         WHERE 
             u.admission_dt BETWEEN CURDATE() - INTERVAL 3 MONTH AND curdate()
             AND (ud.id IS NULL OR ud.is_active = 1) # при услови что ud.is_active=1 значит, что уволен был в случае перевода и продолжает работу
         ORDER BY u.last_name ASC
               ";
      break;
   # Уволенные
   case 2:
      $request = "
         SELECT u.id, u.last_name, u.first_name, u.middle_name, 
             p.name position, u.admission_dt admission_dt, 
             ud.dismission_dt dismission_dt, dr.description dismission_reason, p.salary salary, 
             d.description department, 
             IF(up2.user_id = u.id ,'Является начальником', CONCAT(u2.last_name, ' ', u2.first_name, ' ', u2.middle_name)) department_leader
         FROM	
             user u
             LEFT JOIN user_dismission ud ON (u.id = ud.user_id)
             LEFT JOIN dismission_reason dr ON (ud.reason_id = dr.id)
             LEFT JOIN user_position up ON (u.id = up.user_id)
             LEFT JOIN position p ON (up.position_id = p.id)
             LEFT JOIN department d ON (up.department_id = d.id)
             LEFT JOIN user_position up2 ON (d.leader_id = up2.position_id)
             LEFT JOIN user u2 ON (up2.user_id = u2.id)
         WHERE 
             (ud.id IS NOT NULL and ud.is_active = 0) # при услови что ud.is_active=0 значит, что полностью уволен
         ORDER BY u.last_name ASC
      ";
      break;
   # Начальники
   case 3:
      $request = "
         SELECT u.id, u.last_name, u.first_name, u.middle_name, 
             p.name position, u.admission_dt admission_dt, 
             ud.dismission_dt dismission_dt, dr.description dismission_reason, p.salary salary, 
             d.description department, 
             'Да' AS department_leader
         FROM	
             user u
             LEFT JOIN user_dismission ud ON (u.id = ud.user_id)
             LEFT JOIN dismission_reason dr ON (ud.reason_id = dr.id)
             LEFT JOIN user_position up ON (u.id = up.user_id)
             LEFT JOIN position p ON (up.position_id = p.id)
             LEFT JOIN department d ON (up.department_id = d.id)
         WHERE 
             up.position_id = d.leader_id
         ORDER BY u.last_name ASC
      ";
      break;
   # Все
   default:
      $request = "
         SELECT u.id, u.last_name, u.first_name, u.middle_name, 
             p.name position, u.admission_dt admission_dt, 
             ud.dismission_dt dismission_dt, dr.description dismission_reason, p.salary salary, 
             d.description department, 
             IF(up2.user_id = u.id ,'Является начальником', CONCAT(u2.last_name, ' ', u2.first_name, ' ', u2.middle_name)) department_leader
         FROM	
             user u
             LEFT JOIN user_dismission ud ON (u.id = ud.user_id)
             LEFT JOIN dismission_reason dr ON (ud.reason_id = dr.id)
             LEFT JOIN user_position up ON (u.id = up.user_id)
             LEFT JOIN position p ON (up.position_id = p.id)
             LEFT JOIN department d ON (up.department_id = d.id)
             LEFT JOIN user_position up2 ON (d.leader_id = up2.position_id)
             LEFT JOIN user u2 ON (up2.user_id = u2.id)
         ORDER BY u.last_name ASC
         ";
}

$result = $mysqli->query($request);
$records = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($records);

?>
