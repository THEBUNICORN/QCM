<?php
//Add-on Heroku to send the mail.
$ch = curl_init('https://realemail.expeditedaddons.com/?api_key=' . getenv('REALEMAIL_API_KEY') . '&email=email%40example.org&fix_typos=false');

$response = curl_exec($ch);
curl_close($ch);

var_dump($response);




// If the form is sent by POST, declare variables.	
	if (isset($_POST['submitted'])){
		$submitted = true;
		$nbsubmitted = $_POST['submitted'];
		$firstname = ucfirst($_POST['prenom']);
		$lastname = ucfirst($_POST['nom']);
		$emailStudent = $_POST['email'];
		$emailTeacher = 'becode@becode.org';
		$form= $_POST;

	}
	else {
		$submitted = false;
	}

//Inclusion of the form in the form of an array written by the client.
	include 'reponse.php';

?>

<!DOCTYPE html>
<html>
	<head>
	  <meta charset="utf-8">
	  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	  <link rel="stylesheet" href="perso.css">
	  <TITLE>QCM</TITLE>
	</head>

	<body>

		<h1>QCM</h1>
		<div class=container>

		<?php
		// If POST submitted, generate the patch
		if ($submitted){
			correctifGen($formulaire);
		}
		// If POST is not submitted, generate the form
		else {
		?>
		<form method='POST'>
			<div class='form-group'>
				<h4>Student</h4>
					<label for="firstname">First name
						<input id='firstname' name='firstname' class="form-control">
					</label>
					<label for='lastname'>Last Name
						<input id='lastname' name='lastname' class="form-control">
					</label>
					<label for='email'>Email
						<input type="email" id='email' name='email' class="form-control">
					</label>
			</div>

			<?php
				questionGen($reponse);
			?>
			<div class='form-group'>
				<button type='submit' class="btn btn-default">Check my answer</button>
			</div>

		</form>
		<?php
		}
		?>
		</div>

	</body>
</html>

<?php

// Form generator
	function questionGen($QCM){
		// Variables declarations
		global $multipleQuestions;
		$multipleQuestions = [];
		$num = 0;
		// Browse the form
		foreach ($form as $question) {
			$num++;
			echo "<div class='form-group'>";
			// Number of the question
			echo "<h4>Question " . $num . "</h4>";
			// Statement of the question
			echo "<p class='question'>" . $question[0] . "</p>";
			// Browse fields of questions skiping the 1st field 
		 	for ($i = 1; $i <=3; $i++) {
				// Generation of an identification code answering the number of the question concerned and whether the answer is correct (1st field) or not (2nd and 3rd fields)
				$codeRep = $num . '-' . $i ;
				// Generate radio content
				$choiceHtml = "<div class='radio'> <label for='" . $codeRep . "'> <input type='radio' name='Q" . $num . "' id='" . $codeRep. "' value='" . $codeRep . "'>" . $question[$i] . "</label> </div>";
				// Add the radio selection in an array
				array_push($multipleQuestions, $choiceHtml);
			}
			// Mix the choices to restore them in a random order
			shuffle($multipleQuestions);
			foreach ($multipleQuestions as $choiceHtml) {	
				echo $choiceHtml;
			}
			// Empty the array for the next question
			$multipleQuestions = [];
			echo "</div>";
		}
		// Include the number of questions in hidden input
	echo " <div style='display: none' class='form-group'>
						<input name='submitted' value=" . $num . ">
					</div> ";
	}

// Correctif generator
	function correctifGen($formulaire){
	global $results;
	// Calculation and display of result
		 // Create an array based on the answer codes containing the question number and the student's answer (1, 2 or 3)
		$repStudent = [];
		foreach ($form as $key => $value) {
			if ((substr($key, 0, 1)) == 'Q') {
				$codeArray = explode('-', $value);
				$codeQ = $codeArray[0];
				$codeR = $codeArray[1];
				$repStudent[$codeQ] = $codeR;
			}
		}
		// Count the positive points, 1 is the correct answer
		$good = 0;
		foreach ($repStudent as $codeQ => $codeR) {
			if ($codeR == 1){
				$good++;
			}
		}
		// Count the negative points, 2 and 3 are wrong
		$wrong = 0;
		foreach ($repStudent as $codeQ => $codeR) {
			if ($codeR == 2 OR $codeR == 3){
				$wrong++;
			}
		}
		// Calculate the result and prepare a message based on
		global $nbsubmitted;
		global $firstname;
		$resultat = 100*(($good - $wrong)/$nbsubmitted);
		if ($resultat > 50){
			$msg = 'Well Done '. $firstname . ', your result is ' . $results . '%!';
		}
		else {
			$msg = $firstname . ', You need to work a little bit more harder, your result is '. $results . '%!';
		}
		echo "<p class='result'>" . $msg . "</p>";

	//  Correction display
		?><p>
		<br>
		<!-- Caption display -->
		<p class='good'>  Correction  </p>
		<p class='response'>  -1 point  </p>
		<p class='responsegood'>  +1 point  </p>
		</p>
		<?php
		// Variables declaration
		$num = 0;
		global $questionnaire;

		// Browse the questionnaire
		foreach ($questionnaire as $question) {
			$num++;
			echo "<div class='form-group'>";
			echo "<h4>Question " . $num . "</h4>";
			// Statement of the question
			echo "<p>" . $question[0] . "</p>";
			// Browse fields of questions skiping the 1st field
			 	for ($i = 1; $i <=3; $i++) {
					// Adding a class for the answer entered by the student
					$class1 = "";
					if ($i == $repStudent[$num]) {
						$class1 = "answer";
					}
					// Adding a class for the correct answer
					$class2 = "";
					if ($i == 1){
						$class2 = "well Done";
					}
					//Concatenation of the class to be applied 
					$class = "class='" . $class1 . $class2 . "'";
	
					// Generate the choice of contents
					$choiceHtml = "<p " . $class . ">" . $question[$i] . "</p>";
					echo $choiceHtml;
				}
			echo "</div>";
		}
	}
//test commit
// Send results by mail
	function sendResult($emailTeacher, $emailStudent){
		global $resultat;
		$objectTeacher = 'QCM's results of '.$firstname.' '.upperCase($lastname);
		$messageTeacher = 'results of '.$firstname.' '.upperCase($lastname). 'is '.$results;
		$objectStudent = 'Your QCM results;
		$messageStudent = 'Your results is '. $resultat;
		mail($emailTeacher, $objectTeacher, $messageTeacher);
		mail($emailStudent, $objectStudent, $messageStudent);
	}	
?>