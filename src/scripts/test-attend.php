<?php
use Drupal\office\Entity\Attendance;
use Drupal\office\x;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Member;
use Drupal\office\Entity\Task;
test_attend();
function test_attend() {

	$username = 'jaeho';
	$user = user_load_by_name($username);


	$begin_date = 20150501;
	for( $k = 0; $k < 31; $k++ ) {
		$date = $begin_date + $k;
		$h = rand(10,23);
		$i = rand(10,59);
		$stamp = strtotime("$h$i");
		$r = date('r', $stamp);
		$re = Attendance::attend($user->id(), $date, $stamp);
		//echo "TEST: $date : $stamp : $r\n";
		if ( $re ) {
			//$message = x::getErrorMessage($re);
			echo "ERROR: $re : $r\n";
		}
		else {
			echo "OK: $r \n";
		}
	}





}
