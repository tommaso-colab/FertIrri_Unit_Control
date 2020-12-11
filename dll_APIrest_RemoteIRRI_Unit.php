<?php
// /*/*/*/*/*dll_APIrest_RemoteIRRI_Unit /*/*/*/*/*
// *Tommaso Letterio - date 13/07/2020
// *LIST OBJECT 
// 1 - send to talgil server: send program to irr unit (lo manda sempre anche se non c'è da irrigare quel giorno
// 2- send to talgil server: adjust volume dose to valve in program as sequence
// *LISTFUCNTION 
// 1 - dateDifference: Calcola differenza tra 2 date in giorni 
// 2 - talgil API SENDER: manda stringa con curl tu TALGIL
// 3 - createVector: crea vettore per programmazione centralina come giorni ciclo 14d

?>

<?php
// function per calcolare differenza in gg tra 2 date
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
   
    $interval = date_diff($datetime1, $datetime2);
   
    return $interval->format($differenceFormat);
   
}

// function crea vettore per TALGIL
// creo vettore anche se non ho irrigation e faccio vettore con tutti 0 
// altrimenti metto valore 1 in vettore di 14 elementi per giorno di oggi 
// a partire da data inizio ciclodi 14 gg
function createVector($value, $IrrTime_min)
{
	echo "</br> createVector_value Start: ". $value;
	$VectorSTRING = '';
	if ($value > 14){      
						// se ho diff da inizio cyclo 14 > di 14 faccil calcolo in base 14 con gg in più (giorno 15 da cycle starty date diviene giorno 1)
					echo "</br>Value: ". $value;
					$CalcRatio = floor($value/14); 
					echo "</br>Calc Ratio: ". $CalcRatio;
					// echo "</br>CYCLE 14 int: ". round($value/14);
					// $value = abs($value - round($value/14)*14);
					// echo "</br> createNEW value if >14: ". $value;
					echo "</br>CYCLE 14 int: ". $CalcRatio;
					$value = abs($value - $CalcRatio*14);
					echo "</br> createNEW value if >14: ". $value;
					
					;}
	for ($i = 1 ; $i <= 14; $i++){
		$input = '0';
		if ($i== $value && $IrrTime_min!= 'NoIrr'){ $input = '1';}
		if ($i == 1){ 
					$VectorSTRING = "[".$VectorSTRING.$input; }
		elseif($i == 14){
					$VectorSTRING = $VectorSTRING. "," .$input."]";}
		else{
					$VectorSTRING = $VectorSTRING. "," .$input;}
	}
   
    return $VectorSTRING;
   
}

//function send API modify to TALGIL

function API_Talgil_sender ($requestString, $tokenTALGIL){
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $requestString,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_SSL_VERIFYPEER => FALSE,
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"TLG-API-Key: $tokenTALGIL"
			  ),
			));

			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  echo "</br>##Print request response: </br>";
			  echo $response;
			}
			
		return 	$response;
		}

?>


<?php

// OGGETTO send to talgil server the program:
//input : $IDuser, $IDplot, $DateIrr
//output : response from server
    class sendTo_Talgil  {
		
        //proprietà
			private $idTALGIL_unit= "";
          	private $tokenTALGIL= "";
			private $ID_talgil_Program = "";
			private $IrrTime_min =""; //se no irrigazione "NoIrr" altrimento ho Minuti durata Irrigazione
			private $DateFOR_cycle14d ="";
			private $dayRUN ="";
			private $oradigiornoInizioIrr_inMinuti= "";
			private $idSequenzaDaModificare=""; //id della sequenza di cui modificare il volume come nel programma

        //costruttore
        public function __construct($idTALGIL_unit, $tokenTALGIL, $ID_talgil_Program, $IrrTime_min, $DateFOR_cycle14d, $dayRUN, $oradigiornoInizioIrr_inMinuti, $idSequenzaDaModificare) {
            $this->idTALGIL_unit = $idTALGIL_unit;
			$this->tokenTALGIL = $tokenTALGIL;
			$this->ID_talgil_Program = $ID_talgil_Program;
			$this->IrrTime_min = $IrrTime_min;
			$this->DateFOR_cycle14d = $DateFOR_cycle14d;
			$this->dayRUN = $dayRUN;
			$this->oradigiornoInizioIrr_inMinuti = $oradigiornoInizioIrr_inMinuti;
			$this->idSequenzaDaModificare = $idSequenzaDaModificare;
			
        }   

	     public function Construct_Vector_forTALGIL_program () {
			$DateFOR_cycle14d = $this->DateFOR_cycle14d;
			$dayRUN = $this->dayRUN;
			$IrrTime_min = $this->IrrTime_min;
			echo "</br> Construct inside </br>";
			echo "</br> day start 14dCYCLE: ". $DateFOR_cycle14d;
			echo "</br> day run: ". $dayRUN;
			$interval = dateDifference($dayRUN, $DateFOR_cycle14d);
			echo "</br> diffDate: ". $interval;
			$createVector =	createVector ($interval, $IrrTime_min);
			echo "</br> CreatedVector: ". $createVector;
			return $createVector;
        }
		
		
		public function Send_To_Talgil_Values() {
			
			$VettoreDATE_perTALGIL = $this->Construct_Vector_forTALGIL_program();
			
			$tokenTALGIL = $this->tokenTALGIL;
			$idTALGIL_unit = $this->idTALGIL_unit;
			$ID_talgil_Program = $this->ID_talgil_Program;
			$IrrTime_min = $this->IrrTime_min;
			$oradigiornoInizioIrr_inMinuti = $this->oradigiornoInizioIrr_inMinuti;
			$idSequenzaDaModificare = $this->idSequenzaDaModificare;
			
			$main = "";
		
			//FACCIO CICLO per decidere se inviare o SOLO VETTORE DATE o TUTTO INSEME
			
			if ($IrrTime_min == 'NoIrr'){
					
					// invio VETTORE DATE
					$indexToMod = '113'; //id index per orario START su talgil
					$requestRES = $idTALGIL_unit ."/programs/".  $ID_talgil_Program ."/modify?index=".  $indexToMod ."&value=" .  $VettoreDATE_perTALGIL ;
					$requestString = $main.$requestRES;		
					$sendSTARTime = API_Talgil_sender($requestString, $tokenTALGIL);
						
					}else{
						
					// invio VETTORE DATE
					$indexToMod = '113'; //id index per orario START su talgil
					$requestRES = $idTALGIL_unit ."/programs/".  $ID_talgil_Program ."/modify?index=".  $indexToMod ."&value=" .  $VettoreDATE_perTALGIL ;
					$requestString = $main.$requestRES;		
					$sendSTARTime = API_Talgil_sender($requestString, $tokenTALGIL);
					sleep(3);
					// invio orario start program
					$indexToMod = '105'; //id index per orario START su talgil
					$requestRES = $idTALGIL_unit ."/programs/".  $ID_talgil_Program ."/modify?index=".  $indexToMod ."&value=" .  $oradigiornoInizioIrr_inMinuti ;
					$requestString = $main.$requestRES;		
					$sendSTARTime = API_Talgil_sender($requestString, $tokenTALGIL);
					sleep(3);
					$IRRTime_secondi = $IrrTime_min*60;
					echo "</br><strong>orarioFINE </strong>".$IRRTime_secondi;
					$indexToMod = '106'; //id index per orario START su talgi	
					$requestRES = $idTALGIL_unit ."/programs/".  $ID_talgil_Program ."/sequence/".  $idSequenzaDaModificare ."/modify?index=".  $indexToMod ."&value=" .  $IRRTime_secondi ;
					$requestString = $main.$requestRES;		
					$sendSTARTime = API_Talgil_sender($requestString, $tokenTALGIL);
					sleep(3);
					}
	
			}


	}
?>
