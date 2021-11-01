<?php

if(!empty($_POST)){
	require_once('dbConnector.php');
	require_once('AfricasTalkingGateway.php');
	require_once('config.php');
	$sessionId=$_POST['sessionId'];
	$serviceCode=$_POST['serviceCode'];
	$phoneNumber=$_POST['phoneNumber'];
	$text=$_POST['text'];
	$textArray=explode('*', $text);
	$userResponse=trim(end($textArray));

	$level=0;

	$sql = "select level from session_levels where session_id ='".$sessionId." '";
	$levelQuery = $db->query($sql);
	if($result = $levelQuery->fetch_assoc()) {
  		$level = $result['level'];
		$level = $result['level'];
	}

	$sql7 = "SELECT * FROM users WHERE phonenumber LIKE '%".$phoneNumber."%' LIMIT 1";
	$userQuery=$db->query($sql7);
	$userAvailable=$userQuery->fetch_assoc();

	if($userAvailable && $userAvailable['city']!=NULL && $userAvailable['username']!=NULL){
		
			switch ($userResponse) {
			    case "":
			        if($level==0){
			        
			        	$sql9b = "INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
			        	$db->query($sql9b);
		
						$response = "CON Karibu " . $userAvailable['username']  . ". Please choose a service.\n";
						$response .= " 1. Send me todays voice tip.\n";
						$response .= " 2. Please call me!\n";
						$response .= " 3. Send me Airtime!\n";
			  			header('Content-type: text/plain');
 			  			echo $response;						
			        }
			        break;
			    case "1":
			        if($level==1){
			        	$response = "END Please check your SMS inbox.\n";

						$code = '44005';
            			$recipients = $phoneNumber;
            			$message    = "https://hahahah12-grahamingokho.c9.io/kaka.mp3";
            			$gateway    = new AfricasTalkingGateway($username, $apikey, $env);
            			try { $results = $gateway->sendMessage($recipients, $message, $code); }
            			catch ( AfricasTalkingGatewayException $e ) {echo "Encountered an error while sending: ".$e->getMessage(); }
			  			header('Content-type: text/plain');
 			  			echo $response;	            						        	
			        }
			        break;
			    case "2":
			        if($level==1){
			          	$response = "END Please wait while we place your call.\n";

			          
			         	$from="+254724587654"; $to=$phoneNumber;
			          	$gateway = new AfricasTalkingGateway($username, $apikey, $env);
			          	try { $gateway->call($from, $to); }
			          	catch ( AfricasTalkingGatewayException $e ){echo "Encountered an error when calling: ".$e->getMessage();}
			  			header('Content-type: text/plain');
 			  			echo $response;	 
			        }
			        break;
			    case "3":
			    	if($level==1){

						$response = "END Please wait while we load your account.\n";
						$recipients = array( array("phoneNumber"=>"".$phoneNumber."", "amount"=>"KES 10") );
						$recipientStringFormat = json_encode($recipients);
						$gateway = new AfricasTalkingGateway($username, $apikey, $env);    
						try { $results = $gateway->sendAirtime($recipientStringFormat);}
						catch(AfricasTalkingGatewayException $e){ echo $e->getMessage(); }
			  			header('Content-type: text/plain');
 			  			echo $response;	 			    		
			    	}
			        break;			        
			    default:
			    	if($level==1){
				     
				    	$response = "CON You have to choose a service.\n";
				    	$response .= "Press 0 to go back.\n";
			
				    	$sqlLevelDemote="UPDATE `session_levels` SET `level`=0 where `session_id`='".$sessionId."'";
				    	$db->query($sqlLevelDemote);
	

				  		header('Content-type: text/plain');
	 			  		echo $response;	
			    	}
			}
	}else{
	
		if($userResponse==""){
	
			switch ($level) {
			    case 0:
				
				     $sql10b = "INSERT INTO `session_levels`(`session_id`, `phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."', 1)";
				     $db->query($sql10b);

				     $sql10c = "INSERT INTO `users`(`phonenumber`) VALUES ('".$phoneNumber."')";
				     $db->query($sql10c);

				     $response = "CON Please enter your name";
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    case 1:
			
        			$response = "CON Name not supposed to be empty. Please enter your name \n";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    case 2:
			    	//10f. Request fir city again
					$response = "CON City not supposed to be empty. Please reply with your city \n";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    default:
			    	//10g. Request fir city again
					$response = "END Apologies, something went wrong... \n";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;
			}
		}else{
			//11. Update User table based on input to correct level
			switch ($level) {
			    case 0:
				     //11a. Serve the menu request for name
				     $response = "END This level should not be seen...";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    case 1:
			    	//11b. Update Name, Request for city
			        $sql11b = "UPDATE `users` SET `username`='".$userResponse."' WHERE `phonenumber` LIKE '%". $phoneNumber ."%'";
			        $db->query($sql11b);

			        //11c. We graduate the user to the city level
			        $sql11c = "UPDATE `session_levels` SET `level`=2 WHERE `session_id`='".$sessionId."'";
			        $db->query($sql11c);

			        //We request for the city
			        $response = "CON Please enter your city";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    case 2:
			    	//11d. Update city
			        $sql11d = "UPDATE `users` SET `city`='".$userResponse."' WHERE `phonenumber` = '". $phoneNumber ."'";
			        $db->query($sql11d);

			    	//11e. Change level to 0
		        	$sql11e = "INSERT INTO `session_levels`(`session_id`,`phoneNumber`,`level`) VALUES('".$sessionId."','".$phoneNumber."',1)";
		        	$db->query($sql11e);   

			    	//11f. Serve services menu...
					$response = "CON Please choose a service.\n";
					$response .= " 1. Send me todays voice tip.\n";
					$response .= " 2. Please call me!\n";
					$response .= " 3. Send me Airtime!\n";				    	

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;

			    default:
			    	//11g. Request for city again
					$response = "END Apologies, something went wrong... \n";

			  		// Print the response onto the page so that our gateway can read it
			  		header('Content-type: text/plain');
 			  		echo $response;	
			        break;
			}			
		}
	}
}
?>