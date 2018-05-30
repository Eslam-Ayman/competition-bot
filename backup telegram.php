<?php 
/*ALTER DATABASE telegram CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE chat CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE quistions CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE repeared CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;*/

	$servername ='localhost';
	$username = 'userTelegram';
	$password = 'aa951753';
	$database = 'telegramBot';

	$conn = mysqli_connect($servername, $username, $password, $database);
	// mysqli_select_db($conn, $database);
	$sSQL= 'SET CHARACTER SET utf8'; 
	mysqli_query($conn,$sSQL);

	ini_set("error_reporting", E_All); // to use telegram

	$botToken = "302919708:AAGcqbRr4QLqq_Qn1PTEiFrXnYgE7bZ5Uxs";
	$website = "https://api.telegram.org/bot".$botToken;

	$update = file_get_contents('php://input'); // to get message
	$update = json_decode($update, TRUE); // appear as jason

	$chatId = $update["message"]["chat"]["id"]; // get id of user
	$chatName = $update["message"]["chat"]["first_name"]; // get name of user
	$message = $update["message"]["text"]; // get message from user

	$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
	if(mysqli_num_rows($result) == 0) // if user isn't registered in database must be registered
	{
		$query = "insert into `chat` (`chat_id`,`solans`,`solT`,`solF`,`grade`,`name`) values ('$chatId','','0','0','0','$chatName')";
		$result = mysqli_query($conn,$query);
		$query_2 = "insert into `repeared` (`chat_id`,`q1`,`q2`,`q3`,`q4`,`q5`,`q6`,`q7`,`q8`,`q9`,`q10`,`q11`,`q12`) values ('$chatId','','','','','','','','','','','','')";
		$result_2 = mysqli_query($conn,$query_2);
		$query_2 = "insert into `field` (`chat_id`,`field_name`) values ('$chatId','')";
		$result_2 = mysqli_query($conn,$query_2);
	}
	$exit_message = "";
	if($message == '[exit]')
	{
		$message = '/start';
		$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
		$data = mysqli_fetch_row($result);
		//$data[2]++;$data[3]++;
		$exit_message .= "الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3]."\n\n\n";
	}
$sol = false; // determine if the mesage is anwer or quistion
	if ($message != '/start' && $message !='[سيرة]' && $message != '[معلومات عامة]' && $message != '[مسابقات قرآنية]' && $message != '[أسئلة حديثية]' && $message != '[أسئلة فقهية]' && $message != '[ألغاز]') {
		$result = mysqli_query($conn,"select * from `field` where `chat_id` = '$chatId'");
		$data = mysqli_fetch_row($result);
		$swap = $message; 
		$message = $data[1];
		$sol = $swap;
	}
	else
	{
		mysqli_query($conn,"UPDATE `field` SET `field_name` = '$message' WHERE chat_id = '$chatId'");
	}

	switch ($message) {//chicking on message
		case '/start':
			mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0' WHERE chat_id = '$chatId'");
		   	mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");

			$keyboard = array(array("[مسابقات قرآنية]","[معلومات عامة]","[سيرة]"),array("[أسئلة حديثية]","[أسئلة فقهية]","[ألغاز]"));
			$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
   			$reply = json_encode($resp);
   			$exit_message .= "اختر احد هذه الاقسام التالية";
   			sendMessage($chatId,$exit_message,$reply);
			// sendPhoto($chatId);
			break;
/*---------*/			
/*---------*/			
/*---------*/			
		case '[سيرة]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		  mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `quistions` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `quistions` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
/*---------*/			
/*---------*/			
/*---------*/
		case '[معلومات عامة]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		 mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `GI` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `GI` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
/*---------*/			
/*---------*/			
/*---------*/
			case '[مسابقات قرآنية]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		 mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `quraan` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `quraan` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
/*---------*/			
/*---------*/			
/*---------*/			
		case '[أسئلة حديثية]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		 mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `hades` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `hades` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
/*---------*/			
/*---------*/			
/*---------*/			
		case '[أسئلة فقهية]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		  mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `fqhe` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `fqhe` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
/*---------*/			
/*---------*/			
/*---------*/				
		case '[ألغاز]':
				$result = mysqli_query($conn,"select * from `chat` where `chat_id` = '$chatId'");
				$data = mysqli_fetch_row($result);
				$sum1=$data[2]; $sum2=$data[3];
				$sum = $sum1 + $sum2 + 1;

				if ($sol != false) // checking if there is a solution
				{	
					if ($sol == $data[1]) { // checking if the solution is true
						mysqli_query($conn,"UPDATE `chat` SET `solT` = `solT`+'1' WHERE chat_id = '$chatId'");
						$data[2] = $data[2] + 1;
					}
					else// if the solution is false
					{
						mysqli_query($conn,"UPDATE `chat` SET `solF` = `solF`+'1' WHERE chat_id = '$chatId'");
						$data[3] = $data[3] + 1;
					}
				}
				
				if ($data[2]+$data[3] == 12) { // terminate the program after three quistions
					$keyboard = array(array("/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		  mysqli_query($conn,"UPDATE `chat` SET `solans` = '' , `solT` = '0' , `solF` = '0', `grade` = '$data[2]' WHERE chat_id = '$chatId'");
		   			mysqli_query($conn,"UPDATE `repeared` SET `q1` = '' , `q2` = '' , `q3` = '' , `q4` = '' , `q5` = '' , `q6` = '' , `q7` = '' , `q8` = '' , `q9` = '' , `q10` = '' , `q11` = '' , `q12` = '' WHERE chat_id = '$chatId'");
		   			sendMessage($chatId,"الاجابات الصحيحة = ".$data[2]."\nعدد الاجابات الخاطئة = ".$data[3],$reply);
				}
				else // asking anther quistion
				{
					$results = mysqli_query($conn,"SELECT * FROM `puzzle` ORDER BY RAND() LIMIT 1");
					$data = mysqli_fetch_row($results); // select random quistion from table quistions

					$results_q = mysqli_query($conn,"SELECT * FROM `repeared` WHERE `chat_id` = '$chatId'");
					$data_q = mysqli_fetch_row($results_q); // select all quistions that have been asked

					while (in_array($data[1], $data_q)) //checking if the quistion is repeated
					{
							$results = mysqli_query($conn,"SELECT * FROM `puzzle` ORDER BY RAND() LIMIT 1");
							$data = mysqli_fetch_row($results);// then get anther quistion not repeated befor
					}

					mysqli_query($conn,"UPDATE `repeared` SET `q".$sum."` = '$data[1]' WHERE chat_id = '$chatId'");
/* print button to choice an answer */
					$keyboard = array(array($data[2],$data[3],$data[4]),array($data[5],"[exit]","/start"));
					$resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
		   			$reply = json_encode($resp);
		   			mysqli_query($conn,"UPDATE `chat` SET `solans` = '$data[6]' WHERE chat_id = '$chatId'");
					sendMessage($chatId,$data[1],$reply);
				}
			break;
		
	}
	mysqli_close($conn);



	function sendMessage ($chatId,$message,$reply)
	{
		// $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".urlencode($message);
		$url = $GLOBALS[website]."/sendmessage?chat_id=".$chatId."&text=".urlencode($message)."&reply_markup=".$reply;
		file_get_contents($url);
	}
	function sendPhoto ($chatId)
	{
		// $url = $GLOBALS[website]."/sendMessage?chat_id=".$chatId."&text=".urlencode($message);
	$url = $GLOBALS[website]."/sendPhoto?chat_id=".$chatId."&photo=".file_get_contents('https://performer.azurewebsites.net/mohamed.jpg');
		file_get_contents($url);
	}

// 296664810

?>
